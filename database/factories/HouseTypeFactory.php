<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\HouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseType>
 */
final class HouseTypeFactory extends Factory
{
    public function definition(): array
    {
        $reference = fake()->unique()->numberBetween(1000, 9999);
        $bedrooms = fake()->numberBetween(2, 6);
        $name = "{$bedrooms} Bedroom House {$reference}";

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'code' => "HT-{$reference}",
            'description' => fake()->paragraph(),
            'bedrooms' => $bedrooms,
            'bathrooms' => $bedrooms,
            'toilets' => $bedrooms + 1,
            'floors' => fake()->numberBetween(1, 2),
            'plot_size_sqm' => fake()->randomFloat(2, 300, 1000),
            'building_size_sqm' => fake()->randomFloat(2, 150, 600),
            'base_price' => fake()->randomFloat(2, 50000000, 250000000),
            'features' => [
                'Fitted kitchen',
                'Parking spaces',
                'Security',
            ],
            'is_active' => true,
        ];
    }
}
