<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

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

final class PaymentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_accounts_officer_can_verify_pending_payment(): void
    {
        $accountsOfficer = $this->staff(
            RoleName::AccountsOfficer,
        );

        $payment = $this->pendingPayment();

        $this->actingAs($accountsOfficer)
            ->patch(
                route(
                    'staff.payments.verify',
                    $payment,
                ),
                [
                    'verification_notes' =>
                    'Payment confirmed by Accounts.',
                ],
            )
            ->assertRedirect(
                route('staff.payments.index'),
            );

        $payment->refresh();

        $this->assertSame(
            PaymentStatus::Verified,
            $payment->status,
        );

        $this->assertSame(
            $accountsOfficer->id,
            $payment->verified_by,
        );

        $this->assertNotNull(
            $payment->verified_at,
        );
    }

    public function test_sales_officer_cannot_verify_payment(): void
    {
        $salesOfficer = $this->staff(
            RoleName::SalesOfficer,
        );

        $payment = $this->pendingPayment();

        $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'staff.payments.verify',
                    $payment,
                ),
                [
                    'verification_notes' =>
                    'Attempted unauthorized verification.',
                ],
            )
            ->assertForbidden();

        $payment->refresh();

        $this->assertSame(
            PaymentStatus::PendingVerification,
            $payment->status,
        );

        $this->assertNull(
            $payment->verified_by,
        );

        $this->assertNull(
            $payment->verified_at,
        );
    }

    public function test_sales_officer_cannot_reject_payment(): void
    {
        $salesOfficer = $this->staff(
            RoleName::SalesOfficer,
        );

        $payment = $this->pendingPayment();

        $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'staff.payments.reject',
                    $payment,
                ),
                [
                    'verification_notes' =>
                    'Attempted unauthorized rejection.',
                ],
            )
            ->assertForbidden();

        $payment->refresh();

        $this->assertSame(
            PaymentStatus::PendingVerification,
            $payment->status,
        );

        $this->assertNull(
            $payment->verified_by,
        );

        $this->assertNull(
            $payment->rejected_at,
        );
    }

    private function staff(RoleName $role): User
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(
            $role->value,
        );

        return $user;
    }

    private function pendingPayment(): Payment
    {
        $customer = User::factory()->create();

        $recordedBy = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

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
            'reference' =>
            'AJD-RSV-' . str()->upper(
                str()->random(10),
            ),
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'status' => ReservationStatus::Converted->value,
            'approved_at' => now(),
            'expires_at' => now()->addHours(72),
        ]);

        $purchase = Purchase::query()->create([
            'reference' =>
            'AJD-PUR-' . str()->upper(
                str()->random(10),
            ),
            'reservation_id' => $reservation->id,
            'house_id' => $house->id,
            'active_house_id' => $house->id,
            'customer_id' => $customer->id,
            'initiated_by' => $recordedBy->id,
            'status' => PurchaseStatus::PaymentUnderReview->value,
            'approved_price' => '50000000.00',
            'payment_plan_type' =>
            PaymentPlanType::Instalment->value,
            'effective_date' => today(),
            'initiated_at' => now(),
        ]);

        return Payment::query()->create([
            'reference' =>
            'AJD-PAY-' . str()->upper(
                str()->random(10),
            ),
            'purchase_id' => $purchase->id,
            'customer_id' => $customer->id,
            'recorded_by' => $recordedBy->id,
            'verified_by' => null,
            'status' =>
            PaymentStatus::PendingVerification->value,
            'amount' => '10000000.00',
            'payment_method' =>
            PaymentMethod::BankTransfer->value,
            'transaction_reference' =>
            'AUTH-TEST-' . str()->upper(
                str()->random(10),
            ),
            'paid_at' => now(),
            'notes' => 'Payment authorization security test.',
        ]);
    }
}
