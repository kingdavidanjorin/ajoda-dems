<?php

declare(strict_types=1);

namespace Tests\Feature\Authorization;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PurchasePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_sales_officer_can_confirm_purchases(): void
    {
        $salesOfficer = $this->staffUser(
            RoleName::SalesOfficer,
        );

        $this->assertTrue(
            $salesOfficer->can(
                PermissionName::PurchasesConfirm->value,
            ),
        );
    }

    public function test_accounts_officer_cannot_confirm_purchases(): void
    {
        $accountsOfficer = $this->staffUser(
            RoleName::AccountsOfficer,
        );

        $this->assertFalse(
            $accountsOfficer->can(
                PermissionName::PurchasesConfirm->value,
            ),
        );
    }

    private function staffUser(
        RoleName $role,
    ): User {
        $user = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role->value);

        return $user;
    }
}