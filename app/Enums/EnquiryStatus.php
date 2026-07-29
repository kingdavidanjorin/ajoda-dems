<?php

declare(strict_types=1);

namespace App\Enums;

enum EnquiryStatus: string
{
    case NewEnquiry = 'new';
    case Contacted = 'contacted';
    case Qualified = 'qualified';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NewEnquiry => 'New',
            self::Contacted => 'Contacted',
            self::Qualified => 'Qualified',
            self::Closed => 'Closed',
        };
    }
}
