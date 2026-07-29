<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class PurchaseProgressController extends Controller
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
                'reservation',
                'allocation',
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
            ->withCount([
                'payments as pending_payments_count' => fn ($query) => $query->where(
                    'status',
                    PaymentStatus::PendingVerification->value,
                ),
            ])
            ->latest('initiated_at')
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'customer.purchases.index',
            compact('purchases'),
        );
    }
}
