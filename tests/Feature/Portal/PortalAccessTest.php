<?php

declare(strict_types=1);

namespace Tests\Feature\Portal;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_is_sent_to_customer_dashboard(): void
    {
        $customer = $this->activeUser(
            AccountType::Customer,
            RoleName::Customer,
        );

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeText('Account overview')
            ->assertSeeText('My purchases')
            ->assertSeeText('My properties');
    }

    public function test_sales_officer_is_sent_to_staff_portal(): void
    {
        $salesOfficer = $this->activeUser(
            AccountType::Staff,
            RoleName::SalesOfficer,
        );

        $this->actingAs($salesOfficer)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Sales Officer')
            ->assertSeeText('Reservations')
            ->assertSeeText('Purchases')
            ->assertSeeText('Payments')
            ->assertDontSeeText('Construction progress');
    }

    public function test_accounts_officer_sees_financial_access_only(): void
    {
        $accountsOfficer = $this->activeUser(
            AccountType::Staff,
            RoleName::AccountsOfficer,
        );

        $this->actingAs($accountsOfficer)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSeeText('Accounts Officer')
            ->assertSeeText('Payments')
            ->assertSeeText('Purchases')
            ->assertDontSeeText('Reservations')
            ->assertDontSeeText('Property enquiries');
    }

    public function test_system_administrator_is_sent_to_admin_console(): void
    {
        $administrator = $this->activeUser(
            AccountType::Staff,
            RoleName::SystemAdministrator,
        );

        $this->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeText('System Administration')
            ->assertSeeText('Staff accounts')
            ->assertSeeText('Roles and permissions')
            ->assertSeeText('System settings');
    }

    public function test_pending_staff_cannot_enter_staff_portal(): void
    {
        /** @var User $pendingStaff */
        $pendingStaff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => $this->nonActiveStatus()->value,
            'email_verified_at' => now(),
        ]);

        $pendingStaff->assignRole(
            RoleName::SalesOfficer->value,
        );

        $this->actingAs($pendingStaff)
            ->get(route('staff.dashboard'))
            ->assertRedirect();
    }

    public function test_customer_cannot_enter_staff_or_admin_portals(): void
    {
        $customer = $this->activeUser(
            AccountType::Customer,
            RoleName::Customer,
        );

        $this->actingAs($customer)
            ->get(route('staff.dashboard'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    private function activeUser(
        AccountType $accountType,
        RoleName $role,
    ): User {
        /** @var User $user */
        $user = User::factory()->create([
            'account_type' => $accountType->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($role->value);

        return $user;
    }

    private function nonActiveStatus(): AccountStatus
    {
        foreach (AccountStatus::cases() as $status) {
            if ($status !== AccountStatus::Active) {
                return $status;
            }
        }

        throw new \RuntimeException(
            'AccountStatus must contain a non-active status.',
        );
    }
}
