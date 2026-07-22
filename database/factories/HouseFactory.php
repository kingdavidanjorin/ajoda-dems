<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Models\Estate;
use App\Models\House;
use App\Models\HouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<House>
 */
final class HouseFactory extends Factory
{
    public function definition(): array
    {
        $reference = fake()->unique()->numberBetween(10000, 99999);

        return [
            'estate_id' => Estate::factory(),
            'estate_phase_id' => null,
            'house_type_id' => HouseType::factory(),
            'property_code' => "AJD-{$reference}",
            'name' => "Ajoda Property {$reference}",
            'slug' => "ajoda-property-{$reference}",
            'plot_number' => "PLOT-{$reference}",
            'house_number' => null,
            'description' => fake()->paragraph(),
            'price_override' => null,
            'status' => PropertyStatus::Available->value,
            'is_featured' => false,
            'is_published' => true,
            'layout_x' => null,
            'layout_y' => null,
            'reserved_until' => null,
        ];
    }
}
