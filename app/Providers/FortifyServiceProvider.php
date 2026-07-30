<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\PasswordResetLinkResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

final class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /*
         * Return the same message for existing and nonexistent
         * password-reset accounts.
         */
        $this->app->bind(
            SuccessfulPasswordResetLinkRequestResponse::class,
            PasswordResetLinkResponse::class,
        );

        $this->app->bind(
            FailedPasswordResetLinkRequestResponse::class,
            PasswordResetLinkResponse::class,
        );
    }

    public function boot(): void
    {
        $this->configureActions();
        $this->configureAuthentication();
        $this->configureViews();
        $this->configureRateLimiting();
        $this->configureRouteThrottles();
    }

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

    private function configureAuthentication(): void
    {
        Fortify::authenticateUsing(
            function (Request $request): ?User {
                $email = Str::lower(
                    trim(
                        (string) $request->input(
                            Fortify::username(),
                        ),
                    ),
                );

                $password = (string) $request->input(
                    'password',
                );

                /** @var User|null $user */
                $user = User::query()
                    ->where('email', $email)
                    ->first();

                if (
                    $user === null
                    || ! $user->isActive()
                    || ! Hash::check(
                        $password,
                        $user->password,
                    )
                ) {
                    return null;
                }

                /*
                 * Upgrade an older password hash when the
                 * configured work factor changes.
                 */
                if (Hash::needsRehash($user->password)) {
                    $user->forceFill([
                        'password' => Hash::make(
                            $password,
                        ),
                    ])->save();
                }

                return $user;
            },
        );
    }

    private function configureViews(): void
    {
        Fortify::loginView(
            fn() => view('pages::auth.login'),
        );

        Fortify::registerView(
            fn() => view('pages.auth.register'),
        );

        Fortify::twoFactorChallengeView(
            fn() => view(
                'pages::auth.two-factor-challenge',
            ),
        );

        Fortify::confirmPasswordView(
            fn() => view(
                'pages::auth.confirm-password',
            ),
        );

        Fortify::verifyEmailView(
            fn() => view(
                'livewire.auth.verify-email',
            ),
        );

        Fortify::resetPasswordView(
            fn() => view(
                'livewire.auth.reset-password',
            ),
        );

        Fortify::requestPasswordResetLinkView(
            fn() => view(
                'livewire.auth.forgot-password',
            ),
        );
    }

    private function configureRateLimiting(): void
    {
        RateLimiter::for(
            'login',
            function (Request $request): array {
                $email = $this->normalisedEmail(
                    $request,
                );

                $ipAddress = (string) $request->ip();

                return [
                    /*
                     * Protect one account from repeated attempts
                     * originating from one address.
                     */
                    Limit::perMinute(5)->by(
                        'login-account|'
                            . $email
                            . '|'
                            . $ipAddress,
                    ),

                    /*
                     * Protect against password spraying from one
                     * address across many email accounts.
                     */
                    Limit::perMinute(30)->by(
                        'login-ip|' . $ipAddress,
                    ),
                ];
            },
        );

        RateLimiter::for(
            'two-factor',
            function (Request $request): array {
                $loginId = (string) $request
                    ->session()
                    ->get('login.id', 'unknown');

                $ipAddress = (string) $request->ip();

                return [
                    Limit::perMinute(5)->by(
                        'two-factor-user|'
                            . $loginId
                            . '|'
                            . $ipAddress,
                    ),

                    Limit::perMinute(20)->by(
                        'two-factor-ip|' . $ipAddress,
                    ),
                ];
            },
        );

        RateLimiter::for(
            'registration',
            fn(Request $request): Limit =>
            Limit::perHour(5)->by(
                'registration|' . $request->ip(),
            ),
        );

        RateLimiter::for(
            'password-reset-link',
            function (Request $request): array {
                $email = $this->normalisedEmail(
                    $request,
                );

                $ipAddress = (string) $request->ip();

                return [
                    Limit::perHour(5)->by(
                        'password-reset-account|'
                            . $email
                            . '|'
                            . $ipAddress,
                    ),

                    Limit::perHour(20)->by(
                        'password-reset-ip|' . $ipAddress,
                    ),
                ];
            },
        );

        RateLimiter::for(
            'password-reset-submit',
            fn(Request $request): Limit =>
            Limit::perHour(10)->by(
                'password-reset-submit|'
                    . $request->ip(),
            ),
        );
    }

    /**
     * Fortify directly assigns throttling middleware only to
     * login and two-factor routes. Attach the additional named
     * limiters after all application routes have been registered.
     */
    private function configureRouteThrottles(): void
    {
        $this->app->booted(
            function (): void {
                /** @var Router $router */
                $router = $this->app->make(
                    Router::class,
                );

                $this->attachThrottle(
                    $router,
                    'register.store',
                    'registration',
                );

                $this->attachThrottle(
                    $router,
                    'password.email',
                    'password-reset-link',
                );

                $this->attachThrottle(
                    $router,
                    'password.update',
                    'password-reset-submit',
                );
            },
        );
    }

    private function attachThrottle(
        Router $router,
        string $routeName,
        string $limiter,
    ): void {
        $route = $router
            ->getRoutes()
            ->getByName($routeName);

        if ($route === null) {
            return;
        }

        $route->middleware(
            'throttle:' . $limiter,
        );
    }

    private function normalisedEmail(
        Request $request,
    ): string {
        return Str::transliterate(
            Str::lower(
                trim(
                    (string) $request->input(
                        Fortify::username(),
                    ),
                ),
            ),
        );
    }
}
