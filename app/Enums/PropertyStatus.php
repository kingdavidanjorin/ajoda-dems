<?php

declare(strict_types=1);

namespace App\Enums;

enum PropertyStatus: string
{
    case Available = 'available';
    case TemporarilyReserved = 'temporarily_reserved';
    case SaleProcessing = 'sale_processing';
    case Sold = 'sold';
    case UnderConstruction = 'under_construction';
    case Completed = 'completed';
    case HandedOver = 'handed_over';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Available',
            self::TemporarilyReserved => 'Temporarily Reserved',
            self::SaleProcessing => 'Sale Processing',
            self::Sold => 'Sold',
            self::UnderConstruction => 'Under Construction',
            self::Completed => 'Completed',
            self::HandedOver => 'Handed Over',
        };
    }
}
