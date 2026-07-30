<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Services\Audit\AuditLogger;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

final class AuthenticationEventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            Login::class,
            function (Login $event): void {
                if (! $event->user instanceof User) {
                    return;
                }

                $event->user->forceFill([
                    'last_login_at' => now(),
                ])->saveQuietly();

                app(AuditLogger::class)->record(
                    'login_succeeded',
                    $event->user,
                );
            },
        );

        Event::listen(
            Failed::class,
            function (Failed $event): void {
                app(AuditLogger::class)->record(
                    'login_failed',
                    $event->user instanceof User
                        ? $event->user
                        : null,
                );
            },
        );

        Event::listen(
            Lockout::class,
            fn(): mixed =>
            app(AuditLogger::class)->record(
                'login_rate_limited',
            ),
        );

        Event::listen(
            Logout::class,
            function (Logout $event): void {
                app(AuditLogger::class)->record(
                    'logout',
                    $event->user instanceof User
                        ? $event->user
                        : null,
                );
            },
        );

        Event::listen(
            OtherDeviceLogout::class,
            function (
                OtherDeviceLogout $event,
            ): void {
                app(AuditLogger::class)->record(
                    'other_sessions_revoked',
                    $event->user instanceof User
                        ? $event->user
                        : null,
                );
            },
        );

        Event::listen(
            PasswordReset::class,
            function (PasswordReset $event): void {
                app(AuditLogger::class)->record(
                    'password_reset',
                    $event->user instanceof User
                        ? $event->user
                        : null,
                );
            },
        );
    }
}
