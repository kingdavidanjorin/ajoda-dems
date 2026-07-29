<?php

declare(strict_types=1);

namespace App\Services\Inspections;

use App\Enums\InspectionStatus;
use App\Models\PropertyAllocation;
use App\Models\PropertyInspection;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PropertyInspectionService
{
    /**
     * @param array<string, mixed> $data
     */
    public function schedule(
        array $data,
        User $scheduledBy,
    ): PropertyInspection {
        $customerId = (int) $data['customer_id'];
        $houseId = (int) $data['house_id'];

        $this->validateRelatedRecords(
            $customerId,
            $houseId,
            $data,
        );

        return DB::transaction(
            fn(): PropertyInspection =>
            PropertyInspection::query()->create([
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
                'inspection_type' =>
                $data['inspection_type'],
                'status' =>
                InspectionStatus::Scheduled->value,
                'scheduled_at' => $data['scheduled_at'],
                'location_notes' =>
                $data['location_notes'] ?? null,
                'customer_notes' =>
                $data['customer_notes'] ?? null,
                'staff_notes' =>
                $data['staff_notes'] ?? null,
            ]),
        );
    }

    public function complete(
        PropertyInspection $inspection,
        User $completedBy,
        string $outcome,
        ?string $staffNotes = null,
    ): PropertyInspection {
        return DB::transaction(
            function () use (
                $inspection,
                $completedBy,
                $outcome,
                $staffNotes,
            ): PropertyInspection {
                $lockedInspection =
                    PropertyInspection::query()
                    ->lockForUpdate()
                    ->findOrFail($inspection->id);

                if (
                    $lockedInspection->status
                    === InspectionStatus::Completed
                ) {
                    return $lockedInspection;
                }

                if (
                    $lockedInspection->status
                    !== InspectionStatus::Scheduled
                ) {
                    throw ValidationException::withMessages([
                        'inspection' =>
                        'Only a scheduled inspection can be completed.',
                    ]);
                }

                $lockedInspection->update([
                    'status' =>
                    InspectionStatus::Completed->value,
                    'completed_by' => $completedBy->id,
                    'completed_at' => now(),
                    'outcome' => $outcome,
                    'staff_notes' =>
                    $staffNotes
                        ?? $lockedInspection->staff_notes,
                ]);

                return $lockedInspection->fresh([
                    'customer',
                    'house',
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
                $purchase->customer_id !== $customerId
                || $purchase->house_id !== $houseId
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
                $allocation->customer_id !== $customerId
                || $allocation->house_id !== $houseId
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
                'AJD-INS-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            PropertyInspection::query()
            ->where('reference', $reference)
            ->exists()
        );

        return $reference;
    }
}
