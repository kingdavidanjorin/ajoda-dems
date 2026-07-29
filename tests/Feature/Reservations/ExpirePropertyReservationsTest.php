<?php

declare(strict_types=1);

namespace Tests\Feature\Reservations;

use App\Enums\PropertyStatus;
use App\Enums\ReservationStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use App\Models\PropertyReservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ExpirePropertyReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_hold_is_released_by_the_command(): void
    {
        $customer = User::factory()->create();
        $reviewer = User::factory()->create();

        $house = $this->house([
            'status' =>
                PropertyStatus::TemporarilyReserved->value,
            'reserved_until' => now()->subMinute(),
        ]);

        $reservation = PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-EXPIRED-001',
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'reviewed_by' => $reviewer->id,
            'status' => ReservationStatus::Approved->value,
            'approved_at' => now()->subHours(72),
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 1 reservation(s).')
            ->assertSuccessful();

        $reservation->refresh();
        $house->refresh();

        $this->assertSame(
            ReservationStatus::Expired,
            $reservation->status,
        );

        $this->assertSame(
            PropertyStatus::Available,
            $house->status,
        );

        $this->assertNull(
            $house->reserved_until,
        );

        $this->assertDatabaseHas(
            'house_status_histories',
            [
                'house_id' => $house->id,
                'from_status' =>
                    PropertyStatus::TemporarilyReserved->value,
                'to_status' =>
                    PropertyStatus::Available->value,
            ],
        );
    }

    public function test_future_hold_is_not_expired(): void
    {
        $customer = User::factory()->create();
        $reviewer = User::factory()->create();

        $house = $this->house([
            'status' =>
                PropertyStatus::TemporarilyReserved->value,
            'reserved_until' => now()->addHour(),
        ]);

        $reservation = PropertyReservation::query()->create([
            'reference' => 'AJD-RSV-FUTURE-001',
            'house_id' => $house->id,
            'customer_id' => $customer->id,
            'reviewed_by' => $reviewer->id,
            'status' => ReservationStatus::Approved->value,
            'approved_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $this->artisan('reservations:expire')
            ->expectsOutput('Expired 0 reservation(s).')
            ->assertSuccessful();

        $reservation->refresh();
        $house->refresh();

        $this->assertSame(
            ReservationStatus::Approved,
            $reservation->status,
        );

        $this->assertSame(
            PropertyStatus::TemporarilyReserved,
            $house->status,
        );

        $this->assertNotNull(
            $house->reserved_until,
        );
    }

    private function house(array $attributes = []): House
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()
            ->for($estate)
            ->create();

        $houseType = HouseType::factory()->create();

        return House::factory()
            ->for($estate)
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create(
                array_merge(
                    [
                        'status' =>
                            PropertyStatus::Available->value,
                        'is_published' => true,
                    ],
                    $attributes,
                ),
            );
    }
}