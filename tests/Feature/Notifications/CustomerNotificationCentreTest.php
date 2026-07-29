<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CustomerNotificationCentreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_customer_can_view_only_own_notifications(): void
    {
        $customer = $this->customer();
        $otherCustomer = $this->customer();

        $this->notification(
            $customer,
            'Your inspection has been scheduled.',
        );

        $this->notification(
            $otherCustomer,
            'Another customer notification.',
        );

        $this->actingAs($customer)
            ->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertSeeText(
                'Your inspection has been scheduled.',
            )
            ->assertDontSeeText(
                'Another customer notification.',
            );
    }

    public function test_customer_can_mark_own_notification_as_read(): void
    {
        $customer = $this->customer();

        $notification = $this->notification(
            $customer,
            'Unread customer notification.',
        );

        $this->assertNull($notification->read_at);

        $this->actingAs($customer)
            ->patch(
                route(
                    'customer.notifications.read',
                    $notification->id,
                ),
            )
            ->assertRedirect(
                route('customer.notifications.index'),
            )
            ->assertSessionHas('notification_status');

        $this->assertNotNull(
            $notification->fresh()->read_at,
        );
    }

    public function test_customer_cannot_mark_another_customers_notification_as_read(): void
    {
        $customer = $this->customer();
        $otherCustomer = $this->customer();

        $notification = $this->notification(
            $otherCustomer,
            'Protected customer notification.',
        );

        $this->actingAs($customer)
            ->patch(
                route(
                    'customer.notifications.read',
                    $notification->id,
                ),
            )
            ->assertNotFound();

        $this->assertNull(
            $notification->fresh()->read_at,
        );
    }

    public function test_customer_can_mark_all_own_notifications_as_read(): void
    {
        $customer = $this->customer();
        $otherCustomer = $this->customer();

        $this->notification(
            $customer,
            'First unread notification.',
        );

        $this->notification(
            $customer,
            'Second unread notification.',
        );

        $otherNotification = $this->notification(
            $otherCustomer,
            'Other customer unread notification.',
        );

        $this->actingAs($customer)
            ->patch(
                route('customer.notifications.read-all'),
            )
            ->assertRedirect(
                route('customer.notifications.index'),
            );

        $this->assertSame(
            0,
            $customer->fresh()
                ->unreadNotifications()
                ->count(),
        );

        $this->assertNull(
            $otherNotification->fresh()->read_at,
        );
    }

    private function notification(
        User $user,
        string $message,
    ): DatabaseNotification {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'test_notification',
            'notifiable_type' => $user->getMorphClass(),
            'notifiable_id' => $user->id,
            'data' => json_encode(
                [
                    'event' => 'test_event',
                    'title' => 'Test notification',
                    'message' => $message,
                    'reference' =>
                    'AJD-NOT-' . Str::upper(
                        Str::random(8),
                    ),
                    'url' => route(
                        'customer.notifications.index',
                    ),
                ],
                JSON_THROW_ON_ERROR,
            ),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DatabaseNotification::query()
            ->findOrFail($id);
    }

    private function customer(): User
    {
        $customer = User::factory()->create([
            'account_type' =>
            AccountType::Customer->value,
            'account_status' =>
            AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(
            RoleName::Customer->value,
        );

        return $customer;
    }
}
