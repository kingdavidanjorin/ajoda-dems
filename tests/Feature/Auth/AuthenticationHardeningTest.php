<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Actions\Fortify\ResetUserPassword;
use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Services\Auth\SessionSecurity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

final class AuthenticationHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const STRONG_PASSWORD =
    'Correct-Horse-Battery-7';

    public function test_suspended_staff_cannot_log_in(): void
    {
        $staff = User::factory()->create([
            'email' => 'suspended@example.com',

            'account_type' =>
            AccountType::Staff->value,

            'account_status' =>
            AccountStatus::Suspended->value,

            'email_verified_at' => now(),
        ]);

        $this->post(
            route('login.store'),
            [
                'email' => $staff->email,
                'password' => 'password',
            ],
        )
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_pending_staff_is_activated_after_initial_password_setup(): void
    {
        $staff = User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,

            'account_status' =>
            AccountStatus::Pending->value,
        ]);

        app(ResetUserPassword::class)->reset(
            $staff,
            [
                'password' =>
                'Correct-Horse-Battery-7',

                'password_confirmation' =>
                'Correct-Horse-Battery-7',
            ],
        );

        $this->assertSame(
            AccountStatus::Active,
            $staff->fresh()->account_status,
        );
    }

    public function test_suspended_staff_remains_suspended_after_password_reset(): void
    {
        $staff = User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,

            'account_status' =>
            AccountStatus::Suspended->value,
        ]);

        app(ResetUserPassword::class)->reset(
            $staff,
            [
                'password' =>
                self::STRONG_PASSWORD,

                'password_confirmation' =>
                self::STRONG_PASSWORD,
            ],
        );

        $this->assertSame(
            AccountStatus::Suspended,
            $staff->fresh()->account_status,
        );
    }

    public function test_deactivated_staff_remains_deactivated_after_password_reset(): void
    {
        $staff = User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,

            'account_status' =>
            AccountStatus::Deactivated->value,
        ]);

        app(ResetUserPassword::class)->reset(
            $staff,
            [
                'password' =>
                self::STRONG_PASSWORD,

                'password_confirmation' =>
                self::STRONG_PASSWORD,
            ],
        );

        $this->assertSame(
            AccountStatus::Deactivated,
            $staff->fresh()->account_status,
        );
    }

    public function test_revoking_all_sessions_deletes_database_sessions_and_rotates_remember_token(): void
    {
        $user = User::factory()->create([
            'remember_token' => 'known-token',
        ]);

        DB::table('sessions')->insert([
            'id' => (string) Str::uuid(),
            'user_id' => $user->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Security test',
            'payload' => 'test-payload',
            'last_activity' => now()->timestamp,
        ]);

        app(SessionSecurity::class)->revokeAll(
            $user,
        );

        $this->assertDatabaseMissing(
            'sessions',
            [
                'user_id' => $user->id,
            ],
        );

        $this->assertNotSame(
            'known-token',
            $user->fresh()->getRememberToken(),
        );
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $credentials = [
            'email' => 'rate-limit@example.com',
            'password' => 'incorrect-password',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.10',
            ])
                ->post(
                    route('login.store'),
                    $credentials,
                )
                ->assertSessionHasErrors('email');
        }

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ])
            ->post(
                route('login.store'),
                $credentials,
            )
            ->assertTooManyRequests();
    }

    public function test_password_reset_response_does_not_reveal_account_existence(): void
    {
        Notification::fake();

        User::factory()->create([
            'email' => 'known@example.com',
        ]);

        $message =
            'If an account exists for that email address, a password reset link will be sent.';

        $this->post(
            route('password.email'),
            [
                'email' => 'known@example.com',
            ],
        )->assertSessionHas('status', $message);

        $this->post(
            route('password.email'),
            [
                'email' => 'unknown@example.com',
            ],
        )->assertSessionHas('status', $message);
    }

    public function test_suspended_user_cannot_reach_profile_settings(): void
    {
        $staff = User::factory()->create([
            'account_type' =>
            AccountType::Staff->value,

            'account_status' =>
            AccountStatus::Suspended->value,

            'email_verified_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('profile.edit'))
            ->assertRedirect(
                route('access.status'),
            );
    }

    public function test_two_factor_limiter_is_connected(): void
    {
        $this->assertSame(
            'two-factor',
            config('fortify.limiters.two-factor'),
        );
    }
}
