<?php

declare(strict_types=1);

namespace Tests\Feature\Enquiries;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\EnquiryStatus;
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

final class PropertyEnquiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_can_submit_an_enquiry_for_a_published_property(): void
    {
        $house = $this->house();

        $this->post(
            route('properties.enquiries.store', $house),
            [
                'full_name' => 'Prospective Buyer',
                'email' => 'buyer@example.com',
                'phone' => '08000000000',
                'message' => 'I would like more information about this property.',
            ],
        )
            ->assertRedirect(
                route('properties.show', $house),
            )
            ->assertSessionHas('enquiry_status');

        $this->assertDatabaseHas('property_enquiries', [
            'house_id' => $house->id,
            'email' => 'buyer@example.com',
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);
    }

    public function test_authenticated_customer_is_attached_to_the_enquiry(): void
    {
        $house = $this->house();

        /** @var User $customer */
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        $this->actingAs($customer)
            ->post(
                route('properties.enquiries.store', $house),
                [
                    'full_name' => $customer->name,
                    'email' => $customer->email,
                    'message' => 'Please contact me about the available payment options.',
                ],
            )
            ->assertRedirect();

        $this->assertDatabaseHas('property_enquiries', [
            'house_id' => $house->id,
            'user_id' => $customer->id,
            'email' => $customer->email,
        ]);
    }

    public function test_enquiry_cannot_be_submitted_for_an_unpublished_property(): void
    {
        $house = $this->house([
            'is_published' => false,
        ]);

        $this->post(
            route('properties.enquiries.store', $house),
            [
                'full_name' => 'Prospective Buyer',
                'email' => 'buyer@example.com',
                'message' => 'I would like information about this property.',
            ],
        )->assertNotFound();
    }

    public function test_sales_officer_can_view_and_update_enquiries(): void
    {
        $house = $this->house();

        $enquiry = PropertyEnquiry::query()->create([
            'house_id' => $house->id,
            'full_name' => 'Prospective Buyer',
            'email' => 'buyer@example.com',
            'message' => 'Please contact me about this property.',
            'status' => EnquiryStatus::NewEnquiry->value,
        ]);

        /** @var User $salesOfficer */
        $salesOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $salesOfficer->assignRole(
            RoleName::SalesOfficer->value,
        );

        $this->actingAs($salesOfficer)
            ->get(route('staff.enquiries.index'))
            ->assertOk()
            ->assertSeeText('Property Enquiries')
            ->assertSeeText($house->property_code);

        $this->actingAs($salesOfficer)
            ->patch(
                route('staff.enquiries.update', $enquiry),
                [
                    'status' => EnquiryStatus::Contacted->value,
                ],
            )
            ->assertRedirect();

        $enquiry->refresh();

        $this->assertSame(
            EnquiryStatus::Contacted,
            $enquiry->status,
        );

        $this->assertSame(
            $salesOfficer->id,
            $enquiry->assigned_to,
        );

        $this->assertNotNull(
            $enquiry->responded_at,
        );
    }

    public function test_accounts_officer_cannot_manage_enquiries(): void
    {
        /** @var User $accountsOfficer */
        $accountsOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $accountsOfficer->assignRole(
            RoleName::AccountsOfficer->value,
        );

        $this->actingAs($accountsOfficer)
            ->get(route('staff.enquiries.index'))
            ->assertForbidden();
    }

    private function house(array $attributes = []): House
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()
            ->for($estate)
            ->create();

        $houseType = HouseType::factory()->create();

        return House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create(array_merge([
                'is_published' => true,
            ], $attributes));
    }
}
