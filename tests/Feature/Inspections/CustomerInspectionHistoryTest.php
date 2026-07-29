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
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerInspectionHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_own_inspection(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $inspection = $this->inspection(
            customer: $customer,
            staff: $staff,
            house: $house,
            outcome: null,
        );

        $this->actingAs($customer)
            ->get(route('customer.inspections.index'))
            ->assertOk()
            ->assertSeeText($inspection->reference)
            ->assertSeeText($house->property_code)
            ->assertSeeText('Meet at the estate entrance.');
    }

    public function test_customer_cannot_view_another_customers_inspection(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();
        $staff = $this->staff();

        $firstInspection = $this->inspection(
            customer: $firstCustomer,
            staff: $staff,
            house: $this->house(),
            outcome: null,
        );

        $secondInspection = $this->inspection(
            customer: $secondCustomer,
            staff: $staff,
            house: $this->house(),
            outcome: null,
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.inspections.index'))
            ->assertOk()
            ->assertSeeText($firstInspection->reference)
            ->assertDontSeeText($secondInspection->reference);
    }

    public function test_customer_can_view_completed_inspection_outcome(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $inspection = $this->inspection(
            customer: $customer,
            staff: $staff,
            house: $this->house(),
            outcome: 'The property passed the final inspection.',
        );

        $this->actingAs($customer)
            ->get(route('customer.inspections.index'))
            ->assertOk()
            ->assertSeeText($inspection->reference)
            ->assertSeeText(
                'The property passed the final inspection.',
            );
    }

    public function test_customer_cannot_view_internal_staff_notes(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();

        $this->inspection(
            customer: $customer,
            staff: $staff,
            house: $this->house(),
            outcome: 'Inspection completed successfully.',
            staffNotes: 'Confidential internal management note.',
        );

        $this->actingAs($customer)
            ->get(route('customer.inspections.index'))
            ->assertOk()
            ->assertSeeText('Inspection completed successfully.')
            ->assertDontSeeText(
                'Confidential internal management note.',
            );
    }

    private function inspection(
        User $customer,
        User $staff,
        House $house,
        ?string $outcome,
        string $staffNotes = 'Internal preparation note.',
    ): PropertyInspection {
        $completed = $outcome !== null;

        return PropertyInspection::query()->create([
            'reference' =>
            'AJD-INS-' . Str::upper(Str::random(10)),
            'customer_id' => $customer->id,
            'house_id' => $house->id,
            'scheduled_by' => $staff->id,
            'assigned_to' => $staff->id,
            'completed_by' =>
            $completed ? $staff->id : null,
            'inspection_type' =>
            InspectionType::General->value,
            'status' => $completed
                ? InspectionStatus::Completed->value
                : InspectionStatus::Scheduled->value,
            'scheduled_at' => now()->addDay(),
            'completed_at' =>
            $completed ? now() : null,
            'location_notes' =>
            'Meet at the estate entrance.',
            'customer_notes' =>
            'Bring a valid means of identification.',
            'staff_notes' => $staffNotes,
            'outcome' => $outcome,
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
