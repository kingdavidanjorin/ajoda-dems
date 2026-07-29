<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\House;
use App\Models\User;
use Database\Seeders\EstatePropertySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ApplicationSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(EstatePropertySeeder::class);
    }

    public function test_property_metadata_cannot_execute_stored_xss(): void
    {
        $house = House::query()
            ->where('is_published', true)
            ->firstOrFail();

        $house->forceFill([
            'name' =>
            '</title><script>alert("title-xss")</script>',

            'description' =>
            '"><script>alert("meta-xss")</script>',
        ])->save();

        $this->get(route('properties.show', $house))
            ->assertOk()
            ->assertDontSee(
                '<script>alert("title-xss")</script>',
                false,
            )
            ->assertDontSee(
                '<script>alert("meta-xss")</script>',
                false,
            );
    }

    public function test_notification_links_ignore_unsafe_stored_urls(): void
    {
        $customer = $this->customer();

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'security-test-notification',
            'notifiable_type' => $customer->getMorphClass(),
            'notifiable_id' => $customer->id,

            'data' => json_encode(
                [
                    'event' => 'inspection_scheduled',
                    'title' => 'Inspection scheduled',
                    'message' => 'Your inspection is ready.',
                    'reference' => 'AJD-SECURITY-TEST',

                    'url' =>
                    'javascript:alert(document.domain)',
                ],
                JSON_THROW_ON_ERROR,
            ),

            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('customer.notifications.index'))
            ->assertOk()
            ->assertSee(
                'href="' .
                    route('customer.inspections.index') .
                    '"',
                false,
            )
            ->assertDontSee(
                'javascript:',
                false,
            );
    }

    public function test_catalogue_search_does_not_execute_sql_input(): void
    {
        House::factory()->create([
            'name' => 'Hidden SQL Injection Target',
            'slug' => 'hidden-sql-injection-target',
            'is_published' => false,
        ]);

        $this->get(
            route('properties.index', [
                'search' => "%' OR 1=1 --",
            ]),
        )
            ->assertOk()
            ->assertDontSeeText(
                'Hidden SQL Injection Target',
            );
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
