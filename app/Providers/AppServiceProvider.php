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
        if (app()->environment('local')) {
            ResetPassword::createUrlUsing(
                function (object $notifiable, string $token, ): string {
                    $url = route(
                        'password.reset',
                        [
                            'token' => $token,
                            'email' => $notifiable->getEmailForPasswordReset(),
                        ],
                    );

                    file_put_contents(
                        storage_path('app/staff-setup-link.txt'),
                        $url . PHP_EOL,
                        LOCK_EX,
                    );

                    return $url;
                },
            );
            VerifyEmail::toMailUsing(
                function (object $notifiable, string $url, ): MailMessage {
                    file_put_contents(
                        storage_path('app/verification-link.txt'),
                        $url . PHP_EOL,
                        LOCK_EX,
                    );

                    return (new MailMessage)
                        ->subject('Verify Your Ajoda GRA Account')
                        ->greeting('Hello ' . $notifiable->name . ',')
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

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
