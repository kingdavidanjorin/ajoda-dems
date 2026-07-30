<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use App\Services\Auth\SessionSecurity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

final class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    public function __construct(
        private readonly SessionSecurity $sessionSecurity,
    ) {}

    /**
     * Validate and reset the user's password.
     *
     * @param array<string, string> $input
     */
    public function reset(
        User $user,
        array $input,
    ): void {
        Validator::make(
            $input,
            [
                'password' => $this->passwordRules(),
            ],
        )->validate();

        DB::transaction(
            function () use ($user, $input): void {
                $isPendingStaff =
                    $user->account_type === AccountType::Staff
                    && $user->account_status
                    === AccountStatus::Pending;

                $user->forceFill([
                    'password' => Hash::make(
                        $input['password'],
                    ),
                ]);

                /*
                 * Initial setup may activate a pending staff
                 * account. Suspended and deactivated accounts
                 * retain their current status.
                 */
                if ($isPendingStaff) {
                    $user->account_status =
                        AccountStatus::Active;
                }

                $user->save();

                /*
                 * Password recovery must terminate any sessions
                 * that may have been created using the previous
                 * password or remember-me token.
                 */
                $this->sessionSecurity->revokeAll($user);
            },
        );
    }
}
