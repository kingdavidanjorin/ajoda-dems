<?php

declare(strict_types=1);

namespace Tests\Feature\PublicSite;

use App\Enums\PropertyStatus;
use App\Models\House;
use Database\Seeders\EstatePropertySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PropertyCatalogueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EstatePropertySeeder::class);
    }

    public function test_the_public_catalogue_can_be_viewed(): void
    {
        $this->get(route('properties.index'))
            ->assertOk()
            ->assertSee('Ajoda GRA property catalogue')
            ->assertSee('Terrace Duplex A1')
            ->assertSee('Detached Duplex E2');
    }

    public function test_unpublished_properties_are_excluded(): void
    {
        House::factory()->create([
            'name' => 'Hidden Catalogue Property',
            'slug' => 'hidden-catalogue-property',
            'is_published' => false,
        ]);

        $this->get(route('properties.index'))
            ->assertOk()
            ->assertDontSee('Hidden Catalogue Property');
    }

    public function test_properties_can_be_filtered_by_status(): void
    {
        $response = $this->get(
            route('properties.index', [
                'status' => PropertyStatus::Available->value,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee('Terrace Duplex A1')
            ->assertDontSee('Terrace Duplex A3')
            ->assertDontSee('Detached Duplex C1');
    }

    public function test_properties_can_be_filtered_by_bedrooms(): void
    {
        $response = $this->get(
            route('properties.index', [
                'bedrooms' => 5,
            ]),
        );

        $response
            ->assertOk()
            ->assertSee('Detached Duplex E1')
            ->assertSee('Detached Duplex E2')
            ->assertDontSee('Terrace Duplex A1');
    }

    public function test_a_published_property_detail_page_can_be_viewed(): void
    {
        $house = House::query()
            ->where('property_code', 'AJD-P2-004')
            ->firstOrFail();

        $this->get(route('properties.show', $house))
            ->assertOk()
            ->assertSee('Detached Duplex E2')
            ->assertSee('5 Bedroom Detached Duplex')
            ->assertSee('145,000,000')
            ->assertSee('Property specifications');
    }

    public function test_an_unpublished_property_detail_page_returns_not_found(): void
    {
        $house = House::factory()->create([
            'slug' => 'private-unpublished-property',
            'is_published' => false,
        ]);

        $this->get(route('properties.show', $house))
            ->assertNotFound();
    }
}
