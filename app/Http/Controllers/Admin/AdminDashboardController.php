<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Estate;
use App\Models\House;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

final class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        abort_unless(
            $user !== null
                && $user->hasRole(
                    RoleName::SystemAdministrator->value,
                ),
            403,
        );

        $stats = [
            'customers' => User::query()
                ->where(
                    'account_type',
                    AccountType::Customer->value,
                )
                ->count(),

            'staff' => User::query()
                ->where(
                    'account_type',
                    AccountType::Staff->value,
                )
                ->count(),

            'estates' => Estate::query()->count(),
            'properties' => House::query()->count(),
            'roles' => Role::query()->count(),

            'audit_logs' => Schema::hasTable('audit_logs')
                ? AuditLog::query()->count()
                : 0,
        ];

        return view(
            'admin.dashboard',
            compact('stats'),
        );
    }
}