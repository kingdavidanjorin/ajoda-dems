<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerProfile>
 */
final class CustomerProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state([
                'account_type' => AccountType::Customer->value,
                'account_status' => AccountStatus::Active->value,
            ]),
            'phone' => fake()->optional()->numerify('080########'),
            'date_of_birth' => fake()
                ->optional()
                ->dateTimeBetween('-70 years', '-18 years')
                ?->format('Y-m-d'),
            'occupation' => fake()->optional()->jobTitle(),
            'address_line_one' => fake()->optional()->streetAddress(),
            'address_line_two' => null,
            'city' => fake()->optional()->city(),
            'state' => fake()->optional()->state(),
            'country' => 'Nigeria',
        ];
    }
}
