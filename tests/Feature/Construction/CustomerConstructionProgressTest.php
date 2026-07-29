<?php

declare(strict_types=1);

namespace Tests\Feature\Construction;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\ConstructionStage;
use App\Enums\PaymentPlanType;
use App\Enums\PropertyStatus;
use App\Enums\PurchaseStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoleName;
use App\Models\ConstructionProgressUpdate;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerConstructionProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_own_visible_construction_update(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $purchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $update = $this->constructionUpdate(
            purchase: $purchase,
            staff: $staff,
            customerVisible: true,
            title: 'Foundation work completed',
            summary: 'The foundation stage has been completed successfully.',
        );

        $this->actingAs($customer)
            ->get(route('customer.construction.index'))
            ->assertOk()
            ->assertSeeText($update->reference)
            ->assertSeeText('Foundation work completed')
            ->assertSeeText(
                'The foundation stage has been completed successfully.',
            )
            ->assertSeeText($house->property_code)
            ->assertDontSeeText(
                'Confidential internal construction note.',
            );
    }

    public function test_customer_cannot_view_hidden_construction_update(): void
    {
        $customer = $this->customer();
        $staff = $this->staff();
        $house = $this->house();

        $purchase = $this->purchase(
            customer: $customer,
            staff: $staff,
            house: $house,
        );

        $update = $this->constructionUpdate(
            purchase: $purchase,
            staff: $staff,
            customerVisible: false,
            title: 'Internal site review',
            summary: 'This update is for internal staff only.',
        );

        $this->actingAs($customer)
            ->get(route('customer.construction.index'))
            ->assertOk()
            ->assertDontSeeText($update->reference)
            ->assertDontSeeText('Internal site review')
            ->assertDontSeeText(
                'This update is for internal staff only.',
            );
    }

    public function test_customer_cannot_view_another_customers_update(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();
        $staff = $this->staff();

        $firstPurchase = $this->purchase(
            customer: $firstCustomer,
            staff: $staff,
            house: $this->house(),
        );

        $secondPurchase = $this->purchase(
            customer: $secondCustomer,
            staff: $staff,
            house: $this->house(),
        );

        $firstUpdate = $this->constructionUpdate(
            purchase: $firstPurchase,
            staff: $staff,
            customerVisible: true,
            title: 'First customer update',
            summary: 'Visible to the first customer.',
        );

        $secondUpdate = $this->constructionUpdate(
            purchase: $secondPurchase,
            staff: $staff,
            customerVisible: true,
            title: 'Second customer update',
            summary: 'Visible only to the second customer.',
        );

        $this->actingAs($firstCustomer)
            ->get(route('customer.construction.index'))
            ->assertOk()
            ->assertSeeText($firstUpdate->reference)
            ->assertSeeText('First customer update')
            ->assertDontSeeText($secondUpdate->reference)
            ->assertDontSeeText('Second customer update');
    }

    private function constructionUpdate(
        Purchase $purchase,
        User $staff,
        bool $customerVisible,
        string $title,
        string $summary,
    ): ConstructionProgressUpdate {
        return ConstructionProgressUpdate::query()->create([
            'reference' =>
            'AJD-CNS-' . Str::upper(Str::random(10)),
            'house_id' => $purchase->house_id,
            'purchase_id' => $purchase->id,
            'property_allocation_id' => null,
            'recorded_by' => $staff->id,
            'stage' => ConstructionStage::Foundation->value,
            'progress_percentage' => 30,
            'title' => $title,
            'summary' => $summary,
            'internal_notes' =>
            'Confidential internal construction note.',
            'customer_visible' => $customerVisible,
            'expected_completion_date' =>
            now()->addMonths(6)->toDateString(),
            'occurred_at' => now(),
        ]);
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
            'sales_notes' => 'Construction test purchase.',
            'initiated_at' => now(),
            'confirmed_at' => now(),
            'cancelled_at' => null,
        ]);
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
            'status' => PropertyStatus::UnderConstruction->value,
            'is_published' => true,
        ]);
    }
}
