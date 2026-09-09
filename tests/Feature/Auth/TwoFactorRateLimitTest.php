<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use Laravel\Fortify\Fortify;
use Mockery;
use Tests\TestCase;

final class TwoFactorRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_factor_challenge_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->create([
            'account_type' => AccountType::Customer->value,
            'account_status' => AccountStatus::Active->value,
            'email_verified_at' => now(),
        ]);

        /*
         * Fortify expects the 2FA secret to be encrypted.
         * The actual value is irrelevant here because the
         * authentication provider is mocked to reject it.
         */
        $user->forceFill([
            'two_factor_secret' =>
            Fortify::currentEncrypter()->encrypt(
                'JBSWY3DPEHPK3PXP',
            ),
        ])->save();

        $provider = Mockery::mock(
            TwoFactorAuthenticationProvider::class,
        );

        /*
         * Only the first five requests should reach the
         * actual 2FA verification logic.
         *
         * The sixth request should be stopped by the
         * throttle middleware before verification occurs.
         */
        $provider
            ->shouldReceive('verify')
            ->times(5)
            ->andReturnFalse();

        $this->app->instance(
            TwoFactorAuthenticationProvider::class,
            $provider,
        );

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withSession([
                'login.id' => $user->id,
            ])
                ->withServerVariables([
                    'REMOTE_ADDR' => '203.0.113.77',
                ])
                ->post(
                    route('two-factor.login.store'),
                    [
                        'code' => '000000',
                    ],
                )
                ->assertRedirect();

            $this->assertGuest();
        }

        $this->withSession([
            'login.id' => $user->id,
        ])
            ->withServerVariables([
                'REMOTE_ADDR' => '203.0.113.77',
            ])
            ->post(
                route('two-factor.login.store'),
                [
                    'code' => '000000',
                ],
            )
            ->assertTooManyRequests();

        $this->assertGuest();
    }
}
