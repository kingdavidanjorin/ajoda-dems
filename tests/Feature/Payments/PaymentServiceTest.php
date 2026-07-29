<?php

declare(strict_types=1);

namespace Tests\Feature\Payments;

use App\Enums\PaymentMethod;
use App\Enums\PaymentPlanType;
use App\Enums\PaymentStatus;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\Payment;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_can_be_recorded(): void
    {
        [$purchase] = $this->purchase();

        $accountsOfficer = User::factory()->create();

        $payment = app(PaymentService::class)->record(
            $purchase,
            $accountsOfficer,
            '20000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-TRANSFER-001',
            'First instalment payment.',
        );

        $purchase->refresh();

        $this->assertSame(
            PaymentStatus::PendingVerification,
            $payment->status,
        );

        $this->assertSame(
            PurchaseStatus::PaymentUnderReview,
            $purchase->status,
        );

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'purchase_id' => $purchase->id,
            'customer_id' => $purchase->customer_id,
            'amount' => '20000000.00',
            'transaction_reference' => 'BANK-TRANSFER-001',
        ]);
    }

    public function test_partial_verified_payment_returns_purchase_to_awaiting_payment(): void
    {
        [$purchase] = $this->purchase();

        $recorder = User::factory()->create();
        $verifier = User::factory()->create();

        $payment = app(PaymentService::class)->record(
            $purchase,
            $recorder,
            '20000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-TRANSFER-002',
        );

        app(PaymentService::class)->verify(
            $payment,
            $verifier,
            'Payment confirmed in the bank statement.',
        );

        $payment->refresh();
        $purchase->refresh();

        $this->assertSame(
            PaymentStatus::Verified,
            $payment->status,
        );

        $this->assertSame(
            PurchaseStatus::AwaitingPayment,
            $purchase->status,
        );

        $this->assertSame(
            $verifier->id,
            $payment->verified_by,
        );

        $this->assertNotNull(
            $payment->verified_at,
        );
    }

    public function test_full_verified_payment_marks_purchase_ready_for_confirmation(): void
    {
        [$purchase] = $this->purchase();

        $recorder = User::factory()->create();
        $verifier = User::factory()->create();

        $firstPayment = app(PaymentService::class)->record(
            $purchase,
            $recorder,
            '20000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-TRANSFER-003',
        );

        app(PaymentService::class)->verify(
            $firstPayment,
            $verifier,
        );

        $secondPayment = app(PaymentService::class)->record(
            $purchase,
            $recorder,
            '30000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-TRANSFER-004',
        );

        app(PaymentService::class)->verify(
            $secondPayment,
            $verifier,
        );

        $purchase->refresh();

        $this->assertSame(
            PurchaseStatus::ReadyForConfirmation,
            $purchase->status,
        );

        $verifiedTotal = Payment::query()
            ->where('purchase_id', $purchase->id)
            ->where(
                'status',
                PaymentStatus::Verified->value,
            )
            ->sum('amount');

        $this->assertSame(
            '50000000.00',
            number_format(
                (float) $verifiedTotal,
                2,
                '.',
                '',
            ),
        );
    }

    public function test_rejected_payment_is_not_counted(): void
    {
        [$purchase] = $this->purchase();

        $recorder = User::factory()->create();
        $verifier = User::factory()->create();

        $payment = app(PaymentService::class)->record(
            $purchase,
            $recorder,
            '50000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-TRANSFER-005',
        );

        app(PaymentService::class)->reject(
            $payment,
            $verifier,
            'Transaction could not be confirmed.',
        );

        $payment->refresh();
        $purchase->refresh();

        $this->assertSame(
            PaymentStatus::Rejected,
            $payment->status,
        );

        $this->assertSame(
            PurchaseStatus::AwaitingPayment,
            $purchase->status,
        );

        $this->assertNotNull(
            $payment->rejected_at,
        );

        $this->assertSame(
            '0.00',
            number_format(
                (float) Payment::query()
                    ->where('purchase_id', $purchase->id)
                    ->where(
                        'status',
                        PaymentStatus::Verified->value,
                    )
                    ->sum('amount'),
                2,
                '.',
                '',
            ),
        );
    }

    public function test_duplicate_transaction_reference_is_rejected(): void
    {
        [$firstPurchase] = $this->purchase();
        [$secondPurchase] = $this->purchase();

        $recorder = User::factory()->create();

        app(PaymentService::class)->record(
            $firstPurchase,
            $recorder,
            '10000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-DUPLICATE-001',
        );

        $this->expectException(
            ValidationException::class,
        );

        app(PaymentService::class)->record(
            $secondPurchase,
            $recorder,
            '10000000.00',
            PaymentMethod::BankTransfer,
            now()->toDateTimeString(),
            'BANK-DUPLICATE-001',
        );
    }

    /**
     * @return array{Purchase, User, House}
     */
    private function purchase(): array
    {
        $customer = User::factory()->create();
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
            'expires_at' => now()->addHours(72),
        ]);

        $purchase = Purchase::query()->create([
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

        return [
            $purchase,
            $customer,
            $house,
        ];
    }
}
