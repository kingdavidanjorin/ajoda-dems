<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * @param array<string, string> $input
     */
    public function create(array $input): User
    {
        $normalised = [
            'name' => trim(
                (string) ($input['name'] ?? ''),
            ),

            'email' => Str::lower(
                trim(
                    (string) ($input['email'] ?? ''),
                ),
            ),

            'password' =>
            (string) ($input['password'] ?? ''),

            'password_confirmation' =>
            (string) (
                $input['password_confirmation']
                ?? ''
            ),
        ];

        Validator::make(
            $normalised,
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',

                    Rule::unique(
                        User::class,
                        'email',
                    ),
                ],

                'password' =>
                $this->passwordRules(),
            ],
        )->validate();

        return DB::transaction(
            function () use ($normalised): User {
                $user = User::query()->create([
                    'name' => $normalised['name'],
                    'email' => $normalised['email'],

                    'password' => Hash::make(
                        $normalised['password'],
                    ),

                    'account_type' =>
                    AccountType::Customer->value,

                    'account_status' =>
                    AccountStatus::Active->value,
                ]);

                $user->customerProfile()->create([
                    'country' => (string) config(
                        'dems.customer.default_country',
                    ),
                ]);

                $user->assignRole(
                    RoleName::Customer->value,
                );

                return $user;
            },
        );
    }
}
