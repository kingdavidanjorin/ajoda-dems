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
use App\Models\PropertyHandover;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerHandoverHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_own_scheduled_handover(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $handover = $this->handover(
            customer: $customer,
            staff: $staff,
            house: $house,
            completed: false,
        );

        $this->actingAs($customer)
            ->get(route('customer.handovers.index'))
            ->assertOk()
            ->assertSeeText($handover->reference)
            ->assertSeeText($house->property_code)
            ->assertSeeText(
                'Meet at the estate administrative office.',
            );
    }

    public function test_customer_cannot_view_another_customers_handover(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();
        $staff = $this->staff();

        $ownHandover = $this->handover(
            customer: $firstCustomer,
            staff: $staff,
            house: $this->house(),
            completed: false,
        );

        $otherHandover = $this->handover(
            customer: $secondCustomer,
            staff: $staff,
            house: $this->house(),
            completed: false,
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.handovers.index'))
            ->assertOk()
            ->assertSeeText($ownHandover->reference)
            ->assertDontSeeText($otherHandover->reference);
    }

    public function test_customer_can_view_completed_handover_outcome(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $handover = $this->handover(
            customer: $customer,
            staff: $staff,
            house: $this->house(),
            completed: true,
        );

        $this->actingAs($customer)
            ->get(route('customer.handovers.index'))
            ->assertOk()
            ->assertSeeText($handover->reference)
            ->assertSeeText('Accepted')
            ->assertSeeText('Keys released')
            ->assertSeeText(
                'The property was handed over successfully.',
            );
    }

    public function test_customer_cannot_view_internal_staff_notes(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $this->handover(
            customer: $customer,
            staff: $staff,
            house: $this->house(),
            completed: true,
            staffNotes: 'Confidential internal handover note.',
        );

        $this->actingAs($customer)
            ->get(route('customer.handovers.index'))
            ->assertOk()
            ->assertSeeText(
                'The property was handed over successfully.',
            )
            ->assertDontSeeText(
                'Confidential internal handover note.',
            );
    }

    private function handover(
        User $customer,
        User $staff,
        House $house,
        bool $completed,
        string $staffNotes = 'Internal preparation note.',
    ): PropertyHandover {
        return PropertyHandover::query()->create([
            'reference' =>
            'AJD-HND-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'house_id' => $house->id,
            'scheduled_by' => $staff->id,
            'assigned_to' => $staff->id,
            'completed_by' =>
            $completed ? $staff->id : null,
            'status' => $completed
                ? HandoverStatus::Completed->value
                : HandoverStatus::Scheduled->value,
            'handover_condition' => $completed
                ? HandoverCondition::Accepted->value
                : null,
            'scheduled_at' => now()->addDay(),
            'completed_at' =>
            $completed ? now() : null,
            'possession_released_at' =>
            $completed ? now() : null,
            'keys_released' => $completed,
            'customer_representative_name' =>
            $customer->name,
            'location_notes' =>
            'Meet at the estate administrative office.',
            'customer_notes' =>
            'Bring a valid means of identification.',
            'staff_notes' => $staffNotes,
            'outcome' => $completed
                ? 'The property was handed over successfully.'
                : null,
        ]);
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
