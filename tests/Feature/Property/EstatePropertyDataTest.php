<?php

declare(strict_types=1);

namespace Tests\Feature\Property;

use App\Enums\PropertyMediaType;
use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseStatusHistory;
use App\Models\HouseType;
use App\Models\PropertyMedia;
use Database\Seeders\EstatePropertySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EstatePropertyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_authoritative_property_statuses_are_defined(): void
    {
        $this->assertSame(
            [
                'available',
                'temporarily_reserved',
                'sale_processing',
                'sold',
                'under_construction',
                'completed',
                'handed_over',
            ],
            array_column(PropertyStatus::cases(), 'value'),
        );
    }

    public function test_an_estate_contains_phases_and_houses(): void
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()
            ->for($estate)
            ->create();

        $house = House::factory()
            ->for($estate)
            ->for($phase, 'phase')
            ->create();

        $this->assertTrue($phase->estate->is($estate));
        $this->assertTrue($house->estate->is($estate));
        $this->assertTrue($house->phase->is($phase));
        $this->assertTrue($estate->houses->contains($house));
    }

    public function test_a_house_uses_its_type_price_when_no_override_exists(): void
    {
        $houseType = HouseType::factory()->create([
            'base_price' => 95000000,
        ]);

        $house = House::factory()
            ->for($houseType)
            ->create([
                'price_override' => null,
            ]);

        $this->assertSame(
            '95000000.00',
            $house->effectivePrice(),
        );
    }

    public function test_a_house_uses_its_override_price_when_present(): void
    {
        $house = House::factory()->create([
            'price_override' => 100000000,
        ]);

        $this->assertSame(
            '100000000.00',
            $house->effectivePrice(),
        );
    }

    public function test_published_and_available_scopes_filter_inventory(): void
    {
        House::factory()->create([
            'status' => PropertyStatus::Available->value,
            'is_published' => true,
        ]);

        House::factory()->create([
            'status' => PropertyStatus::Sold->value,
            'is_published' => true,
        ]);

        House::factory()->create([
            'status' => PropertyStatus::Available->value,
            'is_published' => false,
        ]);

        $this->assertSame(2, House::query()->published()->count());
        $this->assertSame(2, House::query()->available()->count());

        $this->assertSame(
            1,
            House::query()
                ->published()
                ->available()
                ->count(),
        );
    }

    public function test_property_media_and_status_history_belong_to_a_house(): void
    {
        $house = House::factory()->create();

        $media = PropertyMedia::factory()
            ->for($house)
            ->create([
                'type' => PropertyMediaType::Image->value,
            ]);

        $history = HouseStatusHistory::factory()
            ->for($house)
            ->create([
                'to_status' => PropertyStatus::Available->value,
            ]);

        $this->assertTrue($media->house->is($house));
        $this->assertTrue($history->house->is($house));
        $this->assertTrue($house->media->contains($media));
        $this->assertTrue($house->statusHistory->contains($history));
    }

    public function test_the_demo_inventory_seeder_is_idempotent(): void
    {
        $this->seed(EstatePropertySeeder::class);
        $this->seed(EstatePropertySeeder::class);

        $estate = Estate::query()
            ->where('code', 'AJODA-GRA')
            ->firstOrFail();

        $this->assertSame(1, Estate::query()->count());
        $this->assertSame(2, $estate->phases()->count());
        $this->assertSame(3, HouseType::query()->count());
        $this->assertSame(10, $estate->houses()->count());
        $this->assertSame(10, HouseStatusHistory::query()->count());
    }
}
