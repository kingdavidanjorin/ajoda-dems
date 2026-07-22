<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StaffDashboardController extends Controller
{
    public function __invoke(
        Request $request,
    ): View|RedirectResponse {
        $user = $request->user();

        if (
            $user->hasRole(
                RoleName::SystemAdministrator->value,
            )
        ) {
            return redirect()->route('admin.dashboard');
        }

        return view('staff.dashboard', [
            'roleNames' => $user->getRoleNames(),
            'permissionCount' => $user
                ->getAllPermissions()
                ->count(),
        ]);
    }
}
