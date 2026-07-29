<?php

declare(strict_types=1);

namespace App\Enums;

enum ConstructionStage: string
{
    case Planning = 'planning';
    case SitePreparation = 'site_preparation';
    case Foundation = 'foundation';
    case StructuralWork = 'structural_work';
    case Roofing = 'roofing';
    case MechanicalElectricalPlumbing = 'mechanical_electrical_plumbing';
    case Finishing = 'finishing';
    case FinalInspection = 'final_inspection';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::SitePreparation => 'Site Preparation',
            self::Foundation => 'Foundation',
            self::StructuralWork => 'Structural Work',
            self::Roofing => 'Roofing',
            self::MechanicalElectricalPlumbing =>
            'Mechanical, Electrical and Plumbing',
            self::Finishing => 'Finishing',
            self::FinalInspection => 'Final Inspection',
            self::Completed => 'Completed',
        };
    }
}
