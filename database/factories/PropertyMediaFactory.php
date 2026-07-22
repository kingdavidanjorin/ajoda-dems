<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PropertyMediaType;
use App\Models\House;
use App\Models\PropertyMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PropertyMedia>
 */
final class PropertyMediaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            'type' => PropertyMediaType::Image->value,
            'disk' => 'public',
            'path' => 'properties/'.fake()->uuid().'.jpg',
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->optional()->sentence(),
            'sort_order' => 0,
            'is_primary' => true,
            'is_public' => true,
        ];
    }
}
