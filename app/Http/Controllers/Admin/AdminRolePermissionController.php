<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Spatie\Permission\Models\Role;

final class AdminRolePermissionController extends Controller
{
    public function index(): View
    {
        $this->ensureAccess();

        $roles = Role::query()
            ->with([
                'permissions' => fn ($query) =>
                    $query->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();

        return view(
            'admin.access-control.index',
            compact('roles'),
        );
    }

    private function ensureAccess(): void
    {
        abort_unless(
            auth()->user()?->hasRole(
                RoleName::SystemAdministrator->value,
            ),
            403,
        );
    }
}