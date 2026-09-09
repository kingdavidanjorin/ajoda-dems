<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DepartmentSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminRouteAuthorizationTest extends TestCase
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

    public function test_sales_officer_cannot_access_admin_staff_list(): void
    {
        $salesOfficer = $this->salesOfficer();

        $this->actingAs($salesOfficer)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    public function test_sales_officer_cannot_create_staff_account(): void
    {
        $salesOfficer = $this->salesOfficer();

        $email = 'unauthorized-created-staff@example.com';

        $this->actingAs($salesOfficer)
            ->post(route('admin.staff.store'), [
                'name' => 'Unauthorized Accounts Officer',
                'email' => $email,
                'department_id' => Department::query()
                    ->where('code', 'ACCOUNTS')
                    ->value('id'),
                'phone' => '08000000000',
                'job_title' => 'Accounts Officer',
                'employment_date' => now()->toDateString(),
                'roles' => [
                    RoleName::AccountsOfficer->value,
                ],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('users', [
            'email' => $email,
        ]);
    }

    public function test_sales_officer_cannot_suspend_another_staff_member(): void
    {
        $salesOfficer = $this->salesOfficer();

        $targetStaff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $targetStaff->assignRole(
            RoleName::AccountsOfficer->value,
        );

        $this->actingAs($salesOfficer)
            ->patch(
                route(
                    'admin.staff.suspend',
                    $targetStaff,
                ),
            )
            ->assertForbidden();

        $targetStaff->refresh();

        $this->assertSame(
            AccountStatus::Active,
            $targetStaff->account_status,
        );
    }

    private function salesOfficer(): User
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(
            RoleName::SalesOfficer->value,
        );

        return $user;
    }
}
