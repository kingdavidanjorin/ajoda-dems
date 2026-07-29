<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\PropertyHandover;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class HandoverScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly PropertyHandover $handover,
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
        $handover = $this->handover->loadMissing('house');

        $propertyCode = $handover->house?->property_code
            ?? 'your property';

        return [
            'event' => 'handover_scheduled',
            'title' => 'Property handover scheduled',
            'message' => sprintf(
                'Your handover for %s is scheduled for %s.',
                $propertyCode,
                $handover->scheduled_at->format(
                    'd M Y, g:i A',
                ),
            ),
            'reference' => $handover->reference,
            'handover_id' => $handover->id,
            'house_id' => $handover->house_id,
            'property_code' => $propertyCode,
            'scheduled_at' =>
            $handover->scheduled_at->toIso8601String(),
        ];
    }
}
