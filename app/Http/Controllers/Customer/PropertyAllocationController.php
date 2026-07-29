<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PropertyAllocation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PropertyAllocationController extends Controller
{
    public function index(Request $request): View
    {
        $allocations = PropertyAllocation::query()
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->with([
                'house.houseType',
                'purchase',
            ])
            ->latest('allocated_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.allocations.index',
            compact('allocations'),
        );
    }
}
