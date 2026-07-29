<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentType: string
{
    case Identification = 'identification';
    case PaymentEvidence = 'payment_evidence';
    case PaymentReceipt = 'payment_receipt';
    case SaleAgreement = 'sale_agreement';
    case AllocationLetter = 'allocation_letter';
    case BuildingPlan = 'building_plan';
    case InspectionReport = 'inspection_report';
    case HandoverDocument = 'handover_document';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Identification => 'Identification',
            self::PaymentEvidence => 'Payment Evidence',
            self::PaymentReceipt => 'Payment Receipt',
            self::SaleAgreement => 'Sale Agreement',
            self::AllocationLetter => 'Allocation Letter',
            self::BuildingPlan => 'Building Plan',
            self::InspectionReport => 'Inspection Report',
            self::HandoverDocument => 'Handover Document',
            self::Other => 'Other',
        };
    }
}