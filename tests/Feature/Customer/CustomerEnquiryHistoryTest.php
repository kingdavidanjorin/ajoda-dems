<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\EnquiryStatus;
use App\Enums\PropertyStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyEnquiry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerEnquiryHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_their_enquiries(): void
    {
        $customer = $this->customer();
        $house = $this->house();

        PropertyEnquiry::query()->create([
            'house_id' => $house->id,
            'user_id' => $customer->id,
            'full_name' => $customer->name,
            'email' => $customer->email,
            'phone' => '08012345678',
            'message' => 'I would like more information about this property.',
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.enquiries.index'))
            ->assertOk()
            ->assertSeeText(
                'I would like more information about this property.',
            );
    }

    public function test_customer_cannot_view_another_customers_enquiry(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();
        $house = $this->house();

        PropertyEnquiry::query()->create([
            'house_id' => $house->id,
            'user_id' => $firstCustomer->id,
            'full_name' => $firstCustomer->name,
            'email' => $firstCustomer->email,
            'message' => 'This belongs to the first customer.',
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);

        PropertyEnquiry::query()->create([
            'house_id' => $house->id,
            'user_id' => $secondCustomer->id,
            'full_name' => $secondCustomer->name,
            'email' => $secondCustomer->email,
            'message' => 'This belongs to the second customer.',
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);

        $this->actingAs($firstCustomer)
            ->get(route('customer.enquiries.index'))
            ->assertOk()
            ->assertSeeText(
                'This belongs to the first customer.',
            )
            ->assertDontSeeText(
                'This belongs to the second customer.',
            );
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
