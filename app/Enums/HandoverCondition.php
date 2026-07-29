<?php

declare(strict_types=1);

namespace App\Enums;

enum HandoverCondition: string
{
    case Accepted = 'accepted';
    case AcceptedWithDefects = 'accepted_with_defects';
    case RectificationRequired = 'rectification_required';

    public function label(): string
    {
        return match ($this) {
            self::Accepted => 'Accepted',
            self::AcceptedWithDefects => 'Accepted with Defects',
            self::RectificationRequired => 'Rectification Required',
        };
    }
}
