<?php

declare(strict_types=1);

namespace Tests\Feature\Identity;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\CustomerProfile;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AccountProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_users_default_to_active_customer_accounts(): void
    {
        $user = User::factory()->create()->fresh();

        $this->assertSame(AccountType::Customer, $user->account_type);
        $this->assertSame(AccountStatus::Active, $user->account_status);
    }

    public function test_registration_creates_a_customer_profile(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ajoda Customer',
            'email' => 'customer@example.com',
            'password' => 'Correct-Horse-Battery-7',
            'password_confirmation' => 'Correct-Horse-Battery-7',
        ]);

        $response->assertSessionHasNoErrors();

        $user = User::query()
            ->where('email', 'customer@example.com')
            ->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(AccountType::Customer, $user->account_type);
        $this->assertNotNull($user->customerProfile);

        $this->assertDatabaseHas('customer_profiles', [
            'user_id' => $user->id,
            'country' => 'Nigeria',
        ]);
    }

    public function test_customer_profile_belongs_to_a_user(): void
    {
        $user = User::factory()->create();

        $profile = CustomerProfile::factory()
            ->for($user)
            ->create();

        $this->assertTrue($profile->user->is($user));
        $this->assertTrue($user->fresh()->customerProfile->is($profile));
    }

    public function test_staff_profile_belongs_to_a_staff_user_and_department(): void
    {
        $department = Department::factory()->create();

        $profile = StaffProfile::factory()
            ->for($department)
            ->create();

        $this->assertSame(
            AccountType::Staff,
            $profile->user->account_type
        );

        $this->assertTrue(
            $profile->department->is($department)
        );
    }

    public function test_deleting_a_user_deletes_the_customer_profile(): void
    {
        $profile = CustomerProfile::factory()->create();
        $user = $profile->user;

        $user->delete();

        $this->assertDatabaseMissing('customer_profiles', [
            'id' => $profile->id,
        ]);
    }
}
