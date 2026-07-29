<?php

declare(strict_types=1);

namespace Tests\Feature\Purchases;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyReservation;
use App\Models\User;
use App\Services\Purchases\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PurchaseInitiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_reservation_can_start_purchase(): void
    {
        $salesOfficer = $this->staff();
        $customer = User::factory()->create();
        $house = $this->house();

        $reservation = $this->approvedReservation(
            $house,
            $customer,
        );

        $purchase = app(PurchaseService::class)
            ->initiate(
                $reservation,
                $salesOfficer,
                '50000000.00',
                PaymentPlanType::Instalment,
                'Customer selected instalment payment.',
            );

        $reservation->refresh();
        $house->refresh();

        $this->assertSame(
            PurchaseStatus::AwaitingPayment,
            $purchase->status,
        );

        $this->assertSame(
            ReservationStatus::Converted,
            $reservation->status,
        );

        $this->assertSame(
            PropertyStatus::SaleProcessing,
            $house->status,
        );

        $this->assertNull($house->reserved_until);

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'active_house_id' => $house->id,
            'customer_id' => $customer->id,
            'approved_price' => '50000000.00',
        ]);

        $this->assertDatabaseHas(
            'house_status_histories',
            [
                'house_id' => $house->id,
                'from_status' =>
                    PropertyStatus::TemporarilyReserved->value,
                'to_status' =>
                    PropertyStatus::SaleProcessing->value,
            ],
        );
    }

    public function test_purchase_initiation_is_idempotent(): void
    {
        $salesOfficer = $this->staff();
        $customer = User::factory()->create();
        $house = $this->house();

        $reservation = $this->approvedReservation(
            $house,
            $customer,
        );

        $service = app(PurchaseService::class);

        $first = $service->initiate(
            $reservation,
            $salesOfficer,
            '50000000.00',
            PaymentPlanType::Outright,
        );

        $second = $service->initiate(
            $reservation,
            $salesOfficer,
            '50000000.00',
            PaymentPlanType::Outright,
        );

        $this->assertSame(
            $first->id,
            $second->id,
        );

        $this->assertDatabaseCount(
            'purchases',
            1,
        );
    }

    public function test_pending_reservation_cannot_start_purchase(): void
    {
        $salesOfficer = $this->staff();
        $customer = User::factory()->create();
        $house = $this->house();

        $reservation = PropertyReservation::query()
            ->create([
                'reference' => 'AJD-RSV-PENDING-001',
                'house_id' => $house->id,
                'customer_id' => $customer->id,
                'status' =>
                    ReservationStatus::PendingReview->value,
            ]);

        $this->expectException(
            ValidationException::class,
        );

        app(PurchaseService::class)->initiate(
            $reservation,
            $salesOfficer,
            '50000000.00',
            PaymentPlanType::Outright,
        );
    }

    private function approvedReservation(
        House $house,
        User $customer,
    ): PropertyReservation {
        $house->forceFill([
            'status' =>
                PropertyStatus::TemporarilyReserved->value,
            'reserved_until' => now()->addHours(72),
        ])->save();

        return PropertyReservation::query()->create([
            'reference' =>
                'AJD-RSV-'.str()->upper(str()->random(10)),
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'status' => ReservationStatus::Approved->value,
            'approved_at' => now(),
            'expires_at' => now()->addHours(72),
        ]);
    }

    private function staff(): User
    {
        return User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
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