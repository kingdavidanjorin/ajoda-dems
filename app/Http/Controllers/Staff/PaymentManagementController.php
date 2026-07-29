<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\RecordPaymentRequest;
use App\Http\Requests\Staff\RejectPaymentRequest;
use App\Http\Requests\Staff\VerifyPaymentRequest;
use App\Models\Payment;
use App\Models\Purchase;
use App\Services\Payments\PaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PaymentManagementController extends Controller
{
    public function index(Request $request): View
    {
        $purchases = Purchase::query()
            ->with([
                'house.houseType',
                'customer',
                'payments.recordedBy',
                'payments.verifiedBy',
            ])
            ->latest()
            ->paginate(
                (int) config(
                    'dems.pagination.per_page',
                    15,
                ),
            );

        return view(
            'staff.payments.index',
            compact('purchases'),
        );
    }

    public function store(
        RecordPaymentRequest $request,
        Purchase $purchase,
        PaymentService $service,
    ): RedirectResponse {
        $payment = $service->record(
            $purchase,
            $request->user(),
            (string) $request->validated('amount'),
            PaymentMethod::from(
                $request->validated('payment_method'),
            ),
            $request->validated('paid_at'),
            $request->validated('transaction_reference'),
            $request->validated('notes'),
        );

        return redirect()
            ->route('staff.payments.index')
            ->with(
                'status',
                "Payment {$payment->reference} recorded successfully.",
            );
    }

    public function verify(
        VerifyPaymentRequest $request,
        Payment $payment,
        PaymentService $service,
    ): RedirectResponse {
        $service->verify(
            $payment,
            $request->user(),
            $request->validated('verification_notes'),
        );

        return redirect()
            ->route('staff.payments.index')
            ->with(
                'status',
                "Payment {$payment->reference} verified successfully.",
            );
    }

    public function reject(
        RejectPaymentRequest $request,
        Payment $payment,
        PaymentService $service,
    ): RedirectResponse {
        $service->reject(
            $payment,
            $request->user(),
            $request->validated('verification_notes'),
        );

        return redirect()
            ->route('staff.payments.index')
            ->with(
                'status',
                "Payment {$payment->reference} rejected.",
            );
    }
}