<?php

declare(strict_types=1);

namespace Tests\Feature\Construction;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\AllocationStatus;
use App\Enums\ConstructionStage;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyAllocation;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Construction\ConstructionProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class ConstructionProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_records_update_with_matching_purchase_and_allocation(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();
        $house = $this->house();

        $purchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $allocation = $this->allocation(
            purchase: $purchase,
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $update = app(
            ConstructionProgressService::class,
        )->record(
            [
                'house_id' => $house->id,
                'purchase_id' => $purchase->id,
                'property_allocation_id' => $allocation->id,
                'stage' => ConstructionStage::Roofing->value,
                'progress_percentage' => 65,
                'title' => 'Roofing work commenced',
                'summary' =>
                'Roof installation has commenced successfully.',
                'internal_notes' =>
                'Materials were inspected before installation.',
                'expected_completion_date' =>
                now()->addMonths(4)->toDateString(),
                'occurred_at' => now()->subMinute(),
            ],
            $staff,
        );

        $this->assertSame($house->id, $update->house_id);
        $this->assertSame($purchase->id, $update->purchase_id);

        $this->assertSame(
            $allocation->id,
            $update->property_allocation_id,
        );

        $this->assertSame(
            $staff->id,
            $update->recorded_by,
        );

        $this->assertSame(
            ConstructionStage::Roofing,
            $update->stage,
        );

        $this->assertSame(65, $update->progress_percentage);
        $this->assertFalse($update->customer_visible);

        $this->assertStringStartsWith(
            'AJD-CNS-' . now()->format('Ymd') . '-',
            $update->reference,
        );

        $this->assertDatabaseHas(
            'construction_progress_updates',
            [
                'id' => $update->id,
                'house_id' => $house->id,
                'purchase_id' => $purchase->id,
                'property_allocation_id' => $allocation->id,
                'recorded_by' => $staff->id,
                'stage' => ConstructionStage::Roofing->value,
                'progress_percentage' => 65,
                'customer_visible' => false,
            ],
        );
    }

    public function test_service_rejects_purchase_from_another_property(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();

        $purchaseHouse = $this->house();
        $selectedHouse = $this->house();

        $purchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $purchaseHouse,
        );

        $this->assertValidationError(
            callback: fn() => app(
                ConstructionProgressService::class,
            )->record(
                $this->recordData(
                    house: $selectedHouse,
                    purchase: $purchase,
                ),
                $staff,
            ),
            field: 'purchase_id',
            message: 'The selected purchase does not belong to the selected property.',
        );

        $this->assertDatabaseCount(
            'construction_progress_updates',
            0,
        );
    }

    public function test_service_rejects_allocation_from_another_property(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();

        $allocationHouse = $this->house();
        $selectedHouse = $this->house();

        $purchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $allocationHouse,
        );

        $allocation = $this->allocation(
            purchase: $purchase,
            customer: $customer,
            staff: $staff,
            house: $allocationHouse,
        );

        $this->assertValidationError(
            callback: fn() => app(
                ConstructionProgressService::class,
            )->record(
                $this->recordData(
                    house: $selectedHouse,
                    allocation: $allocation,
                ),
                $staff,
            ),
            field: 'property_allocation_id',
            message: 'The selected allocation does not belong to the selected property.',
        );

        $this->assertDatabaseCount(
            'construction_progress_updates',
            0,
        );
    }

    public function test_service_rejects_allocation_from_another_purchase(): void
    {
        $staff = $this->staff();
        $customer = $this->customer();
        $house = $this->house();

        $firstPurchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $secondPurchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $allocation = $this->allocation(
            purchase: $firstPurchase,
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $this->assertValidationError(
            callback: fn() => app(
                ConstructionProgressService::class,
            )->record(
                $this->recordData(
                    house: $house,
                    purchase: $secondPurchase,
                    allocation: $allocation,
                ),
                $staff,
            ),
            field: 'property_allocation_id',
            message: 'The selected allocation does not belong to the selected purchase.',
        );

        $this->assertDatabaseCount(
            'construction_progress_updates',
            0,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function recordData(
        House $house,
        ?Purchase $purchase = null,
        ?PropertyAllocation $allocation = null,
    ): array {
        return [
            'house_id' => $house->id,
            'purchase_id' => $purchase?->id,
            'property_allocation_id' => $allocation?->id,
            'stage' => ConstructionStage::Foundation->value,
            'progress_percentage' => 30,
            'title' => 'Construction relationship test',
            'summary' =>
            'Testing related construction records.',
            'internal_notes' => null,
            'customer_visible' => false,
            'expected_completion_date' => null,
            'occurred_at' => now()->subMinute(),
        ];
    }

    private function assertValidationError(
        callable $callback,
        string $field,
        string $message,
    ): void {
        try {
            $callback();

            $this->fail(
                'A validation exception was expected.',
            );
        } catch (ValidationException $exception) {
            $this->assertSame(
                [$message],
                $exception->errors()[$field] ?? [],
            );
        }
    }

    private function purchase(
        User $customer,
        User $staff,
        House $house,
    ): Purchase {
        $reservation = PropertyReservation::query()->create([
            'reference' =>
            'AJD-RES-' . Str::upper(Str::random(10)),
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'property_enquiry_id' => null,
            'reviewed_by' => $staff->id,
            'status' => ReservationStatus::Approved->value,
            'customer_notes' => null,
            'review_notes' => 'Approved for construction test.',
            'approved_at' => now(),
            'rejected_at' => null,
            'cancelled_at' => null,
            'expires_at' => now()->addDays(14),
        ]);

        return Purchase::query()->create([
            'reference' =>
            'AJD-PUR-' . Str::upper(Str::random(10)),
            'reservation_id' => $reservation->id,
            'house_id' => $house->id,
            'active_house_id' => null,
            'customer_id' => $customer->id,
            'initiated_by' => $staff->id,
            'status' => PurchaseStatus::Confirmed->value,
            'approved_price' => 25000000,
            'payment_plan_type' =>
            PaymentPlanType::Outright->value,
            'effective_date' => now()->toDateString(),
            'sales_notes' => 'Construction service test.',
            'initiated_at' => now(),
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ]);
    }

    private function allocation(
        Purchase $purchase,
        User $customer,
        User $staff,
        House $house,
    ): PropertyAllocation {
        return PropertyAllocation::query()->create([
            'reference' =>
            'AJD-ALC-' . Str::upper(Str::random(10)),
            'purchase_id' => $purchase->id,
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'allocated_by' => $staff->id,
            'status' => AllocationStatus::Active->value,
            'allocated_at' => now(),
            'notes' => 'Construction service test allocation.',
        ]);
    }

    private function customer(): User
    {
        return User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
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
            'status' =>
            PropertyStatus::UnderConstruction->value,
            'is_published' => true,
        ]);
    }
}
