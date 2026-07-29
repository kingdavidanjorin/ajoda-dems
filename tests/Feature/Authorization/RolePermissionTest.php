<?php

declare(strict_types=1);

namespace Tests\Feature\Authorization;

use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_all_roles_and_permissions_are_seeded(): void
    {
        $this->assertSame(
            count(RoleName::cases()),
            Role::query()->count(),
        );

        $this->assertSame(
            count(PermissionName::cases()),
            Permission::query()->count(),
        );
    }

    public function test_newly_registered_users_receive_the_customer_role(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ajoda Customer',
            'email' => 'customer-role@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::query()
            ->where('email', 'customer-role@example.com')
            ->firstOrFail();

        $this->assertTrue(
            $user->hasRole(RoleName::Customer->value),
        );

        $this->assertTrue(
            $user->can(PermissionName::ReservationsCreate->value),
        );

        $this->assertFalse(
            $user->can(PermissionName::ReservationsManage->value),
        );
    }

    public function test_sales_and_accounts_duties_are_separated(): void
    {
        $salesOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
        ]);

        $salesOfficer->assignRole(
            RoleName::SalesOfficer->value,
        );

        $this->assertTrue(
            $salesOfficer->can(
                PermissionName::ReservationsManage->value,
            ),
        );

        $this->assertTrue(
            $salesOfficer->can(
                PermissionName::SalesManage->value,
            ),
        );

        $this->assertFalse(
            $salesOfficer->can(
                PermissionName::PaymentsVerify->value,
            ),
        );

        $this->assertTrue(
            $salesOfficer->can(
                PermissionName::PurchasesConfirm->value,
            ),
        );

        $accountsOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
        ]);

        $accountsOfficer->assignRole(
            RoleName::AccountsOfficer->value,
        );

        $this->assertTrue(
            $accountsOfficer->can(
                PermissionName::PaymentsVerify->value,
            ),
        );

        $this->assertFalse(
            $accountsOfficer->can(
                PermissionName::PurchasesConfirm->value,
            ),
        );

        $this->assertFalse(
            $accountsOfficer->can(
                PermissionName::ReservationsManage->value,
            ),
        );

        $this->assertFalse(
            $accountsOfficer->can(
                PermissionName::SalesManage->value,
            ),
        );
    }

    public function test_executive_management_has_read_only_oversight(): void
    {
        $executive = User::factory()->create([
            'account_type' => AccountType::Staff->value,
        ]);

        $executive->assignRole(
            RoleName::ExecutiveManagement->value,
        );

        $this->assertTrue(
            $executive->can(PermissionName::ReportsView->value),
        );

        $this->assertTrue(
            $executive->can(PermissionName::AuditView->value),
        );

        $this->assertFalse(
            $executive->can(PermissionName::SalesManage->value),
        );

        $this->assertFalse(
            $executive->can(PermissionName::PaymentsVerify->value),
        );

        $this->assertFalse(
            $executive->can(PermissionName::PropertiesManage->value),
        );
    }

    public function test_system_administrator_has_every_permission(): void
    {
        $administrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
        ]);

        $administrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        foreach (PermissionName::cases() as $permission) {
            $this->assertTrue(
                $administrator->can($permission->value),
                "System Administrator lacks {$permission->value}.",
            );
        }
    }

    public function test_staff_members_can_hold_multiple_approved_roles(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
        ]);

        $staff->syncRoles([
            RoleName::SalesOfficer->value,
            RoleName::CustomerServiceOfficer->value,
        ]);

        $this->assertTrue(
            $staff->hasAllRoles([
                RoleName::SalesOfficer->value,
                RoleName::CustomerServiceOfficer->value,
            ]),
        );

        $this->assertTrue(
            $staff->can(PermissionName::ReservationsManage->value),
        );

        $this->assertTrue(
            $staff->can(PermissionName::CasesManage->value),
        );

        $this->assertFalse(
            $staff->can(PermissionName::PaymentsVerify->value),
        );
    }
}
