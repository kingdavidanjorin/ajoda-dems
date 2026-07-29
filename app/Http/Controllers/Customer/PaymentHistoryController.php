<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = Purchase::query()
            ->where(
                'customer_id',
                $request->user()->id,
            )
            ->with([
                'house.houseType',
                'allocation',
                'payments' => fn ($query) => $query
                    ->latest('paid_at'),
            ])
            ->withSum(
                [
                    'payments as verified_payments_total' => fn ($query) => $query->where(
                        'status',
                        PaymentStatus::Verified->value,
                    ),
                ],
                'amount',
            )
            ->latest('initiated_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.payments.index',
            compact('purchases'),
        );
    }
}
