<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\PaymentPlanType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ConfirmPurchaseRequest;
use App\Http\Requests\Staff\InitiatePurchaseRequest;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use App\Services\Purchases\PurchaseService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PurchaseManagementController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = Purchase::query()
            ->with([
                'reservation',
                'house.houseType',
                'customer',
                'initiatedBy',
                'allocation',
            ])
            ->latest()
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'staff.purchases.index',
            compact('purchases'),
        );
    }

    public function confirm(
        ConfirmPurchaseRequest $request,
        Purchase $purchase,
        PurchaseService $service,
    ): RedirectResponse {
        $confirmedPurchase = $service->confirm(
            $purchase,
            $request->user(),
        );

        return redirect()
            ->route('staff.purchases.index')
            ->with(
                'status',
                "Purchase {$confirmedPurchase->reference} confirmed successfully. The property is now marked as sold.",
            );
    }

    public function store(
        InitiatePurchaseRequest $request,
        PropertyReservation $reservation,
        PurchaseService $service,
    ): RedirectResponse {
        $purchase = $service->initiate(
            $reservation,
            $request->user(),
            (string) $request->validated('approved_price'),
            PaymentPlanType::from(
                $request->validated('payment_plan_type'),
            ),
            $request->validated('sales_notes'),
        );

        return redirect()
            ->route('staff.purchases.index')
            ->with(
                'status',
                "Purchase {$purchase->reference} initiated successfully.",
            );
    }
}
