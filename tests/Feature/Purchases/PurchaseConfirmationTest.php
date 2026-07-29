<?php

declare(strict_types=1);

namespace Tests\Feature\Purchases;

use App\Enums\AllocationStatus;
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
use App\Services\Purchases\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PurchaseConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fully_paid_purchase_can_be_confirmed(): void
    {
        [$purchase, $house, $salesOfficer] =
            $this->readyPurchase('50000000.00');

        $confirmedPurchase = app(PurchaseService::class)
            ->confirm(
                $purchase,
                $salesOfficer,
            );

        $house->refresh();

        $this->assertSame(
            PurchaseStatus::Confirmed,
            $confirmedPurchase->status,
        );

        $this->assertNotNull(
            $confirmedPurchase->confirmed_at,
        );

        $this->assertNull(
            $confirmedPurchase->active_house_id,
        );

        $this->assertSame(
            PropertyStatus::Sold,
            $house->status,
        );

        $this->assertDatabaseHas(
            'house_status_histories',
            [
                'house_id' => $house->id,
                'changed_by' => $salesOfficer->id,
                'from_status' => PropertyStatus::SaleProcessing->value,
                'to_status' => PropertyStatus::Sold->value,
            ],
        );

        $this->assertDatabaseHas(
            'property_allocations',
            [
                'purchase_id' => $confirmedPurchase->id,
                'house_id' => $house->id,
                'customer_id' => $confirmedPurchase->customer_id,
                'allocated_by' => $salesOfficer->id,
                'status' => AllocationStatus::Active->value,
            ],
        );

        $this->assertNotNull(
            $confirmedPurchase->allocation,
        );
    }

    public function test_purchase_cannot_be_confirmed_with_insufficient_verified_payment(): void
    {
        [$purchase, $house, $salesOfficer] =
            $this->readyPurchase('40000000.00');

        try {
            app(PurchaseService::class)->confirm(
                $purchase,
                $salesOfficer,
            );

            $this->fail(
                'A purchase with insufficient payment was confirmed.',
            );
        } catch (ValidationException) {
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
    }

    public function test_repeated_confirmation_does_not_duplicate_allocation(): void
    {
        [$purchase, $house, $salesOfficer] =
            $this->readyPurchase('50000000.00');

        $service = app(PurchaseService::class);

        $service->confirm(
            $purchase,
            $salesOfficer,
        );

        $service->confirm(
            $purchase->fresh(),
            $salesOfficer,
        );

        $this->assertDatabaseCount(
            'property_allocations',
            1,
        );

        $this->assertDatabaseHas(
            'property_allocations',
            [
                'purchase_id' => $purchase->id,
                'house_id' => $house->id,
            ],
        );
    }

    /**
     * @return array{Purchase, House, User}
     */
    private function readyPurchase(
        string $verifiedAmount,
    ): array {
        $customer = User::factory()->create();
        $salesOfficer = User::factory()->create();
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
            'initiated_by' => $salesOfficer->id,
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
            'amount' => $verifiedAmount,
            'payment_method' => PaymentMethod::BankTransfer->value,
            'transaction_reference' => 'CONFIRM-'.str()->upper(str()->random(10)),
            'paid_at' => now()->subMinute(),
            'verified_at' => now(),
        ]);

        return [
            $purchase,
            $house,
            $salesOfficer,
        ];
    }
}
