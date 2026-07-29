<?php

declare(strict_types=1);

namespace Tests\Feature\Reservations;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PropertyStatus;
use App\Enums\ReservationStatus;
use App\Enums\RoleName;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyReservation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_submit_a_reservation_request(): void
    {
        $customer = $this->customer();
        $house = $this->house();

        $response = $this->actingAs($customer)->post(
            route('customer.reservations.store', $house),
            [
                'customer_notes' =>
                    'I would like to reserve this property.',
            ],
        );

        $response
            ->assertRedirect(
                route('customer.reservations.index'),
            )
            ->assertSessionHas('status');

        $this->assertDatabaseHas(
            'property_reservations',
            [
                'house_id' => $house->id,
                'customer_id' => $customer->id,
                'status' =>
                    ReservationStatus::PendingReview->value,
            ],
        );

        $house->refresh();

        $this->assertSame(
            PropertyStatus::Available,
            $house->status,
        );
    }

    public function test_customer_cannot_submit_duplicate_pending_request(): void
    {
        $customer = $this->customer();
        $house = $this->house();

        PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-DUPLICATE-001',
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'status' =>
                ReservationStatus::PendingReview->value,
        ]);

        $response = $this->actingAs($customer)
            ->from(route('properties.show', $house))
            ->post(
                route(
                    'customer.reservations.store',
                    $house,
                ),
                [
                    'customer_notes' =>
                        'This is another reservation request.',
                ],
            );

        $response
            ->assertRedirect(
                route('properties.show', $house),
            )
            ->assertSessionHasErrors('house_id');

        $this->assertSame(
            1,
            PropertyReservation::query()
                ->where('house_id', $house->id)
                ->where('customer_id', $customer->id)
                ->count(),
        );
    }

    public function test_sales_officer_can_approve_a_reservation(): void
    {
        $salesOfficer = $this->salesOfficer();

        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $house = $this->house();

        $firstReservation = PropertyReservation::query()
            ->create([
                'reference' => 'AJD-RSV-APPROVE-001',
                'house_id' => $house->id,
                'customer_id' => $firstCustomer->id,
                'status' =>
                    ReservationStatus::PendingReview->value,
            ]);

        $secondReservation = PropertyReservation::query()
            ->create([
                'reference' => 'AJD-RSV-APPROVE-002',
                'house_id' => $house->id,
                'customer_id' => $secondCustomer->id,
                'status' =>
                    ReservationStatus::PendingReview->value,
            ]);

        $response = $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'staff.reservations.approve',
                    $firstReservation,
                ),
                [
                    'review_notes' =>
                        'Reservation approved after review.',
                ],
            );

        $response
            ->assertRedirect()
            ->assertSessionHas('status');

        $firstReservation->refresh();
        $secondReservation->refresh();
        $house->refresh();

        $this->assertSame(
            ReservationStatus::Approved,
            $firstReservation->status,
        );

        $this->assertSame(
            $salesOfficer->id,
            $firstReservation->reviewed_by,
        );

        $this->assertNotNull(
            $firstReservation->approved_at,
        );

        $this->assertNotNull(
            $firstReservation->expires_at,
        );

        $this->assertSame(
            ReservationStatus::Rejected,
            $secondReservation->status,
        );

        $this->assertSame(
            PropertyStatus::TemporarilyReserved,
            $house->status,
        );

        $this->assertNotNull(
            $house->reserved_until,
        );

        $this->assertDatabaseHas(
            'house_status_histories',
            [
                'house_id' => $house->id,
                'changed_by' => $salesOfficer->id,
                'from_status' =>
                    PropertyStatus::Available->value,
                'to_status' =>
                    PropertyStatus::TemporarilyReserved->value,
            ],
        );
    }

    public function test_sales_officer_can_reject_a_reservation(): void
    {
        $salesOfficer = $this->salesOfficer();
        $customer = $this->customer();
        $house = $this->house();

        $reservation = PropertyReservation::query()
            ->create([
                'reference' => 'AJD-RSV-REJECT-001',
                'house_id' => $house->id,
                'customer_id' => $customer->id,
                'status' =>
                    ReservationStatus::PendingReview->value,
            ]);

        $response = $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'staff.reservations.reject',
                    $reservation,
                ),
                [
                    'review_notes' =>
                        'The customer information requires clarification.',
                ],
            );

        $response
            ->assertRedirect()
            ->assertSessionHas('status');

        $reservation->refresh();
        $house->refresh();

        $this->assertSame(
            ReservationStatus::Rejected,
            $reservation->status,
        );

        $this->assertSame(
            $salesOfficer->id,
            $reservation->reviewed_by,
        );

        $this->assertNotNull(
            $reservation->rejected_at,
        );

        $this->assertSame(
            PropertyStatus::Available,
            $house->status,
        );
    }

    public function test_customer_can_only_see_their_own_reservations(): void
    {
        $firstCustomer = $this->customer();
        $secondCustomer = $this->customer();

        $firstHouse = $this->house();
        $secondHouse = $this->house();

        PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-OWNER-001',
            'house_id' => $firstHouse->id,
            'customer_id' => $firstCustomer->id,
            'status' =>
                ReservationStatus::PendingReview->value,
        ]);

        PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-OWNER-002',
            'house_id' => $secondHouse->id,
            'customer_id' => $secondCustomer->id,
            'status' =>
                ReservationStatus::PendingReview->value,
        ]);

        $this->actingAs($firstCustomer)
            ->get(route('customer.reservations.index'))
            ->assertOk()
            ->assertSeeText('AJD-RSV-OWNER-001')
            ->assertDontSeeText('AJD-RSV-OWNER-002');
    }

    public function test_accounts_officer_cannot_manage_reservations(): void
    {
        $accountsOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $accountsOfficer->assignRole(
            RoleName::AccountsOfficer->value,
        );

        $this->actingAs($accountsOfficer)
            ->get(route('staff.reservations.index'))
            ->assertForbidden();
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

    private function salesOfficer(): User
    {
        $salesOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $salesOfficer->assignRole(
            RoleName::SalesOfficer->value,
        );

        return $salesOfficer;
    }

    private function house(
        array $attributes = [],
    ): House {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()->create([
            'estate_id' => $estate->id,
        ]);

        $houseType = HouseType::factory()->create();

        return House::factory()->create(
            array_merge(
                [
                    'estate_id' => $estate->id,
                    'estate_phase_id' => $phase->id,
                    'house_type_id' => $houseType->id,
                    'status' =>
                        PropertyStatus::Available->value,
                    'is_published' => true,
                ],
                $attributes,
            ),
        );
    }
}