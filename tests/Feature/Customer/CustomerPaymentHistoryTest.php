<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentPlanType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Payment;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerPaymentHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_their_payment_history(): void
    {
        $customer = $this->customer();
        $purchase = $this->purchase($customer);

        Payment::query()->create([
            'reference' => 'AJD-PAY-CUSTOMER-001',
            'purchase_id' => $purchase->id,
            'customer_id' => $customer->id,
            'recorded_by' => User::factory()->create()->id,
            'status' => PaymentStatus::Verified->value,
            'amount' => '20000000.00',
            'payment_method' => PaymentMethod::BankTransfer->value,
            'transaction_reference' => 'CUSTOMER-PAYMENT-001',
            'paid_at' => now(),
            'verified_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('customer.payments.index'))
            ->assertOk()
            ->assertSeeText('AJD-PAY-CUSTOMER-001')
            ->assertSeeText('CUSTOMER-PAYMENT-001');
    }

    public function test_customer_cannot_view_another_customers_payments(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $ownPurchase = $this->purchase($firstCustomer);
        $otherPurchase = $this->purchase($secondCustomer);

        Payment::query()->create([
            'reference' => 'AJD-PAY-OWN-001',
            'purchase_id' => $ownPurchase->id,
            'customer_id' => $firstCustomer->id,
            'recorded_by' => User::factory()->create()->id,
            'status' => PaymentStatus::Verified->value,
            'amount' => '10000000.00',
            'payment_method' => PaymentMethod::BankTransfer->value,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);

        Payment::query()->create([
            'reference' => 'AJD-PAY-OTHER-001',
            'purchase_id' => $otherPurchase->id,
            'customer_id' => $secondCustomer->id,
            'recorded_by' => User::factory()->create()->id,
            'status' => PaymentStatus::Verified->value,
            'amount' => '10000000.00',
            'payment_method' => PaymentMethod::BankTransfer->value,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);

        $this->actingAs($firstCustomer)
            ->get(route('customer.payments.index'))
            ->assertOk()
            ->assertSeeText('AJD-PAY-OWN-001')
            ->assertDontSeeText('AJD-PAY-OTHER-001');
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(RoleName::Customer->value);

        return $customer;
    }

    private function purchase(User $customer): Purchase
    {
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
            'reference' => 'AJD-PUR-'.str()->upper(str()->random(10)),
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
