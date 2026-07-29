<?php

declare(strict_types=1);

namespace Tests\Feature\Handovers;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\HandoverCondition;
use App\Enums\HandoverStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\User;
use App\Services\Handovers\PropertyHandoverService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyHandoverManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_system_administrator_can_open_handover_workspace(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->get(route('staff.handovers.index'))
            ->assertOk()
            ->assertSeeText('Schedule handover')
            ->assertSeeText('Handover records');
    }

    public function test_system_administrator_can_schedule_handover(): void
    {
        $administrator = $this->administrator();
        $customer = $this->customer();
        $house = $this->house();

        $this->actingAs($administrator)
            ->post(
                route('staff.handovers.store'),
                [
                    'customer_id' => $customer->id,
                    'house_id' => $house->id,
                    'assigned_to' => $administrator->id,
                    'scheduled_at' =>
                    now()->addDays(3)->toDateTimeString(),
                    'customer_representative_name' =>
                    'Test Customer',
                    'location_notes' =>
                    'Meet at the estate administrative office.',
                    'customer_notes' =>
                    'Bring a valid means of identification.',
                    'staff_notes' =>
                    'Confirm all documents before releasing keys.',
                ],
            )
            ->assertRedirect(
                route('staff.handovers.index'),
            )
            ->assertSessionHas('handover_status');

        $this->assertDatabaseHas(
            'property_handovers',
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_by' => $administrator->id,
                'assigned_to' => $administrator->id,
                'status' =>
                HandoverStatus::Scheduled->value,
                'keys_released' => false,
            ],
        );
    }

    public function test_system_administrator_can_complete_handover(): void
    {
        $administrator = $this->administrator();
        $customer = $this->customer();
        $house = $this->house();

        $handover = app(
            PropertyHandoverService::class,
        )->schedule(
            [
                'customer_id' => $customer->id,
                'house_id' => $house->id,
                'scheduled_at' => now()->addDay(),
            ],
            $administrator,
        );

        $this->actingAs($administrator)
            ->patch(
                route(
                    'staff.handovers.complete',
                    $handover,
                ),
                [
                    'handover_condition' =>
                    HandoverCondition::Accepted->value,
                    'outcome' =>
                    'The property was handed over successfully.',
                    'keys_released' => true,
                    'staff_notes' =>
                    'All keys and access documents were released.',
                ],
            )
            ->assertRedirect(
                route('staff.handovers.index'),
            );

        $this->assertDatabaseHas(
            'property_handovers',
            [
                'id' => $handover->id,
                'status' =>
                HandoverStatus::Completed->value,
                'handover_condition' =>
                HandoverCondition::Accepted->value,
                'completed_by' => $administrator->id,
                'keys_released' => true,
                'outcome' =>
                'The property was handed over successfully.',
            ],
        );
    }

    public function test_customer_cannot_access_staff_handover_workspace(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('staff.handovers.index'))
            ->assertForbidden();
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,
            'account_status' =>
            AccountStatus::Active->value,
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
            'account_type' =>
            AccountType::Customer->value,
            'account_status' =>
            AccountStatus::Active->value,
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
