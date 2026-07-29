<?php

declare(strict_types=1);

namespace App\Services\Handovers;

use App\Enums\HandoverCondition;
use App\Enums\HandoverStatus;
use App\Models\PropertyAllocation;
use App\Models\PropertyHandover;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PropertyHandoverService
{
    /**
     * @param array<string, mixed> $data
     */
    public function schedule(
        array $data,
        User $scheduledBy,
    ): PropertyHandover {
        $customerId = (int) $data['customer_id'];
        $houseId = (int) $data['house_id'];

        $this->validateRelatedRecords(
            $customerId,
            $houseId,
            $data,
        );

        return DB::transaction(
            fn(): PropertyHandover =>
            PropertyHandover::query()->create([
                'reference' => $this->generateReference(),
                'customer_id' => $customerId,
                'house_id' => $houseId,
                'purchase_id' =>
                $data['purchase_id'] ?? null,
                'property_allocation_id' =>
                $data['property_allocation_id'] ?? null,
                'scheduled_by' => $scheduledBy->id,
                'assigned_to' =>
                $data['assigned_to'] ?? null,
                'completed_by' => null,
                'status' =>
                HandoverStatus::Scheduled->value,
                'handover_condition' => null,
                'scheduled_at' => $data['scheduled_at'],
                'completed_at' => null,
                'cancelled_at' => null,
                'possession_released_at' => null,
                'keys_released' => false,
                'customer_representative_name' =>
                $data['customer_representative_name']
                    ?? null,
                'location_notes' =>
                $data['location_notes'] ?? null,
                'customer_notes' =>
                $data['customer_notes'] ?? null,
                'staff_notes' =>
                $data['staff_notes'] ?? null,
                'outcome' => null,
            ]),
        );
    }

    public function complete(
        PropertyHandover $handover,
        User $completedBy,
        HandoverCondition $condition,
        string $outcome,
        bool $keysReleased,
        ?string $staffNotes = null,
    ): PropertyHandover {
        return DB::transaction(
            function () use (
                $handover,
                $completedBy,
                $condition,
                $outcome,
                $keysReleased,
                $staffNotes,
            ): PropertyHandover {
                $lockedHandover =
                    PropertyHandover::query()
                    ->lockForUpdate()
                    ->findOrFail($handover->id);

                if (
                    $lockedHandover->status
                    === HandoverStatus::Completed
                ) {
                    return $lockedHandover;
                }

                if (
                    $lockedHandover->status
                    !== HandoverStatus::Scheduled
                ) {
                    throw ValidationException::withMessages([
                        'handover' =>
                        'Only a scheduled handover can be completed.',
                    ]);
                }

                $lockedHandover->update([
                    'status' =>
                    HandoverStatus::Completed->value,
                    'handover_condition' =>
                    $condition->value,
                    'completed_by' => $completedBy->id,
                    'completed_at' => now(),
                    'possession_released_at' =>
                    $keysReleased ? now() : null,
                    'keys_released' => $keysReleased,
                    'outcome' => $outcome,
                    'staff_notes' =>
                    $staffNotes
                        ?? $lockedHandover->staff_notes,
                ]);

                return $lockedHandover->fresh([
                    'customer',
                    'house',
                    'purchase',
                    'propertyAllocation',
                    'scheduledBy',
                    'assignedTo',
                    'completedBy',
                ]);
            },
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRelatedRecords(
        int $customerId,
        int $houseId,
        array $data,
    ): void {
        if (! empty($data['purchase_id'])) {
            $purchase = Purchase::query()
                ->findOrFail((int) $data['purchase_id']);

            if (
                (int) $purchase->customer_id !== $customerId
                || (int) $purchase->house_id !== $houseId
            ) {
                throw ValidationException::withMessages([
                    'purchase_id' =>
                    'The selected purchase does not match the customer and property.',
                ]);
            }
        }

        if (! empty($data['property_allocation_id'])) {
            $allocation = PropertyAllocation::query()
                ->findOrFail(
                    (int) $data['property_allocation_id'],
                );

            if (
                (int) $allocation->customer_id !== $customerId
                || (int) $allocation->house_id !== $houseId
            ) {
                throw ValidationException::withMessages([
                    'property_allocation_id' =>
                    'The selected allocation does not match the customer and property.',
                ]);
            }
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-HND-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            PropertyHandover::query()
            ->where('reference', $reference)
            ->exists()
        );

        return $reference;
    }
}
