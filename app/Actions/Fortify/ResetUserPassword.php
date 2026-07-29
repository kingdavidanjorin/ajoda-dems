<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

final class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's password.
     *
     * @param array<string, string> $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
        ]);

        $isStaff = $user->account_type === AccountType::Staff
            || $user->account_type === AccountType::Staff->value;

        if ($isStaff) {
            $user->email_verified_at ??= now();
            $user->account_status = AccountStatus::Active->value;
        }

        $user->save();
    }
}