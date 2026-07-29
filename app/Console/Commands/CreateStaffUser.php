<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use BackedEnum;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Throwable;
use UnitEnum;

final class CreateStaffUser extends Command
{
    protected $signature = 'dems:create-staff-user
        {--name= : Staff member name}
        {--email= : Staff member email address}
        {--role= : Existing staff role name}
        {--update : Update an existing user with this email}';

    protected $description =
        'Create or update an active, email-verified staff user and assign a role.';

    public function handle(): int
    {
        try {
            $roles = Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->pluck('name')
                ->values()
                ->all();

            if ($roles === []) {
                $this->error(
                    'No roles were found. Run the role and permission seeder first.',
                );

                return self::FAILURE;
            }

            $name = trim(
                (string) (
                    $this->option('name')
                    ?: $this->ask('Full name')
                ),
            );

            $email = Str::lower(
                trim(
                    (string) (
                        $this->option('email')
                        ?: $this->ask('Email address')
                    ),
                ),
            );

            $validation = Validator::make(
                [
                    'name' => $name,
                    'email' => $email,
                ],
                [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                ],
            );

            if ($validation->fails()) {
                foreach ($validation->errors()->all() as $error) {
                    $this->error($error);
                }

                return self::FAILURE;
            }

            $role = $this->resolveRole($roles);

            if ($role === null) {
                return self::FAILURE;
            }

            $existingUser = User::query()
                ->where('email', $email)
                ->first();

            if (
                $existingUser !== null
                && ! $this->option('update')
                && ! $this->confirm(
                    'A user with this email already exists. Update the account?',
                    false,
                )
            ) {
                $this->warn('No changes were made.');

                return self::SUCCESS;
            }

            $password = $this->secret(
                $existingUser
                    ? 'New password'
                    : 'Password',
            );

            if (! is_string($password) || strlen($password) < 8) {
                $this->error(
                    'The password must contain at least 8 characters.',
                );

                return self::FAILURE;
            }

            $passwordConfirmation = $this->secret(
                'Confirm password',
            );

            if ($password !== $passwordConfirmation) {
                $this->error('The passwords do not match.');

                return self::FAILURE;
            }

            $user = DB::transaction(
                function () use (
                    $existingUser,
                    $name,
                    $email,
                    $password,
                    $role,
                ): User {
                    $user = $existingUser ?? new User();

                    $attributes = [
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                    ];

                    $this->applyAccessAttributes(
                        $user,
                        $attributes,
                    );

                    $user->forceFill($attributes);
                    $user->save();

                    $user->syncRoles([$role]);

                    return $user->refresh();
                },
            );

            $this->newLine();
            $this->info('Staff user is ready.');

            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', (string) $user->getKey()],
                    ['Name', (string) $user->getAttribute('name')],
                    ['Email', (string) $user->getAttribute('email')],
                    ['Role', $role],
                    ['Email verified', 'Yes'],
                    ['Account active', 'Yes'],
                ],
            );

            $this->line(
                'Log in normally with the email and password you entered.',
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'The user could not be created: '.$exception->getMessage(),
            );

            return self::FAILURE;
        }
    }

    /**
     * @param array<int, string> $roles
     */
    private function resolveRole(array $roles): ?string
    {
        $requestedRole = trim(
            (string) $this->option('role'),
        );

        if ($requestedRole !== '') {
            foreach ($roles as $role) {
                if (Str::lower($role) === Str::lower($requestedRole)) {
                    return $role;
                }
            }

            $this->error(
                'The requested role does not exist: '.$requestedRole,
            );

            $this->line(
                'Available roles: '.implode(', ', $roles),
            );

            return null;
        }

        $defaultIndex = array_search(
            'System Administrator',
            $roles,
            true,
        );

        return $this->choice(
            'Staff role',
            $roles,
            $defaultIndex === false ? 0 : $defaultIndex,
        );
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function applyAccessAttributes(
        User $user,
        array &$attributes,
    ): void {
        $table = $user->getTable();

        if (Schema::hasColumn($table, 'email_verified_at')) {
            $attributes['email_verified_at'] = now();
        }

        if (Schema::hasColumn($table, 'is_verified')) {
            $attributes['is_verified'] = true;
        }

        if (Schema::hasColumn($table, 'account_type')) {
            $attributes['account_type'] = $this->enumValue(
                $user,
                'account_type',
                ['staff', 'employee'],
                'staff',
            );
        }

        if (Schema::hasColumn($table, 'account_status')) {
            $attributes['account_status'] = $this->enumValue(
                $user,
                'account_status',
                ['active', 'approved', 'enabled'],
                'active',
            );
        }

        if (Schema::hasColumn($table, 'status')) {
            $attributes['status'] = $this->enumValue(
                $user,
                'status',
                ['active', 'approved', 'enabled'],
                'active',
            );
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $attributes['is_active'] = true;
        }

        if (Schema::hasColumn($table, 'activated_at')) {
            $attributes['activated_at'] = now();
        }

        if (Schema::hasColumn($table, 'approved_at')) {
            $attributes['approved_at'] = now();
        }
    }

    /**
     * @param array<int, string> $preferredTerms
     */
    private function enumValue(
        User $user,
        string $attribute,
        array $preferredTerms,
        string $fallback,
    ): mixed {
        $cast = $user->getCasts()[$attribute] ?? null;

        if (! is_string($cast) || ! enum_exists($cast)) {
            return $fallback;
        }

        /** @var class-string<UnitEnum> $cast */
        foreach ($cast::cases() as $case) {
            $caseValue = $case instanceof BackedEnum
                ? (string) $case->value
                : $case->name;

            $searchable = Str::lower(
                $case->name.' '.$caseValue,
            );

            foreach ($preferredTerms as $term) {
                if (Str::contains($searchable, Str::lower($term))) {
                    return $case instanceof BackedEnum
                        ? $case->value
                        : $case;
                }
            }
        }

        return $fallback;
    }
}