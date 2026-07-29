<?php

declare(strict_types=1);

namespace App\Services\Reservations;

use App\Enums\PropertyStatus;
use App\Enums\ReservationStatus;
use App\Models\House;
use App\Models\HouseStatusHistory;
use App\Models\PropertyReservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PropertyReservationService
{
    public function submit(
        House $house,
        User $customer,
        ?string $notes = null,
    ): PropertyReservation {
        return DB::transaction(
            function () use (
                $house,
                $customer,
                $notes,
            ): PropertyReservation {
                $lockedHouse = House::query()
                    ->whereKey($house->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->releaseExpiredHold(
                    $lockedHouse,
                    $customer,
                );

                if (
                    $lockedHouse->status
                    !== PropertyStatus::Available
                ) {
                    throw ValidationException::withMessages([
                        'house_id' =>
                            'This property is not available for reservation.',
                    ]);
                }

                $alreadyPending = PropertyReservation::query()
                    ->where('house_id', $lockedHouse->id)
                    ->where('customer_id', $customer->id)
                    ->where(
                        'status',
                        ReservationStatus::PendingReview->value,
                    )
                    ->exists();

                if ($alreadyPending) {
                    throw ValidationException::withMessages([
                        'house_id' =>
                            'You already have a pending reservation request for this property.',
                    ]);
                }

                return PropertyReservation::query()->create([
                    'reference' => $this->generateReference(),
                    'house_id' => $lockedHouse->id,
                    'customer_id' => $customer->id,
                    'status' =>
                        ReservationStatus::PendingReview->value,
                    'customer_notes' => $notes,
                ]);
            },
            3,
        );
    }

    public function approve(
        PropertyReservation $reservation,
        User $reviewer,
        ?string $reviewNotes = null,
    ): PropertyReservation {
        return DB::transaction(
            function () use (
                $reservation,
                $reviewer,
                $reviewNotes,
            ): PropertyReservation {
                $lockedReservation = PropertyReservation::query()
                    ->whereKey($reservation->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedReservation->status
                    !== ReservationStatus::PendingReview
                ) {
                    throw ValidationException::withMessages([
                        'reservation' =>
                            'Only pending reservation requests can be approved.',
                    ]);
                }

                $house = House::query()
                    ->whereKey($lockedReservation->house_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->releaseExpiredHold(
                    $house,
                    $reviewer,
                );

                if ($house->status !== PropertyStatus::Available) {
                    throw ValidationException::withMessages([
                        'reservation' =>
                            'This property is no longer available.',
                    ]);
                }

                $now = now();

                $expiresAt = $now->copy()->addHours(
                    (int) config(
                        'dems.reservation_hold_hours',
                        72,
                    ),
                );

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Approved->value,
                    'reviewed_by' => $reviewer->id,
                    'review_notes' => $reviewNotes,
                    'approved_at' => $now,
                    'expires_at' => $expiresAt,
                ])->save();

                PropertyReservation::query()
                    ->where('house_id', $house->id)
                    ->whereKeyNot($lockedReservation->id)
                    ->where(
                        'status',
                        ReservationStatus::PendingReview->value,
                    )
                    ->update([
                        'status' =>
                            ReservationStatus::Rejected->value,
                        'reviewed_by' => $reviewer->id,
                        'review_notes' =>
                            'Another reservation request was approved for this property.',
                        'rejected_at' => $now,
                        'updated_at' => $now,
                    ]);

                $house->forceFill([
                    'status' =>
                        PropertyStatus::TemporarilyReserved->value,
                    'reserved_until' => $expiresAt,
                ])->save();

                HouseStatusHistory::query()->create([
                    'house_id' => $house->id,
                    'changed_by' => $reviewer->id,
                    'from_status' =>
                        PropertyStatus::Available->value,
                    'to_status' =>
                        PropertyStatus::TemporarilyReserved->value,
                    'reason' => 'Reservation request approved.',
                    'metadata' => [
                        'reservation_id' =>
                            $lockedReservation->id,
                        'reservation_reference' =>
                            $lockedReservation->reference,
                        'expires_at' =>
                            $expiresAt->toISOString(),
                    ],
                    'changed_at' => $now,
                ]);

                return $lockedReservation->fresh([
                    'house',
                    'customer',
                    'reviewedBy',
                ]);
            },
            3,
        );
    }

    public function reject(
        PropertyReservation $reservation,
        User $reviewer,
        string $reason,
    ): PropertyReservation {
        return DB::transaction(
            function () use (
                $reservation,
                $reviewer,
                $reason,
            ): PropertyReservation {
                $lockedReservation = PropertyReservation::query()
                    ->whereKey($reservation->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedReservation->status
                    !== ReservationStatus::PendingReview
                ) {
                    throw ValidationException::withMessages([
                        'reservation' =>
                            'Only pending reservation requests can be rejected.',
                    ]);
                }

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Rejected->value,
                    'reviewed_by' => $reviewer->id,
                    'review_notes' => $reason,
                    'rejected_at' => now(),
                ])->save();

                return $lockedReservation->fresh([
                    'house',
                    'customer',
                    'reviewedBy',
                ]);
            },
            3,
        );
    }

    public function expire(
        PropertyReservation $reservation,
    ): bool {
        return DB::transaction(
            function () use ($reservation): bool {
                $lockedReservation = PropertyReservation::query()
                    ->whereKey($reservation->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedReservation->status
                    !== ReservationStatus::Approved
                ) {
                    return false;
                }

                if (
                    $lockedReservation->expires_at === null
                    || $lockedReservation->expires_at->isFuture()
                ) {
                    return false;
                }

                $house = House::query()
                    ->whereKey($lockedReservation->house_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Expired->value,
                ])->save();

                if (
                    $house->status
                    === PropertyStatus::TemporarilyReserved
                ) {
                    $house->forceFill([
                        'status' =>
                            PropertyStatus::Available->value,
                        'reserved_until' => null,
                    ])->save();

                    HouseStatusHistory::query()->create([
                        'house_id' => $house->id,
                        'changed_by' =>
                            $lockedReservation->reviewed_by
                            ?? $lockedReservation->customer_id,
                        'from_status' =>
                            PropertyStatus::TemporarilyReserved->value,
                        'to_status' =>
                            PropertyStatus::Available->value,
                        'reason' => 'Reservation hold expired.',
                        'metadata' => [
                            'reservation_id' =>
                                $lockedReservation->id,
                            'reservation_reference' =>
                                $lockedReservation->reference,
                        ],
                        'changed_at' => now(),
                    ]);
                }

                return true;
            },
            3,
        );
    }

    public function expireDueReservations(): int
    {
        $expiredCount = 0;

        PropertyReservation::query()
            ->where(
                'status',
                ReservationStatus::Approved->value,
            )
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->eachById(
                function (
                    PropertyReservation $reservation,
                ) use (&$expiredCount): void {
                    if ($this->expire($reservation)) {
                        $expiredCount++;
                    }
                },
            );

        return $expiredCount;
    }

    private function releaseExpiredHold(
        House $house,
        User $actor,
    ): void {
        if (
            $house->status
            !== PropertyStatus::TemporarilyReserved
        ) {
            return;
        }

        if (
            $house->reserved_until === null
            || $house->reserved_until->isFuture()
        ) {
            return;
        }

        $activeReservation = PropertyReservation::query()
            ->where('house_id', $house->id)
            ->where(
                'status',
                ReservationStatus::Approved->value,
            )
            ->lockForUpdate()
            ->latest('id')
            ->first();

        if ($activeReservation !== null) {
            $activeReservation->forceFill([
                'status' => ReservationStatus::Expired->value,
            ])->save();
        }

        $house->forceFill([
            'status' => PropertyStatus::Available->value,
            'reserved_until' => null,
        ])->save();

        HouseStatusHistory::query()->create([
            'house_id' => $house->id,
            'changed_by' =>
                $activeReservation?->reviewed_by
                ?? $actor->id,
            'from_status' =>
                PropertyStatus::TemporarilyReserved->value,
            'to_status' =>
                PropertyStatus::Available->value,
            'reason' =>
                'Expired reservation hold released automatically.',
            'metadata' => [
                'reservation_id' =>
                    $activeReservation?->id,
            ],
            'changed_at' => now(),
        ]);

        $house->refresh();
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-RSV-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            PropertyReservation::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}