<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PropertyInspection;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class InspectionHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $inspections = PropertyInspection::query()
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
            'customer.inspections.index',
            compact('inspections'),
        );
    }
}
