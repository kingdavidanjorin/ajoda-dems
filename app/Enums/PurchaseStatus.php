<?php

declare(strict_types=1);

namespace App\Enums;

enum PurchaseStatus: string
{
    case AwaitingPayment = 'awaiting_payment';
    case PaymentUnderReview = 'payment_under_review';
    case ReadyForConfirmation = 'ready_for_confirmation';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingPayment => 'Awaiting Payment',
            self::PaymentUnderReview => 'Payment Under Review',
            self::ReadyForConfirmation => 'Ready for Confirmation',
            self::Confirmed => 'Confirmed',
            self::Cancelled => 'Cancelled',
        };
    }
}