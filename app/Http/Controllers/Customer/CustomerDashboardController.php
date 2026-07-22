<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\House;
use Illuminate\Contracts\View\View;

final class CustomerDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('customer.dashboard', [
            'availableProperties' => House::query()
                ->published()
                ->available()
                ->count(),
        ]);
    }
}
