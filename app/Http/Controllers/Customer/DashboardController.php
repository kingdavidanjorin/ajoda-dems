<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Enums\AllocationStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PropertyAllocation;
use App\Models\PropertyReservation;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $customerId = $request->user()->id;

        $purchases = Purchase::query()
            ->where('customer_id', $customerId)
            ->with([
                'house.houseType',
                'allocation',
            ])
            ->withSum(
                [
                    'payments as verified_payments_total' =>
                        fn($query) => $query->where(
                            'status',
                            PaymentStatus::Verified->value,
                        ),
                ],
                'amount',
            )
            ->latest('initiated_at')
            ->get();

        $verifiedTotal = (float) Payment::query()
            ->where('customer_id', $customerId)
            ->where(
                'status',
                PaymentStatus::Verified->value,
            )
            ->sum('amount');

        $outstandingTotal = $purchases->sum(
            function (Purchase $purchase): float {
                $approvedPrice = (float) $purchase->approved_price;

                $verifiedPayments = (float) (
                    $purchase->verified_payments_total ?? 0
                );

                return max(
                    $approvedPrice - $verifiedPayments,
                    0,
                );
            },
        );

        $summary = [
            'reservations' => PropertyReservation::query()
                ->where('customer_id', $customerId)
                ->count(),

            'purchases' => $purchases->count(),

            'allocated_properties' => PropertyAllocation::query()
                ->where('customer_id', $customerId)
                ->where(
                    'status',
                    AllocationStatus::Active->value,
                )
                ->count(),

            'verified_total' => $verifiedTotal,

            'outstanding_total' => $outstandingTotal,
        ];

        $recentReservations = PropertyReservation::query()
            ->where('customer_id', $customerId)
            ->with([
                'house.houseType',
            ])
            ->latest()
            ->limit(3)
            ->get();

        $recentPurchases = $purchases->take(3);

        return view(
            'customer.dashboard',
            compact(
                'summary',
                'recentReservations',
                'recentPurchases',
            ),
        );
    }
}