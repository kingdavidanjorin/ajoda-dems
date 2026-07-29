<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentPlanType: string
{
    case Outright = 'outright';
    case Instalment = 'instalment';

    public function label(): string
    {
        return match ($this) {
            self::Outright => 'Outright Payment',
            self::Instalment => 'Instalment Payment',
        };
    }
}
