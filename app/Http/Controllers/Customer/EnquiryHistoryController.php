<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PropertyEnquiry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class EnquiryHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $enquiries = PropertyEnquiry::query()
            ->where(
                'user_id',
                $request->user()->id,
            )
            ->with([
                'house.houseType',
                'house.estatePhase',
            ])
            ->latest()
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.enquiries.index',
            compact('enquiries'),
        );
    }
}
