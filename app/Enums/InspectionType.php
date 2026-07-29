<?php

declare(strict_types=1);

namespace App\Enums;

enum InspectionType: string
{
    case PrePurchase = 'pre_purchase';
    case ConstructionProgress = 'construction_progress';
    case PreHandover = 'pre_handover';
    case FinalHandover = 'final_handover';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::PrePurchase => 'Pre-purchase Inspection',
            self::ConstructionProgress => 'Construction Progress Inspection',
            self::PreHandover => 'Pre-handover Inspection',
            self::FinalHandover => 'Final Handover Inspection',
            self::General => 'General Inspection',
        };
    }
}
