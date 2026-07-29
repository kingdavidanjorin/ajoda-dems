<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AdminCustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->ensureAccess();

        $search = trim(
            $request->string('search')->toString(),
        );

        $customers = User::query()
            ->where(
                'account_type',
                AccountType::Customer->value,
            )
            ->when(
                $search !== '',
                fn ($query) => $query->where(
                    function ($innerQuery) use ($search): void {
                        $innerQuery
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    },
                ),
            )
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view(
            'admin.customers.index',
            compact(
                'customers',
                'search',
            ),
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