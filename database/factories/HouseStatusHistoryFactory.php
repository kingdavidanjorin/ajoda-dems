<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PropertyStatus;
use App\Models\House;
use App\Models\HouseStatusHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HouseStatusHistory>
 */
final class HouseStatusHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'house_id' => House::factory(),
            'changed_by' => null,
            'from_status' => null,
            'to_status' => PropertyStatus::Available->value,
            'reason' => 'Initial property inventory entry.',
            'metadata' => null,
            'changed_at' => now(),
        ];
    }
}
