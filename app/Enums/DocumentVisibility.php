<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentVisibility: string
{
    case StaffOnly = 'staff_only';
    case CustomerVisible = 'customer_visible';

    public function label(): string
    {
        return match ($this) {
            self::StaffOnly => 'Staff Only',
            self::CustomerVisible => 'Customer Visible',
        };
    }
}