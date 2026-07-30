<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            DepartmentSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }

    public function test_the_initial_system_administrator_can_be_created(): void
    {
        $this->artisan(
            'dems:make-admin',
            [
                'email' => 'administrator@example.com',
                '--name' => 'Ajoda Administrator',
            ],
        )
            ->expectsQuestion(
                'Administrator password (minimum 15 characters)',
                'SecureAdmin123!',
            )
            ->expectsQuestion(
                'Confirm administrator password',
                'SecureAdmin123!',
            )
            ->expectsConfirmation(
                'Create this System Administrator account?',
                'yes',
            )
            ->assertSuccessful();

        $administrator = User::query()
            ->where('email', 'administrator@example.com')
            ->firstOrFail();

        $this->assertSame(
            AccountType::Staff,
            $administrator->account_type,
        );

        $this->assertSame(
            AccountStatus::Active,
            $administrator->account_status,
        );

        $this->assertTrue(
            $administrator->hasVerifiedEmail(),
        );

        $this->assertTrue(
            $administrator->hasRole(
                RoleName::SystemAdministrator->value,
            ),
        );

        $this->assertNotNull(
            $administrator->staffProfile,
        );

        $this->assertSame(
            'System Administrator',
            $administrator->staffProfile->job_title,
        );
    }

    public function test_a_second_bootstrap_administrator_is_rejected(): void
    {
        $existingAdministrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $existingAdministrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        $this->artisan(
            'dems:make-admin',
            [
                'email' => 'another-admin@example.com',
                '--name' => 'Another Administrator',
            ],
        )->assertFailed();

        $this->assertDatabaseMissing('users', [
            'email' => 'another-admin@example.com',
        ]);
    }
}
