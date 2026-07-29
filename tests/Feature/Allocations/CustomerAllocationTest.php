<?php

declare(strict_types=1);

namespace Tests\Feature\Allocations;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AllocationStatus;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyAllocation;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_their_allocated_property(): void
    {
        $customer = $this->customer();
        $allocation = $this->allocation(
            $customer,
            'AJD-ALC-CUSTOMER-001',
        );

        $this->actingAs($customer)
            ->get(route('customer.allocations.index'))
            ->assertOk()
            ->assertSeeText($allocation->reference);
    }

    public function test_customer_cannot_view_another_customers_allocation(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $ownAllocation = $this->allocation(
            $firstCustomer,
            'AJD-ALC-OWNER-001',
        );

        $otherAllocation = $this->allocation(
            $secondCustomer,
            'AJD-ALC-OTHER-001',
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.allocations.index'))
            ->assertOk()
            ->assertSeeText($ownAllocation->reference)
            ->assertDontSeeText($otherAllocation->reference);
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

    private function allocation(
        User $customer,
        string $reference,
    ): PropertyAllocation {
        $salesOfficer = User::factory()->create();

        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()->create([
            'estate_id' => $estate->id,
        ]);

        $houseType = HouseType::factory()->create();

        $house = House::factory()->create([
            'estate_id' => $estate->id,
            'estate_phase_id' => $phase->id,
            'house_type_id' => $houseType->id,
            'status' => PropertyStatus::Sold->value,
            'is_published' => true,
        ]);

        $reservation = PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-'.str()->upper(str()->random(10)),
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'status' => ReservationStatus::Converted->value,
            'approved_at' => now(),
            'expires_at' => now(),
        ]);

        $purchase = Purchase::query()->create([
            'reference' => 'AJD-PUR-'.str()->upper(str()->random(10)),
            'reservation_id' => $reservation->id,
            'house_id' => $house->id,
            'active_house_id' => null,
            'customer_id' => $customer->id,
            'initiated_by' => $salesOfficer->id,
            'status' => PurchaseStatus::Confirmed->value,
            'approved_price' => '50000000.00',
            'payment_plan_type' => PaymentPlanType::Outright->value,
            'effective_date' => today(),
            'initiated_at' => now()->subDay(),
            'confirmed_at' => now(),
        ]);

        return PropertyAllocation::query()->create([
            'reference' => $reference,
            'purchase_id' => $purchase->id,
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'allocated_by' => $salesOfficer->id,
            'status' => AllocationStatus::Active->value,
            'allocated_at' => now(),
        ]);
    }
}
