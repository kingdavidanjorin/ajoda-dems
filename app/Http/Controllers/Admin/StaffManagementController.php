<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\Auth\SessionSecurity;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Throwable;

final class StaffManagementController extends Controller
{
    public function __construct(
        private readonly SessionSecurity $sessionSecurity,
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                Rule::enum(AccountStatus::class),
            ],

            'role' => [
                'nullable',
                Rule::in(RoleName::assignableStaffValues()),
            ],
        ]);

        $staffMembers = User::query()
            ->where(
                'account_type',
                AccountType::Staff->value,
            )
            ->whereDoesntHave(
                'roles',
                fn (Builder $query): Builder => $query
                    ->where(
                        'name',
                        RoleName::SystemAdministrator->value,
                    ),
            )
            ->with([
                'staffProfile.department',
                'roles',
            ])
            ->when(
                $filters['search'] ?? null,
                function (
                    Builder $query,
                    string $search,
                ): void {
                    $query->where(
                        function (Builder $query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhereHas(
                                    'staffProfile',
                                    fn (Builder $query): Builder => $query
                                        ->where(
                                            'staff_number',
                                            'like',
                                            "%{$search}%",
                                        )
                                        ->orWhere(
                                            'job_title',
                                            'like',
                                            "%{$search}%",
                                        ),
                                );
                        },
                    );
                },
            )
            ->when(
                $filters['status'] ?? null,
                fn (
                    Builder $query,
                    string $status,
                ): Builder => $query->where(
                    'account_status',
                    $status,
                ),
            )
            ->when(
                $filters['role'] ?? null,
                fn (
                    Builder $query,
                    string $role,
                ): Builder => $query->role($role),
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.staff.index', [
            'staffMembers' => $staffMembers,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function create(): View
    {
        return view('admin.staff.create', [
            'departments' => Department::query()
                ->orderBy('name')
                ->get(),

            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(
        StoreStaffRequest $request,
    ): RedirectResponse {
        $data = $request->validated();

        try {
            $staff = DB::transaction(
                function () use ($data): User {
                    $staff = User::query()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'email_verified_at' => null,
                        'password' => Hash::make(
                            Str::random(64),
                        ),
                        'account_type' => AccountType::Staff->value,
                        'account_status' => AccountStatus::Pending->value,
                    ]);

                    $staff->staffProfile()->create([
                        'department_id' => $data['department_id'],
                        'staff_number' => $this->nextStaffNumber(),
                        'phone' => $data['phone'] ?? null,
                        'job_title' => $data['job_title'],
                        'employment_date' => $data['employment_date'] ?? null,
                    ]);

                    $staff->syncRoles($data['roles']);

                    return $staff;
                },
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors([
                    'staff' => 'The staff account could not be created.',
                ]);
        }

        $this->sendSetupNotifications($staff);

        return redirect()
            ->route('admin.staff.index')
            ->with(
                'status',
                'Staff account created in Pending status. Setup instructions were sent.',
            );
    }

    public function edit(User $staff): View
    {
        $this->ensureManageableStaff($staff);

        $staff->load([
            'staffProfile.department',
            'roles',
        ]);

        return view('admin.staff.edit', [
            'staff' => $staff,

            'departments' => Department::query()
                ->orderBy('name')
                ->get(),

            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(
        UpdateStaffRequest $request,
        User $staff,
    ): RedirectResponse {
        $this->ensureManageableStaff($staff);

        $data = $request->validated();
        $emailChanged = $staff->email !== $data['email'];

        DB::transaction(
            function () use (
                $staff,
                $data,
                $emailChanged,
            ): void {
                $staff->update([
                    'name' => $data['name'],
                    'email' => $data['email'],

                    'email_verified_at' => $emailChanged
                        ? null
                        : $staff->email_verified_at,
                ]);

                $staff->staffProfile()->updateOrCreate(
                    [
                        'user_id' => $staff->id,
                    ],
                    [
                        'department_id' => $data['department_id'],
                        'staff_number' => $staff->staffProfile?->staff_number
                            ?? $this->nextStaffNumber(),
                        'phone' => $data['phone'] ?? null,
                        'job_title' => $data['job_title'],
                        'employment_date' => $data['employment_date'] ?? null,
                    ],
                );

                $staff->syncRoles($data['roles']);
            },
        );

        if ($emailChanged) {
            $staff->refresh();

            $staff->sendEmailVerificationNotification();

            /*
     * Only pending staff still require an initial
     * password-setup link.
     */
            if (
                $staff->account_status
                === AccountStatus::Pending
            ) {
                Password::sendResetLink([
                    'email' => $staff->email,
                ]);
            }
        }

        return redirect()
            ->route('admin.staff.index')
            ->with(
                'status',
                'Staff account updated successfully.',
            );
    }

    public function activate(User $staff): RedirectResponse
    {
        $this->ensureManageableStaff($staff);

        $staff->update([
            'account_status' => AccountStatus::Active->value,
        ]);

        return back()->with(
            'status',
            "{$staff->name} now has active staff access.",
        );
    }

    public function suspend(
        User $staff,
    ): RedirectResponse {
        $this->ensureManageableStaff($staff);

        DB::transaction(
            function () use ($staff): void {
                $staff->forceFill([
                    'account_status' =>
                    AccountStatus::Suspended,
                ])->save();

                $this->sessionSecurity->revokeAll(
                    $staff,
                );
            },
        );

        return back()->with(
            'status',
            "{$staff->name}'s staff access has been suspended.",
        );
    }

    public function resendSetup(
        User $staff,
    ): RedirectResponse {
        $this->ensureManageableStaff($staff);

        abort_unless(
            $staff->account_status
                === AccountStatus::Pending,
            422,
            'Setup instructions can only be resent to pending staff accounts.',
        );

        $this->sendSetupNotifications($staff);

        return back()->with(
            'status',
            "New setup instructions were sent to {$staff->email}.",
        );
    }

    private function assignableRoles()
    {
        return Role::query()
            ->whereIn(
                'name',
                RoleName::assignableStaffValues(),
            )
            ->orderBy('name')
            ->get();
    }

    private function ensureManageableStaff(User $staff): void
    {
        abort_unless(
            $staff->isStaff()
            && ! $staff->hasRole(
                RoleName::SystemAdministrator->value,
            ),
            404,
        );
    }

    private function sendSetupNotifications(User $staff): void
    {
        $staff->sendEmailVerificationNotification();

        Password::sendResetLink([
            'email' => $staff->email,
        ]);
    }

    private function nextStaffNumber(): string
    {
        $sequence = (
            (int) StaffProfile::query()->max('id')
        ) + 1;

        do {
            $staffNumber = sprintf(
                'STF-%05d',
                $sequence,
            );

            $sequence++;
        } while (
            StaffProfile::query()
                ->where(
                    'staff_number',
                    $staffNumber,
                )
                ->exists()
        );

        return $staffNumber;
    }
}
