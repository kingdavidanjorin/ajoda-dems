<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Estate;
use App\Models\EstatePhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstatePhase>
 */
final class EstatePhaseFactory extends Factory
{
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999);

        return [
            'estate_id' => Estate::factory(),
            'name' => "Phase {$number}",
            'slug' => "phase-{$number}",
            'code' => "PH-{$number}",
            'description' => fake()->optional()->sentence(),
            'sort_order' => $number,
            'is_active' => true,
        ];
    }
}
