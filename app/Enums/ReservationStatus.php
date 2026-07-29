<?php

declare(strict_types=1);

namespace App\Enums;

enum ReservationStatus: string
{
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case Converted = 'converted';

    public function label(): string
    {
        return match ($this) {
            self::PendingReview => 'Pending Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
            self::Expired => 'Expired',
            self::Converted => 'Converted to Sale',
        };
    }
}