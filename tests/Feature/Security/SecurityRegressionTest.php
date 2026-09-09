<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\DocumentType;
use App\Enums\DocumentVisibility;
use App\Enums\RoleName;
use App\Models\House;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG_PASSWORD = 'Correct-Horse-Battery-7';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_ip_limiter_blocks_password_spraying_after_thirty_attempts(): void
    {
        $ip = '203.0.113.50';

        for ($attempt = 1; $attempt <= 30; $attempt++) {
            $response = $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(route('login.store'), [
                'email' => "spray-{$attempt}@example.com",
                'password' => 'wrong-password',
            ]);

            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(route('login.store'), [
            'email' => 'spray-31@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_registration_is_rate_limited_after_five_requests_from_one_ip(): void
    {
        $ip = '203.0.113.60';

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(route('register.store'), [
                'name' => "Rate Limit User {$attempt}",
                'email' => "register-{$attempt}@example.com",
                'password' => self::STRONG_PASSWORD,
                'password_confirmation' => self::STRONG_PASSWORD,
            ])->assertSessionHasNoErrors();

            // Registration authenticates the new user. Log out so the
            // next request still exercises the guest registration route.
            $this->post(route('logout'));
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(route('register.store'), [
            'name' => 'Rate Limit User 6',
            'email' => 'register-6@example.com',
            'password' => self::STRONG_PASSWORD,
            'password_confirmation' => self::STRONG_PASSWORD,
        ])->assertTooManyRequests();
    }

    public function test_password_reset_link_is_rate_limited_per_account_and_ip(): void
    {
        Notification::fake();

        $ip = '203.0.113.70';
        $payload = ['email' => 'reset-target@example.com'];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $response = $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(route('password.email'), $payload);

            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(route('password.email'), $payload)
            ->assertTooManyRequests();
    }

    public function test_password_reset_link_ip_limiter_blocks_account_spraying_after_twenty_requests(): void
    {
        Notification::fake();

        $ip = '203.0.113.71';

        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $response = $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(route('password.email'), [
                'email' => "reset-spray-{$attempt}@example.com",
            ]);

            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(route('password.email'), [
            'email' => 'reset-spray-21@example.com',
        ])->assertTooManyRequests();
    }

    public function test_password_reset_submission_is_rate_limited_after_ten_requests_per_ip(): void
    {
        $ip = '203.0.113.72';

        $payload = [
            'token' => 'invalid-token',
            'email' => 'nobody@example.com',
            'password' => self::STRONG_PASSWORD,
            'password_confirmation' => self::STRONG_PASSWORD,
        ];

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $response = $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(route('password.update'), $payload);

            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(route('password.update'), $payload)
            ->assertTooManyRequests();
    }

    public function test_public_property_enquiry_is_rate_limited_after_ten_requests_per_minute(): void
    {
        $house = House::factory()->create([
            'is_published' => true,
        ]);

        $ip = '203.0.113.80';

        $payload = [
            'full_name' => 'Security Tester',
            'email' => 'security-tester@example.com',
            'phone' => '08000000000',
            'message' => 'I would like more information about this property.',
        ];

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            $response = $this->withServerVariables([
                'REMOTE_ADDR' => $ip,
            ])->post(
                route('properties.enquiries.store', $house),
                $payload,
            );

            $this->assertNotSame(429, $response->getStatusCode());
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => $ip,
        ])->post(
            route('properties.enquiries.store', $house),
            $payload,
        )->assertTooManyRequests();
    }

    public function test_registration_payload_cannot_promote_customer_to_staff_or_administrator(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Privilege Escalation Attempt',
            'email' => 'privilege-test@example.com',
            'password' => self::STRONG_PASSWORD,
            'password_confirmation' => self::STRONG_PASSWORD,

            // These fields must be ignored by CreateNewUser.
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'roles' => [RoleName::SystemAdministrator->value],
        ])->assertSessionHasNoErrors();

        $user = User::query()
            ->where('email', 'privilege-test@example.com')
            ->firstOrFail();

        $this->assertSame(AccountType::Customer, $user->account_type);
        $this->assertTrue($user->hasRole(RoleName::Customer->value));
        $this->assertFalse(
            $user->hasRole(RoleName::SystemAdministrator->value),
        );
    }

    public function test_non_administrator_staff_cannot_open_admin_control_centre(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $staff->assignRole(RoleName::SalesOfficer->value);

        $this->actingAs($staff)
            ->get(route('admin.system-settings.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.access-control.index'))
            ->assertForbidden();

        $this->actingAs($staff)
            ->get(route('admin.audit.index'))
            ->assertForbidden();
    }

    public function test_suspended_staff_is_blocked_even_if_they_already_have_an_authenticated_session(): void
    {
        $staff = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $staff->assignRole(RoleName::SalesOfficer->value);

        $this->actingAs($staff)
            ->get(route('staff.dashboard'))
            ->assertOk();

        $staff->forceFill([
            'account_status' => AccountStatus::Suspended->value,
        ])->save();

        $this->get(route('staff.dashboard'))
            ->assertRedirect(route('access.status'));
    }

    public function test_document_upload_rejects_executable_php_files(): void
    {
        $documentOfficer = User::factory()->create([
            'account_type' => AccountType::Staff->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $documentOfficer->assignRole(RoleName::DocumentOfficer->value);

        $customer = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        $customer->assignRole(RoleName::Customer->value);

        $maliciousFile = UploadedFile::fake()->create(
            'shell.php',
            1,
            'application/x-php',
        );

        $this->actingAs($documentOfficer)
            ->post(route('staff.documents.store'), [
                'customer_id' => $customer->id,
                'purchase_id' => null,
                'document_type' => DocumentType::Other->value,
                'visibility' => DocumentVisibility::StaffOnly->value,
                'title' => 'Malicious Upload Attempt',
                'description' => 'Security regression test.',
                'file' => $maliciousFile,
            ])
            ->assertSessionHasErrors('file');
    }
}
