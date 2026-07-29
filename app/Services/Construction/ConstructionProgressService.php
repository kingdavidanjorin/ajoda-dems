<?php

declare(strict_types=1);

namespace App\Services\Construction;

use App\Models\ConstructionProgressUpdate;
use App\Models\PropertyAllocation;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ConstructionProgressService
{
    /**
     * @param array<string, mixed> $data
     */
    public function record(
        array $data,
        User $recordedBy,
    ): ConstructionProgressUpdate {
        $houseId = (int) $data['house_id'];

        $this->validateRelatedRecords(
            $houseId,
            $data,
        );

        return DB::transaction(
            fn(): ConstructionProgressUpdate =>
            ConstructionProgressUpdate::query()->create([
                'reference' => $this->generateReference(),
                'house_id' => $houseId,
                'purchase_id' =>
                $data['purchase_id'] ?? null,
                'property_allocation_id' =>
                $data['property_allocation_id'] ?? null,
                'recorded_by' => $recordedBy->id,
                'stage' => $data['stage'],
                'progress_percentage' =>
                (int) $data['progress_percentage'],
                'title' => $data['title'],
                'summary' => $data['summary'],
                'internal_notes' =>
                $data['internal_notes'] ?? null,
                'customer_visible' =>
                (bool) ($data['customer_visible'] ?? false),
                'expected_completion_date' =>
                $data['expected_completion_date'] ?? null,
                'occurred_at' => $data['occurred_at'],
            ]),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateRelatedRecords(
        int $houseId,
        array $data,
    ): void {
        $purchase = null;
        $allocation = null;

        if (! empty($data['purchase_id'])) {
            $purchase = Purchase::query()
                ->findOrFail((int) $data['purchase_id']);

            if ((int) $purchase->house_id !== $houseId) {
                throw ValidationException::withMessages([
                    'purchase_id' =>
                    'The selected purchase does not belong to the selected property.',
                ]);
            }
        }

        if (! empty($data['property_allocation_id'])) {
            $allocation = PropertyAllocation::query()
                ->findOrFail(
                    (int) $data['property_allocation_id'],
                );

            if ((int) $allocation->house_id !== $houseId) {
                throw ValidationException::withMessages([
                    'property_allocation_id' =>
                    'The selected allocation does not belong to the selected property.',
                ]);
            }
        }

        if (
            $purchase !== null
            && $allocation !== null
            && (int) $allocation->purchase_id
            !== (int) $purchase->id
        ) {
            throw ValidationException::withMessages([
                'property_allocation_id' =>
                'The selected allocation does not belong to the selected purchase.',
            ]);
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-CNS-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            ConstructionProgressUpdate::query()
            ->where('reference', $reference)
            ->exists()
        );

        return $reference;
    }
}
