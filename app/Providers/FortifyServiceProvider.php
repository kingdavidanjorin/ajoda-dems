<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::createUsersUsing(
            CreateNewUser::class,
        );

        Fortify::updateUserProfileInformationUsing(
            UpdateUserProfileInformation::class,
        );

        Fortify::updateUserPasswordsUsing(
            UpdateUserPassword::class,
        );

        Fortify::resetUserPasswordsUsing(
            ResetUserPassword::class,
        );
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(
            fn () => view('pages::auth.login'),
        );

        Fortify::registerView(
            fn () => view('pages.auth.register'),
        );

        Fortify::twoFactorChallengeView(
            fn () => view('pages::auth.two-factor-challenge'),
        );

        Fortify::confirmPasswordView(
            fn () => view('pages::auth.confirm-password'),
        );

        Fortify::verifyEmailView(
            fn () => view('livewire.auth.verify-email'),
        );

        Fortify::resetPasswordView(
            fn () => view('livewire.auth.reset-password'),
        );

        Fortify::requestPasswordResetLinkView(
            fn () => view('livewire.auth.forgot-password'),
        );
    }

    /**
     * Configure authentication rate limits.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for(
            'two-factor',
            function (Request $request): Limit {
                return Limit::perMinute(5)->by(
                    (string) $request->session()->get('login.id'),
                );
            },
        );

        RateLimiter::for(
            'login',
            function (Request $request): Limit {
                $throttleKey = Str::transliterate(
                    Str::lower(
                        (string) $request->input(
                            Fortify::username(),
                        ),
                    ).'|'.$request->ip(),
                );

                return Limit::perMinute(5)->by(
                    $throttleKey,
                );
            },
        );
    }
}
