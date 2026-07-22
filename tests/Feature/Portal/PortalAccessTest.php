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
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $customer->assignRole(RoleName::Customer->value);

        $this->actingAs($customer)
            ->get(route('dashboard'))
            ->assertRedirect(route('customer.dashboard'));

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeText('Customer Portal')
            ->assertSeeText('Dashboard')
            ->assertSeeText($customer->name);
    }

    public function test_sales_officer_is_sent_to_staff_portal(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $staff->assignRole(RoleName::SalesOfficer->value);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertRedirect(route('staff.dashboard'));

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Reservations')
            ->assertSee('Sales processing')
            ->assertDontSee('Payment verification');
    }

    public function test_accounts_officer_sees_financial_access_only(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $staff->assignRole(RoleName::AccountsOfficer->value);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertOk()
            ->assertSee('Payment verification')
            ->assertDontSee('Reservations')
            ->assertDontSee('Sales processing');
    }

    public function test_system_administrator_is_sent_to_admin_console(): void
    {
        $administrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $administrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        $this->actingAs($administrator)
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($administrator)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Administration Console');
    }

    public function test_pending_staff_cannot_enter_staff_portal(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Pending->value,
        ]);

        $staff->assignRole(RoleName::SalesOfficer->value);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertRedirect(route('access.status'));

        $this->actingAs($staff)
            ->get(route('access.status'))
            ->assertOk()
            ->assertSee('awaiting administrator approval');
    }

    public function test_customer_cannot_enter_staff_or_admin_portals(): void
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $customer->assignRole(RoleName::Customer->value);

        $this->actingAs($customer)
            ->get(route('staff.dashboard'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeText('Customer Portal')
            ->assertSeeText('Dashboard')
            ->assertSeeText($customer->name);
    }
}
