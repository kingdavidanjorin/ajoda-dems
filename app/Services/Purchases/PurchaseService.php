<?php

declare(strict_types=1);

namespace App\Services\Purchases;

use App\Enums\AllocationStatus;
use App\Enums\PaymentPlanType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Models\House;
use App\Models\HouseStatusHistory;
use App\Models\Payment;
use App\Models\PropertyAllocation;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PurchaseService
{
    public function initiate(
        PropertyReservation $reservation,
        User $salesOfficer,
        string $approvedPrice,
        PaymentPlanType $paymentPlan,
        ?string $salesNotes = null,
    ): Purchase {
        return DB::transaction(
            function () use (
                $reservation,
                $salesOfficer,
                $approvedPrice,
                $paymentPlan,
                $salesNotes,
            ): Purchase {
                $lockedReservation = PropertyReservation::query()
                    ->whereKey($reservation->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Repeated submission returns the purchase that was
                 * already created instead of creating a duplicate.
                 */
                $existingPurchase = Purchase::query()
                    ->where(
                        'reservation_id',
                        $lockedReservation->id,
                    )
                    ->lockForUpdate()
                    ->first();

                if ($existingPurchase !== null) {
                    return $existingPurchase;
                }

                if (
                    $lockedReservation->status
                    !== ReservationStatus::Approved
                ) {
                    throw ValidationException::withMessages([
                        'reservation' => 'Only an approved reservation can start a purchase.',
                    ]);
                }

                if (
                    $lockedReservation->expires_at === null
                    || $lockedReservation->expires_at->isPast()
                ) {
                    throw ValidationException::withMessages([
                        'reservation' => 'The approved reservation hold has expired.',
                    ]);
                }

                $house = House::query()
                    ->whereKey($lockedReservation->house_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $house->status
                    !== PropertyStatus::TemporarilyReserved
                ) {
                    throw ValidationException::withMessages([
                        'reservation' => 'The property is not eligible for purchase initiation.',
                    ]);
                }

                $conflictingPurchase = Purchase::query()
                    ->where(
                        'active_house_id',
                        $house->id,
                    )
                    ->lockForUpdate()
                    ->exists();

                if ($conflictingPurchase) {
                    throw ValidationException::withMessages([
                        'reservation' => 'An active purchase already exists for this property.',
                    ]);
                }

                $now = now();

                $purchase = Purchase::query()->create([
                    'reference' => $this->generateReference(),
                    'reservation_id' => $lockedReservation->id,
                    'house_id' => $house->id,
                    'active_house_id' => $house->id,
                    'customer_id' => $lockedReservation->customer_id,
                    'initiated_by' => $salesOfficer->id,
                    'status' => PurchaseStatus::AwaitingPayment->value,
                    'approved_price' => $approvedPrice,
                    'payment_plan_type' => $paymentPlan->value,
                    'effective_date' => $now->toDateString(),
                    'sales_notes' => $salesNotes,
                    'initiated_at' => $now,
                ]);

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Converted->value,
                ])->save();

                $house->forceFill([
                    'status' => PropertyStatus::SaleProcessing->value,
                    'reserved_until' => null,
                ])->save();

                HouseStatusHistory::query()->create([
                    'house_id' => $house->id,
                    'changed_by' => $salesOfficer->id,
                    'from_status' => PropertyStatus::TemporarilyReserved->value,
                    'to_status' => PropertyStatus::SaleProcessing->value,
                    'reason' => 'Purchase process initiated.',
                    'metadata' => [
                        'purchase_id' => $purchase->id,
                        'purchase_reference' => $purchase->reference,
                        'reservation_id' => $lockedReservation->id,
                        'reservation_reference' => $lockedReservation->reference,
                    ],
                    'changed_at' => $now,
                ]);

                return $purchase->fresh([
                    'reservation',
                    'house',
                    'customer',
                    'initiatedBy',
                ]);
            },
            3,
        );
    }

    public function confirm(
        Purchase $purchase,
        User $salesOfficer,
    ): Purchase {
        return DB::transaction(
            function () use ($purchase, $salesOfficer): Purchase {
                $lockedPurchase = Purchase::query()
                    ->whereKey($purchase->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $lockedPurchase->status
                    === PurchaseStatus::Confirmed
                ) {
                    return $lockedPurchase->fresh([
                        'house',
                        'customer',
                        'payments',
                    ]);
                }

                if (
                    $lockedPurchase->status
                    !== PurchaseStatus::ReadyForConfirmation
                ) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Only a fully paid purchase can be confirmed.',
                    ]);
                }

                $verifiedTotal = (string) Payment::query()
                    ->where(
                        'purchase_id',
                        $lockedPurchase->id,
                    )
                    ->where(
                        'status',
                        PaymentStatus::Verified->value,
                    )
                    ->sum('amount');

                if (
                    $this->moneyToMinorUnits($verifiedTotal)
                    < $this->moneyToMinorUnits(
                        (string) $lockedPurchase->approved_price,
                    )
                ) {
                    throw ValidationException::withMessages([
                        'purchase' => 'Verified payments do not cover the approved price.',
                    ]);
                }

                $house = House::query()
                    ->whereKey($lockedPurchase->house_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (
                    $house->status
                    !== PropertyStatus::SaleProcessing
                ) {
                    throw ValidationException::withMessages([
                        'purchase' => 'The property is not in sale processing.',
                    ]);
                }

                $now = now();

                $lockedPurchase->forceFill([
                    'status' => PurchaseStatus::Confirmed->value,
                    'active_house_id' => null,
                    'confirmed_at' => $now,
                ])->save();

                $house->forceFill([
                    'status' => PropertyStatus::Sold->value,
                    'reserved_until' => null,
                ])->save();

                $allocation = PropertyAllocation::query()
                    ->firstOrCreate(
                        [
                            'purchase_id' => $lockedPurchase->id,
                        ],
                        [
                            'reference' => $this->generateAllocationReference(),
                            'house_id' => $house->id,
                            'customer_id' => $lockedPurchase->customer_id,
                            'allocated_by' => $salesOfficer->id,
                            'status' => AllocationStatus::Active->value,
                            'allocated_at' => $now,
                            'notes' => 'Automatically created when the purchase was confirmed.',
                        ],
                    );

                HouseStatusHistory::query()->create([
                    'house_id' => $house->id,
                    'changed_by' => $salesOfficer->id,
                    'from_status' => PropertyStatus::SaleProcessing->value,
                    'to_status' => PropertyStatus::Sold->value,
                    'reason' => 'Purchase confirmed after full payment verification.',
                    'metadata' => [
                        'purchase_id' => $lockedPurchase->id,
                        'purchase_reference' => $lockedPurchase->reference,
                        'verified_amount' => $verifiedTotal,
                    ],
                    'changed_at' => $now,
                ]);

                return $lockedPurchase->fresh([
                    'house',
                    'customer',
                    'payments',
                    'allocation',
                ]);
            },
            3,
        );
    }

    private function moneyToMinorUnits(
        string $amount,
    ): int {
        $amount = trim($amount);

        [$wholePart, $decimalPart] = array_pad(
            explode('.', $amount, 2),
            2,
            '0',
        );

        $decimalPart = substr(
            str_pad($decimalPart, 2, '0'),
            0,
            2,
        );

        return ((int) $wholePart * 100)
            + (int) $decimalPart;
    }

    private function generateAllocationReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-ALC-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            PropertyAllocation::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-PUR-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            Purchase::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
