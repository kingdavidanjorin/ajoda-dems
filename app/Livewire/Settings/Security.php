<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use App\Services\Auth\SessionSecurity;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Security settings')]
final class Security extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(
        DisableTwoFactorAuthentication $disableTwoFactorAuthentication,
    ): void {}

    public function updatePassword(
        SessionSecurity $sessionSecurity,
    ): void {
        try {
            $validated = $this->validate([
                'current_password' =>
                $this->currentPasswordRules(),

                'password' =>
                $this->passwordRules(),
            ]);
        } catch (ValidationException $exception) {
            $this->reset(
                'current_password',
                'password',
                'password_confirmation',
            );

            throw $exception;
        }

        /** @var User $user */
        $user = Auth::user();

        $user->forceFill([
            'password' => Hash::make(
                $validated['password'],
            ),
        ])->save();

        /*
         * Refresh the persistent authentication token and
         * prepare Laravel's auth.session middleware to reject
         * other authenticated devices.
         */
        Auth::logoutOtherDevices(
            $validated['password'],
        );

        /*
         * Because DEMS uses database sessions, delete all other
         * rows immediately instead of waiting for their next
         * request.
         */
        $sessionSecurity->revokeOtherDatabaseSessions(
            $user,
            request()->session()->getId(),
        );

        request()->session()->regenerate();

        $this->reset(
            'current_password',
            'password',
            'password_confirmation',
        );

        Flux::toast(
            variant: 'success',
            text: __(
                'Password updated. Other signed-in devices were logged out.',
            ),
        );
    }
}
