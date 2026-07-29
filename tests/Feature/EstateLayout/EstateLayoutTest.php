<?php

declare(strict_types=1);

namespace Tests\Feature\EstateLayout;

use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\EstatePhase;
use App\Models\House;
use App\Models\HouseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EstateLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_view_published_mapped_properties(): void
    {
        [$phase, $houseType] = $this->inventoryParents();

        $publishedHouse = House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create([
                'property_code' => 'AJD-MAP-001',
                'status' => PropertyStatus::Available->value,
                'is_published' => true,
                'layout_x' => 25,
                'layout_y' => 40,
            ]);

        House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create([
                'property_code' => 'AJD-HIDDEN-001',
                'is_published' => false,
                'layout_x' => 60,
                'layout_y' => 60,
            ]);

        House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create([
                'property_code' => 'AJD-NO-MAP-001',
                'is_published' => true,
                'layout_x' => null,
                'layout_y' => null,
            ]);

        $this->get(route('estate.layout'))
            ->assertOk()
            ->assertSeeText('Interactive Estate Layout')
            ->assertSeeText($publishedHouse->property_code)
            ->assertDontSeeText('AJD-HIDDEN-001')
            ->assertDontSeeText('AJD-NO-MAP-001');
    }

    public function test_layout_can_be_filtered_by_property_status(): void
    {
        [$phase, $houseType] = $this->inventoryParents();

        House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create([
                'property_code' => 'AJD-AVAILABLE-001',
                'status' => PropertyStatus::Available->value,
                'is_published' => true,
                'layout_x' => 20,
                'layout_y' => 20,
            ]);

        House::factory()
            ->for($phase, 'estatePhase')
            ->for($houseType, 'houseType')
            ->create([
                'property_code' => 'AJD-SOLD-001',
                'status' => PropertyStatus::Sold->value,
                'is_published' => true,
                'layout_x' => 70,
                'layout_y' => 70,
            ]);

        $this->get(route('estate.layout', [
            'status' => PropertyStatus::Available->value,
        ]))
            ->assertOk()
            ->assertSeeText('AJD-AVAILABLE-001')
            ->assertDontSeeText('AJD-SOLD-001');
    }

    public function test_layout_can_be_filtered_by_phase_and_house_type(): void
    {
        $estate = Estate::factory()->create();

        $phaseOne = EstatePhase::factory()
            ->for($estate)
            ->create([
                'name' => 'Phase One',
            ]);

        $phaseTwo = EstatePhase::factory()
            ->for($estate)
            ->create([
                'name' => 'Phase Two',
            ]);

        $duplex = HouseType::factory()->create([
            'name' => 'Duplex',
        ]);

        $bungalow = HouseType::factory()->create([
            'name' => 'Bungalow',
        ]);

        House::factory()
            ->for($phaseOne, 'estatePhase')
            ->for($duplex, 'houseType')
            ->create([
                'property_code' => 'AJD-PHASE-ONE',
                'is_published' => true,
                'layout_x' => 30,
                'layout_y' => 30,
            ]);

        House::factory()
            ->for($phaseTwo, 'estatePhase')
            ->for($bungalow, 'houseType')
            ->create([
                'property_code' => 'AJD-PHASE-TWO',
                'is_published' => true,
                'layout_x' => 60,
                'layout_y' => 60,
            ]);

        $this->get(route('estate.layout', [
            'phase' => $phaseTwo->id,
            'type' => $bungalow->id,
        ]))
            ->assertOk()
            ->assertSeeText('AJD-PHASE-TWO')
            ->assertDontSeeText('AJD-PHASE-ONE');
    }

    private function inventoryParents(): array
    {
        $estate = Estate::factory()->create();

        $phase = EstatePhase::factory()
            ->for($estate)
            ->create();

        $houseType = HouseType::factory()->create();

        return [
            $phase,
            $houseType,
        ];
    }
}
