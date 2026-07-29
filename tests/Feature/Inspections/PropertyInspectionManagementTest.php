<?php

declare(strict_types=1);

namespace Tests\Feature\Inspections;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Enums\PropertyStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyInspection;
use App\Models\User;
use App\Services\Inspections\PropertyInspectionService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyInspectionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_system_administrator_can_open_inspection_workspace(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('staff.inspections.index'))
            ->assertOk()
            ->assertSeeText('Schedule inspection')
            ->assertSeeText('Inspection records');
    }

    public function test_system_administrator_can_schedule_inspection(): void
    {
        $administrator = $this->administrator();
        $customer = $this->customer();
        $house = $this->house();

        $this->actingAs($administrator)
            ->post(
                route('staff.inspections.store'),
                [
                    'customer_id' => $customer->id,
                    'house_id' => $house->id,
                    'assigned_to' => $administrator->id,
                    'inspection_type' =>
                    InspectionType::PrePurchase->value,
                    'scheduled_at' =>
                    now()->addDays(2)->toDateTimeString(),
                    'location_notes' =>
                    'Meet at the estate entrance.',
                    'customer_notes' =>
                    'Customer requested an afternoon appointment.',
                    'staff_notes' =>
                    'Confirm access before arrival.',
                ],
            )
            ->assertRedirect(
                route('staff.inspections.index'),
            )
            ->assertSessionHas('inspection_status');

        $this->assertDatabaseHas(
            'property_inspections',
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $administrator->id,
                'assigned_to' => $administrator->id,
                'inspection_type' =>
                InspectionType::PrePurchase->value,
                'status' =>
                InspectionStatus::Scheduled->value,
            ],
        );
    }

    public function test_system_administrator_can_complete_inspection(): void
    {
        $administrator = $this->administrator();
        $customer = $this->customer();
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
            $administrator,
        );

        $this->actingAs($administrator)
            ->patch(
                route(
                    'staff.inspections.complete',
                    $inspection,
                ),
                [
                    'outcome' =>
                    'The customer inspected the property successfully.',
                    'staff_notes' =>
                    'No defects were reported.',
                ],
            )
            ->assertRedirect(
                route('staff.inspections.index'),
            );

        $this->assertDatabaseHas(
            'property_inspections',
            [
                'id' => $inspection->id,
                'status' =>
                InspectionStatus::Completed->value,
                'completed_by' => $administrator->id,
                'outcome' =>
                'The customer inspected the property successfully.',
            ],
        );
    }

    public function test_customer_cannot_access_staff_inspection_workspace(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('staff.inspections.index'))
            ->assertForbidden();
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $administrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        return $administrator;
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        return $customer;
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
