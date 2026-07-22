<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;
use Throwable;

final class DemsMakeAdmin extends Command
{
    protected $signature = 'dems:make-admin
        {email? : Administrator email address}
        {--name= : Administrator full name}';

    protected $description =
        'Create the initial DEMS system administrator account';

    public function handle(): int
    {
        if (
            ! Role::query()
                ->where(
                    'name',
                    RoleName::SystemAdministrator->value,
                )
                ->exists()
        ) {
            $this->error(
                'The System Administrator role does not exist. Run php artisan db:seed first.',
            );

            return self::FAILURE;
        }

        if (
            User::role(RoleName::SystemAdministrator->value)
                ->exists()
        ) {
            $this->error(
                'A System Administrator already exists. Use the administration console to manage additional administrators.',
            );

            return self::FAILURE;
        }

        $name = trim(
            (string) (
                $this->option('name')
                ?: $this->ask('Administrator full name')
            ),
        );

        $email = strtolower(
            trim(
                (string) (
                    $this->argument('email')
                    ?: $this->ask('Administrator email')
                ),
            ),
        );

        $password = (string) $this->secret(
            'Administrator password (minimum 12 characters)',
        );

        $passwordConfirmation = (string) $this->secret(
            'Confirm administrator password',
        );

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $passwordConfirmation,
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::min(12)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        if (
            ! $this->confirm(
                'Create this System Administrator account?',
                true,
            )
        ) {
            $this->warn('Administrator creation cancelled.');

            return self::SUCCESS;
        }

        try {
            $administrator = DB::transaction(
                function () use (
                    $name,
                    $email,
                    $password,
                ): User {
                    $user = User::query()->create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'account_type' => AccountType::Staff->value,
                        'account_status' => AccountStatus::Active->value,
                    ]);

                    $user->markEmailAsVerified();

                    $user->staffProfile()->create([
                        'department_id' => Department::query()
                            ->where('code', 'SYSTEM-ADMIN')
                            ->value('id'),

                        'staff_number' => $this->nextStaffNumber(),
                        'phone' => null,
                        'job_title' => 'System Administrator',
                        'employment_date' => now()->toDateString(),
                    ]);

                    $user->assignRole(
                        RoleName::SystemAdministrator->value,
                    );

                    return $user;
                },
            );
        } catch (Throwable $exception) {
            report($exception);

            $this->error(
                'The administrator account could not be created.',
            );

            return self::FAILURE;
        }

        $this->newLine();

        $this->info('System Administrator created successfully.');

        $this->table(
            ['Field', 'Value'],
            [
                ['Name', $administrator->name],
                ['Email', $administrator->email],
                ['Account type', $administrator->account_type->value],
                ['Account status', $administrator->account_status->value],
                ['Role', RoleName::SystemAdministrator->value],
            ],
        );

        $this->warn(
            'Keep the administrator credentials secure. Do not commit or share them.',
        );

        return self::SUCCESS;
    }

    private function nextStaffNumber(): string
    {
        $sequence = (
            (int) StaffProfile::query()->max('id')
        ) + 1;

        do {
            $staffNumber = sprintf(
                'ADM-%05d',
                $sequence,
            );

            $sequence++;
        } while (
            StaffProfile::query()
                ->where('staff_number', $staffNumber)
                ->exists()
        );

        return $staffNumber;
    }
}
