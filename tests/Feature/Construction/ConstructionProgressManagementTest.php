<?php

declare(strict_types=1);

namespace Tests\Feature\Construction;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\ConstructionStage;
use App\Enums\PropertyStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConstructionProgressManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_project_manager_can_open_construction_workspace(): void
    {
        $projectManager = $this->staffWithRole(
            RoleName::ProjectManager,
        );

        $this->actingAs($projectManager)
            ->get(route('staff.construction.index'))
            ->assertOk()
            ->assertSeeText('Construction progress')
            ->assertSeeText('Record progress update')
            ->assertSeeText('Construction updates');
    }

    public function test_project_manager_can_record_construction_update(): void
    {
        $projectManager = $this->staffWithRole(
            RoleName::ProjectManager,
        );

        $house = $this->house();

        $this->actingAs($projectManager)
            ->post(
                route('staff.construction.store'),
                [
                    'house_id' => $house->id,
                    'purchase_id' => null,
                    'property_allocation_id' => null,
                    'stage' =>
                    ConstructionStage::Foundation->value,
                    'progress_percentage' => 35,
                    'title' =>
                    'Foundation construction completed',
                    'summary' =>
                    'Foundation work has been completed and approved.',
                    'internal_notes' =>
                    'Site engineer confirmed structural compliance.',
                    'customer_visible' => true,
                    'expected_completion_date' =>
                    now()->addMonths(8)->toDateString(),
                    'occurred_at' =>
                    now()->subMinute()->toDateTimeString(),
                ],
            )
            ->assertRedirect(
                route('staff.construction.index'),
            )
            ->assertSessionHas('construction_status');

        $this->assertDatabaseHas(
            'construction_progress_updates',
            [
                'house_id' => $house->id,
                'recorded_by' => $projectManager->id,
                'stage' =>
                ConstructionStage::Foundation->value,
                'progress_percentage' => 35,
                'title' =>
                'Foundation construction completed',
                'customer_visible' => true,
            ],
        );
    }

    public function test_executive_management_has_read_only_access(): void
    {
        $executive = $this->staffWithRole(
            RoleName::ExecutiveManagement,
        );

        $this->actingAs($executive)
            ->get(route('staff.construction.index'))
            ->assertOk()
            ->assertSeeText('Construction updates')
            ->assertDontSeeText('Record progress update')
            ->assertDontSeeText('Save progress update');
    }

    public function test_customer_service_has_read_only_access(): void
    {
        $customerServiceOfficer = $this->staffWithRole(
            RoleName::CustomerServiceOfficer,
        );

        $this->actingAs($customerServiceOfficer)
            ->get(route('staff.construction.index'))
            ->assertOk()
            ->assertSeeText('Construction updates')
            ->assertDontSeeText('Record progress update');
    }

    public function test_read_only_staff_cannot_record_construction_update(): void
    {
        $executive = $this->staffWithRole(
            RoleName::ExecutiveManagement,
        );

        $house = $this->house();

        $this->actingAs($executive)
            ->post(
                route('staff.construction.store'),
                [
                    'house_id' => $house->id,
                    'stage' =>
                    ConstructionStage::Foundation->value,
                    'progress_percentage' => 20,
                    'title' => 'Unauthorised construction update',
                    'summary' =>
                    'This update must not be recorded.',
                    'customer_visible' => false,
                    'occurred_at' =>
                    now()->subMinute()->toDateTimeString(),
                ],
            )
            ->assertForbidden();

        $this->assertDatabaseMissing(
            'construction_progress_updates',
            [
                'title' => 'Unauthorised construction update',
            ],
        );
    }

    public function test_unrelated_staff_cannot_access_construction_workspace(): void
    {
        $salesOfficer = $this->staffWithRole(
            RoleName::SalesOfficer,
        );

        $this->actingAs($salesOfficer)
            ->get(route('staff.construction.index'))
            ->assertForbidden();
    }

    public function test_customer_cannot_access_staff_construction_workspace(): void
    {
        /** @var User $customer */
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        $this->actingAs($customer)
            ->get(route('staff.construction.index'))
            ->assertForbidden();
    }

    private function staffWithRole(RoleName $role): User
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $staff->assignRole($role->value);

        return $staff;
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
            'status' =>
            PropertyStatus::UnderConstruction->value,
            'is_published' => true,
        ]);
    }
}
