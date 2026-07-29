<?php

declare(strict_types=1);

namespace Tests\Feature\Inspections;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyInspection;
use App\Models\User;
use App\Services\Inspections\PropertyInspectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyInspectionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_property_inspection_can_be_scheduled(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $inspection = app(
            PropertyInspectionService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'assigned_to' => $staff->id,
                'inspection_type' =>
                InspectionType::PrePurchase->value,
                'scheduled_at' => now()->addDays(2),
                'location_notes' =>
                'Meet at the estate entrance.',
                'customer_notes' =>
                'Customer requested an afternoon inspection.',
            ],
            $staff,
        );

        $this->assertStringStartsWith(
            'AJD-INS-',
            $inspection->reference,
        );

        $this->assertSame(
            InspectionStatus::Scheduled,
            $inspection->status,
        );

        $this->assertDatabaseHas(
            'property_inspections',
            [
                'id' => $inspection->id,
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $staff->id,
                'assigned_to' => $staff->id,
                'inspection_type' =>
                InspectionType::PrePurchase->value,
                'status' =>
                InspectionStatus::Scheduled->value,
            ],
        );
    }

    public function test_scheduled_inspection_can_be_completed(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $inspection = app(
            PropertyInspectionService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'inspection_type' =>
                InspectionType::General->value,
                'scheduled_at' => now()->addDay(),
            ],
            $staff,
        );

        $completed = app(
            PropertyInspectionService::class,
        )->complete(
            $inspection,
            $staff,
            'The customer inspected the property successfully.',
            'No defects were reported during the inspection.',
        );

        $this->assertSame(
            InspectionStatus::Completed,
            $completed->status,
        );

        $this->assertSame(
            $staff->id,
            $completed->completed_by,
        );

        $this->assertNotNull(
            $completed->completed_at,
        );

        $this->assertDatabaseHas(
            'property_inspections',
            [
                'id' => $inspection->id,
                'status' =>
                InspectionStatus::Completed->value,
                'completed_by' => $staff->id,
                'outcome' =>
                'The customer inspected the property successfully.',
            ],
        );
    }

    public function test_repeated_completion_does_not_change_completed_inspection(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $inspection = app(
            PropertyInspectionService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'inspection_type' =>
                InspectionType::General->value,
                'scheduled_at' => now()->addDay(),
            ],
            $staff,
        );

        $service = app(PropertyInspectionService::class);

        $service->complete(
            $inspection,
            $staff,
            'Inspection completed.',
        );

        $service->complete(
            $inspection->fresh(),
            $staff,
            'This second outcome should not replace the first.',
        );

        $this->assertDatabaseCount(
            'property_inspections',
            1,
        );

        $this->assertDatabaseHas(
            'property_inspections',
            [
                'id' => $inspection->id,
                'outcome' => 'Inspection completed.',
            ],
        );
    }

    private function customer(): User
    {
        return User::factory()->create([
            'account_type' =>
            AccountType::Customer->value,
            'account_status' =>
            AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,
            'account_status' =>
            AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);
    }

    private function house(): House
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()->create([
            'estate_id' => $estate->id,
        ]);

        $houseType = HouseType::factory()->create();

        return House::factory()->create([
            'estate_id' => $estate->id,
            'estate_phase_id' => $phase->id,
            'house_type_id' => $houseType->id,
            'status' => PropertyStatus::Available->value,
            'is_published' => true,
        ]);
    }
}
