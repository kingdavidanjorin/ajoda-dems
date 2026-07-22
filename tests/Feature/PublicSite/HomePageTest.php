<?php

declare(strict_types=1);

namespace Tests\Feature\PublicSite;

use App\Models\House;
use Database\Seeders\EstatePropertySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EstatePropertySeeder::class);
    }

    public function test_the_public_homepage_can_be_viewed(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Ajoda GRA')
            ->assertSee('Find a home built around your future.')
            ->assertSee('Featured properties')
            ->assertSee('Terrace Duplex A1');
    }

    public function test_unpublished_houses_are_not_displayed(): void
    {
        House::factory()->create([
            'name' => 'Hidden Demonstration Property',
            'is_featured' => true,
            'is_published' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Hidden Demonstration Property');
    }

    public function test_the_homepage_displays_property_statistics(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Available homes')
            ->assertSee('Estate phases')
            ->assertSee('House types');
    }
}
