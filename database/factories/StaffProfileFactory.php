<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StaffProfile>
 */
final class StaffProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state([
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Active->value,
            ]),
            'department_id' => Department::factory(),
            'staff_number' => fake()->unique()->bothify('AJD-####'),
            'phone' => fake()->optional()->numerify('080########'),
            'job_title' => fake()->jobTitle(),
            'employment_date' => fake()
                ->dateTimeBetween('-10 years', 'now')
                ->format('Y-m-d'),
        ];
    }
}
