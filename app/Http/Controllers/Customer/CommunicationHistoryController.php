<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\CommunicationDirection;
use App\Http\Controllers\Controller;
use App\Models\CustomerCommunication;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class CommunicationHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $communications = CustomerCommunication::query()
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->customerVisible()
            ->where(
                'direction',
                '!=',
                CommunicationDirection::Internal->value,
            )
            ->with([
                'house',
                'purchase',
                'propertyEnquiry',
            ])
            ->latest('occurred_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.communications.index',
            compact('communications'),
        );
    }
}
