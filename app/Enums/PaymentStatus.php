<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentStatus: string
{
    case PendingVerification = 'pending_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PendingVerification => 'Pending Verification',
            self::Verified => 'Verified',
            self::Rejected => 'Rejected',
            self::Refunded => 'Refunded',
        };
    }
}