<?php

declare(strict_types=1);

namespace App\Enums;

enum AllocationStatus: string
{
    case Active = 'active';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Cancelled => 'Cancelled',
        };
    }
}
