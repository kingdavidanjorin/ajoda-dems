<?php

declare(strict_types=1);

namespace Tests\Feature\Purchases;

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

final class PurchaseConfirmationManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sales_officer_can_confirm_fully_paid_purchase(): void
    {
        $salesOfficer = $this->staff(
            RoleName::SalesOfficer,
        );

        [$purchase, $house] = $this->readyPurchase();

        $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'staff.purchases.confirm',
                    $purchase,
                ),
            )
            ->assertRedirect(
                route('staff.purchases.index'),
            )
            ->assertSessionHas('status');

        $purchase->refresh();
        $house->refresh();

        $this->assertSame(
            PurchaseStatus::Confirmed,
            $purchase->status,
        );

        $this->assertSame(
            PropertyStatus::Sold,
            $house->status,
        );

        $this->assertNull(
            $purchase->active_house_id,
        );

        $this->assertNotNull(
            $purchase->confirmed_at,
        );
    }

    public function test_accounts_officer_cannot_confirm_purchase(): void
    {
        $accountsOfficer = $this->staff(
            RoleName::AccountsOfficer,
        );

        [$purchase, $house] = $this->readyPurchase();

        $this->actingAs($accountsOfficer)
            ->patch(
                route(
                    'staff.purchases.confirm',
                    $purchase,
                ),
            )
            ->assertForbidden();

        $purchase->refresh();
        $house->refresh();

        $this->assertSame(
            PurchaseStatus::ReadyForConfirmation,
            $purchase->status,
        );

        $this->assertSame(
            PropertyStatus::SaleProcessing,
            $house->status,
        );
    }

    private function staff(RoleName $role): User
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role->value);

        return $user;
    }

    /**
     * @return array{Purchase, House}
     */
    private function readyPurchase(): array
    {
        $customer = User::factory()->create();
        $initiator = User::factory()->create();
        $accountsOfficer = User::factory()->create();

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
            'expires_at' => now()->addHours(72),
        ]);

        $purchase = Purchase::query()->create([
            'reference' => 'AJD-PUR-'.str()->upper(str()->random(10)),
            'reservation_id' => $reservation->id,
            'house_id' => $house->id,
            'active_house_id' => $house->id,
            'customer_id' => $customer->id,
            'initiated_by' => $initiator->id,
            'status' => PurchaseStatus::ReadyForConfirmation->value,
            'approved_price' => '50000000.00',
            'payment_plan_type' => PaymentPlanType::Outright->value,
            'effective_date' => today(),
            'initiated_at' => now(),
        ]);

        Payment::query()->create([
            'reference' => 'AJD-PAY-'.str()->upper(str()->random(10)),
            'purchase_id' => $purchase->id,
            'customer_id' => $customer->id,
            'recorded_by' => $accountsOfficer->id,
            'verified_by' => $accountsOfficer->id,
            'status' => PaymentStatus::Verified->value,
            'amount' => '50000000.00',
            'payment_method' => PaymentMethod::BankTransfer->value,
            'transaction_reference' => 'CONFIRM-HTTP-'.str()->upper(
                str()->random(8),
            ),
            'paid_at' => now()->subMinute(),
            'verified_at' => now(),
        ]);

        return [$purchase, $house];
    }
}
