<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PropertyHandover;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class HandoverHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $handovers = PropertyHandover::query()
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->with([
                'house',
                'purchase',
                'propertyAllocation',
            ])
            ->latest('scheduled_at')
            ->paginate(15);

        return view(
            'customer.handovers.index',
            compact('handovers'),
        );
    }
}
