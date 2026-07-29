<?php

declare(strict_types=1);

namespace App\Services\Communications;

use App\Enums\CommunicationStatus;
use App\Models\CustomerCommunication;
use App\Models\PropertyEnquiry;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CustomerCommunicationService
{
    /**
     * @param array<string, mixed> $data
     */
    public function record(
        array $data,
        User $recordedBy,
    ): CustomerCommunication {
        $customerId = (int) $data['customer_id'];

        $this->validateRelatedRecords(
            $customerId,
            $data,
        );

        $status = ! empty($data['follow_up_at'])
            ? CommunicationStatus::FollowUpPending
            : CommunicationStatus::Recorded;

        return DB::transaction(
            fn (): CustomerCommunication =>
                CustomerCommunication::query()->create([
                    'reference' => $this->generateReference(),
                    'customer_id' => $customerId,
                    'recorded_by' => $recordedBy->id,
                    'assigned_to' =>
                        $data['assigned_to'] ?? null,
                    'completed_by' => null,
                    'property_enquiry_id' =>
                        $data['property_enquiry_id'] ?? null,
                    'purchase_id' =>
                        $data['purchase_id'] ?? null,
                    'house_id' =>
                        $data['house_id'] ?? null,
                    'channel' => $data['channel'],
                    'direction' => $data['direction'],
                    'status' => $status->value,
                    'subject' => $data['subject'],
                    'summary' => $data['summary'],
                    'customer_visible' =>
                        (bool) ($data['customer_visible'] ?? false),
                    'occurred_at' => $data['occurred_at'],
                    'follow_up_at' =>
                        $data['follow_up_at'] ?? null,
                    'completed_at' => null,
                ]),
        );
    }

    public function complete(
        CustomerCommunication $communication,
        User $completedBy,
    ): CustomerCommunication {
        return DB::transaction(
            function () use (
                $communication,
                $completedBy,
            ): CustomerCommunication {
                $lockedCommunication =
                    CustomerCommunication::query()
                        ->lockForUpdate()
                        ->findOrFail($communication->id);

                if (
                    $lockedCommunication->status
                    === CommunicationStatus::Completed
                ) {
                    return $lockedCommunication;
                }

                $lockedCommunication->update([
                    'status' =>
                        CommunicationStatus::Completed->value,
                    'completed_by' => $completedBy->id,
                    'completed_at' => now(),
                ]);

                return $lockedCommunication->fresh([
                    'customer',
                    'recordedBy',
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
        array $data,
    ): void {
        if (! empty($data['purchase_id'])) {
            $purchase = Purchase::query()
                ->findOrFail((int) $data['purchase_id']);

            if ($purchase->customer_id !== $customerId) {
                throw ValidationException::withMessages([
                    'purchase_id' =>
                        'The selected purchase does not belong to the selected customer.',
                ]);
            }
        }

        if (! empty($data['property_enquiry_id'])) {
            $enquiry = PropertyEnquiry::query()
                ->findOrFail(
                    (int) $data['property_enquiry_id'],
                );

            if (
                $enquiry->user_id !== null
                && $enquiry->user_id !== $customerId
            ) {
                throw ValidationException::withMessages([
                    'property_enquiry_id' =>
                        'The selected enquiry does not belong to the selected customer.',
                ]);
            }
        }
    }

    private function generateReference(): string
    {
        do {
            $reference = sprintf(
                'AJD-COM-%s-%s',
                now()->format('Ymd'),
                Str::upper(Str::random(6)),
            );
        } while (
            CustomerCommunication::query()
                ->where('reference', $reference)
                ->exists()
        );

        return $reference;
    }
}