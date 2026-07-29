<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerPurchaseProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_their_purchase_progress(): void
    {
        $customer = $this->customer();

        $purchase = $this->purchase(
            $customer,
            'AJD-PUR-CUSTOMER-001',
        );

        $this->actingAs($customer)
            ->get(route('customer.purchases.index'))
            ->assertOk()
            ->assertSeeText($purchase->reference);
    }

    public function test_customer_cannot_view_another_customers_purchase(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $ownPurchase = $this->purchase(
            $firstCustomer,
            'AJD-PUR-OWNER-001',
        );

        $otherPurchase = $this->purchase(
            $secondCustomer,
            'AJD-PUR-OTHER-001',
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.purchases.index'))
            ->assertOk()
            ->assertSeeText($ownPurchase->reference)
            ->assertDontSeeText($otherPurchase->reference);
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

    private function purchase(
        User $customer,
        string $reference,
    ): Purchase {
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
            'status' => PropertyStatus::SaleProcessing->value,
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

        return Purchase::query()->create([
            'reference' => $reference,
            'reservation_id' => $reservation->id,
            'house_id' => $house->id,
            'active_house_id' => $house->id,
            'customer_id' => $customer->id,
            'initiated_by' => $salesOfficer->id,
            'status' => PurchaseStatus::AwaitingPayment->value,
            'approved_price' => '50000000.00',
            'payment_plan_type' => PaymentPlanType::Instalment->value,
            'effective_date' => today(),
            'initiated_at' => now(),
        ]);
    }
}
