<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isActive()) {
            return redirect()->route('access.status');
        }

        if (
            $user->isStaff()
            && $user->hasRole(RoleName::SystemAdministrator->value)
        ) {
            return redirect()->route('admin.dashboard');
        }

        if (
            $user->isStaff()
            && $user->roles()->exists()
        ) {
            return redirect()->route('staff.dashboard');
        }

        if (
            $user->isCustomer()
            && $user->hasRole(RoleName::Customer->value)
        ) {
            return redirect()->route('customer.dashboard');
        }

        return redirect()->route('access.status');
    }
}
