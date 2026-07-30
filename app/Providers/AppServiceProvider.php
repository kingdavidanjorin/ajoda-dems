<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        $this->configureLocalAuthenticationLinks();
    }

    private function configureLocalAuthenticationLinks(): void
    {
        if (
            ! app()->environment('local')
            || ! config('dems.auth_debug.write_links')
        ) {
            return;
        }

        $resetFilename = basename(
            (string) config(
                'dems.auth_debug.reset_link_filename',
            ),
        );

        $verificationFilename = basename(
            (string) config(
                'dems.auth_debug.verification_link_filename',
            ),
        );

        $resetLinkPath = storage_path(
            'app/' . $resetFilename,
        );

        $verificationLinkPath = storage_path(
            'app/' . $verificationFilename,
        );

        $applicationName = (string) config(
            'app.name',
        );

        ResetPassword::createUrlUsing(
            function (
                object $notifiable,
                string $token,
            ) use ($resetLinkPath): string {
                $url = route(
                    'password.reset',
                    [
                        'token' => $token,

                        'email' =>
                        $notifiable
                            ->getEmailForPasswordReset(),
                    ],
                );

                file_put_contents(
                    $resetLinkPath,
                    $url . PHP_EOL,
                    LOCK_EX,
                );

                return $url;
            },
        );

        VerifyEmail::toMailUsing(
            function (
                object $notifiable,
                string $url,
            ) use (
                $verificationLinkPath,
                $applicationName,
            ): MailMessage {
                file_put_contents(
                    $verificationLinkPath,
                    $url . PHP_EOL,
                    LOCK_EX,
                );

                return (new MailMessage)
                    ->subject(
                        "Verify Your {$applicationName} Account",
                    )
                    ->greeting(
                        'Hello ' . $notifiable->name . ',',
                    )
                    ->line(
                        'Click the button below to verify your email address.',
                    )
                    ->action(
                        'Verify Email Address',
                        $url,
                    )
                    ->line(
                        'Ignore this message if you did not create this account.',
                    );
            },
        );
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            function (): Password {
                $rule = Password::min(15);

                return app()->isProduction()
                    ? $rule->uncompromised()
                    : $rule;
            },
        );
    }
}
