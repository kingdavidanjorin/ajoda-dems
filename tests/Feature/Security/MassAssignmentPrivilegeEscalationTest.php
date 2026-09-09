<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class MassAssignmentPrivilegeEscalationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_public_registration_cannot_escalate_to_staff_or_admin(): void
    {
        $email = 'mass-assignment-attack@example.com';

        $response = $this->post(
            route('register.store'),
            [
                'name' => 'Privilege Escalation Test',
                'email' => $email,
                'password' => 'SecurePassword123!',
                'password_confirmation' => 'SecurePassword123!',

                // Malicious extra fields.
                'account_type' => AccountType::Staff->value,
                'account_status' => AccountStatus::Suspended->value,

                'role' => RoleName::SystemAdministrator->value,

                'roles' => [
                    RoleName::SystemAdministrator->value,
                ],

                'email_verified_at' => now()->toDateTimeString(),
            ],
        );

        $response->assertRedirect();

        $user = User::query()
            ->where('email', $email)
            ->firstOrFail();

        $this->assertSame(
            AccountType::Customer,
            $user->account_type,
        );

        $this->assertSame(
            AccountStatus::Active,
            $user->account_status,
        );

        $this->assertTrue(
            $user->hasRole(
                RoleName::Customer->value,
            ),
        );

        $this->assertFalse(
            $user->hasRole(
                RoleName::SystemAdministrator->value,
            ),
        );

        $this->assertNull(
            $user->email_verified_at,
        );
    }
}
