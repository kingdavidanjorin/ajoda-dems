<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PropertyInspection;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class InspectionScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PropertyInspection $inspection,
    ) {
        $this->afterCommit();
        $this->onQueue('notifications');
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $inspection = $this->inspection->loadMissing('house');

        $propertyCode = $inspection->house?->property_code
            ?? 'your property';

        return [
            'event' => 'inspection_scheduled',
            'title' => 'Property inspection scheduled',
            'message' => sprintf(
                'Your inspection for %s is scheduled for %s.',
                $propertyCode,
                $inspection->scheduled_at->format(
                    'd M Y, g:i A',
                ),
            ),
            'reference' => $inspection->reference,
            'inspection_id' => $inspection->id,
            'house_id' => $inspection->house_id,
            'property_code' => $propertyCode,
            'scheduled_at' =>
            $inspection->scheduled_at->toIso8601String(),
            'url' => route('customer.inspections.index'),
        ];
    }
}
