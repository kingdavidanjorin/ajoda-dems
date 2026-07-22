<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\AccountType;
use App\Http\Controllers\Controller;
use App\Models\Estate;
use App\Models\House;
use App\Models\User;
use Illuminate\Contracts\View\View;

final class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'customerCount' => User::query()
                ->where(
                    'account_type',
                    AccountType::Customer->value,
                )
                ->count(),

            'staffCount' => User::query()
                ->where(
                    'account_type',
                    AccountType::Staff->value,
                )
                ->count(),

            'estateCount' => Estate::query()->count(),
            'propertyCount' => House::query()->count(),
        ]);
    }
}
