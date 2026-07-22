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
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class StaffManagementTest extends TestCase
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

    public function test_administrator_can_create_a_pending_staff_account(): void
    {
        Notification::fake();

        $administrator = $this->administrator();

        $response = $this
            ->actingAs($administrator)
            ->post(route('admin.staff.store'), [
                'name' => 'Ajoda Sales Officer',
                'email' => 'sales@example.com',
                'department_id' => Department::query()
                    ->where('code', 'SALES')
                    ->value('id'),
                'phone' => '08000000000',
                'job_title' => 'Sales Officer',
                'employment_date' => now()->toDateString(),
                'roles' => [
                    RoleName::SalesOfficer->value,
                ],
            ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff = User::query()
            ->where('email', 'sales@example.com')
            ->firstOrFail();

        $this->assertSame(
            AccountType::Staff,
            $staff->account_type,
        );

        $this->assertSame(
            AccountStatus::Pending,
            $staff->account_status,
        );

        $this->assertTrue(
            $staff->hasRole(RoleName::SalesOfficer->value),
        );

        $this->assertNotNull($staff->staffProfile);

        Notification::assertSentTo(
            $staff,
            VerifyEmail::class,
        );

        Notification::assertSentTo(
            $staff,
            ResetPassword::class,
        );
    }

    public function test_administrator_can_activate_and_suspend_staff(): void
    {
        $administrator = $this->administrator();
        $staff = $this->staffMember();

        $this->actingAs($administrator)
            ->patch(route('admin.staff.activate', $staff))
            ->assertRedirect();

        $this->assertSame(
            AccountStatus::Active,
            $staff->fresh()->account_status,
        );

        $this->actingAs($administrator)
            ->patch(route('admin.staff.suspend', $staff))
            ->assertRedirect();

        $this->assertSame(
            AccountStatus::Suspended,
            $staff->fresh()->account_status,
        );
    }

    public function test_administrator_can_change_staff_roles(): void
    {
        $administrator = $this->administrator();
        $staff = $this->staffMember();

        $this->actingAs($administrator)
            ->put(route('admin.staff.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'department_id' => $staff->staffProfile->department_id,
                'phone' => $staff->staffProfile->phone,
                'job_title' => 'Accounts Officer',
                'employment_date' => now()->toDateString(),
                'roles' => [
                    RoleName::AccountsOfficer->value,
                ],
            ])
            ->assertRedirect(route('admin.staff.index'));

        $staff->refresh();

        $this->assertTrue(
            $staff->hasRole(RoleName::AccountsOfficer->value),
        );

        $this->assertFalse(
            $staff->hasRole(RoleName::SalesOfficer->value),
        );
    }

    public function test_system_administrator_role_cannot_be_assigned_through_staff_form(): void
    {
        $administrator = $this->administrator();

        $this->actingAs($administrator)
            ->post(route('admin.staff.store'), [
                'name' => 'Unauthorized Administrator',
                'email' => 'unauthorized@example.com',
                'department_id' => Department::query()->value('id'),
                'job_title' => 'Administrator',
                'roles' => [
                    RoleName::SystemAdministrator->value,
                ],
            ])
            ->assertSessionHasErrors('roles.0');

        $this->assertDatabaseMissing('users', [
            'email' => 'unauthorized@example.com',
        ]);
    }

    public function test_customer_cannot_access_staff_management(): void
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $customer->assignRole(RoleName::Customer->value);

        $this->actingAs($customer)
            ->get(route('admin.staff.index'))
            ->assertForbidden();
    }

    private function administrator(): User
    {
        $administrator = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
        ]);

        $administrator->assignRole(
            RoleName::SystemAdministrator->value,
        );

        return $administrator;
    }

    private function staffMember(): User
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Pending->value,
        ]);

        $staff->staffProfile()->create([
            'department_id' => Department::query()
                ->where('code', 'SALES')
                ->value('id'),
            'staff_number' => 'STF-99999',
            'phone' => null,
            'job_title' => 'Sales Officer',
            'employment_date' => now()->toDateString(),
        ]);

        $staff->assignRole(RoleName::SalesOfficer->value);

        return $staff;
    }
}
