<?php

declare(strict_types=1);

namespace App\Enums;

enum CommunicationStatus: string
{
    case Recorded = 'recorded';
    case FollowUpPending = 'follow_up_pending';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Recorded => 'Recorded',
            self::FollowUpPending => 'Follow-up Pending',
            self::Completed => 'Completed',
        };
    }
}