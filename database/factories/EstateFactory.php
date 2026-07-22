<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Estate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Estate>
 */
final class EstateFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company().' Estate';

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'code' => fake()->unique()->bothify('EST-###'),
            'description' => fake()->optional()->paragraph(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => 'Nigeria',
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'is_active' => true,
        ];
    }
}
