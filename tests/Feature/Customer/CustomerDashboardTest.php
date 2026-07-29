<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_the_dashboard(): void
    {
        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeText('Account overview')
            ->assertSeeText('Recent reservations')
            ->assertSeeText('Recent purchases');
    }
}