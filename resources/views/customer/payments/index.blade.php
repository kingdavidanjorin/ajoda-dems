@extends('layouts.portal')

@section('title', 'My Payments | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My payments')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        Payment history
    </p>

    <h2 class="mt-3 text-3xl font-bold text-stone-950">
        My purchase payments
    </h2>

    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
        Review payments recorded against your property purchases and track
        the outstanding balance.
    </p>

    <div class="mt-8 space-y-6">
        @forelse ($purchases as $purchase)
            @php
                $verifiedTotal = (float) (
                    $purchase->verified_payments_total ?? 0
                );

                $approvedPrice = (float) $purchase->approved_price;

                $outstanding = max(
                    $approvedPrice - $verifiedTotal,
                    0,
                );

                $progress = $approvedPrice > 0
                    ? min(($verifiedTotal / $approvedPrice) * 100, 100)
                    : 0;
            @endphp

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-bold text-stone-950">
                                    {{ $purchase->house?->name ?? 'Property purchase' }}
                                </h3>

                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                    {{ $purchase->status->label() }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-stone-500">
                                {{ $purchase->reference }}
                            </p>
                        </div>

                        @if ($purchase->allocation)
                            <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
                                <p class="font-bold">
                                    Property allocated
                                </p>

                                <p class="mt-1 text-xs">
                                    {{ $purchase->allocation->reference }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Approved price
                            </p>

                            <p class="mt-2 text-lg font-bold text-stone-950">
                                ₦{{ number_format($approvedPrice, 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-green-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-green-700">
                                Verified payments
                            </p>

                            <p class="mt-2 text-lg font-bold text-green-900">
                                ₦{{ number_format($verifiedTotal, 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">
                                Outstanding
                            </p>

                            <p class="mt-2 text-lg font-bold text-amber-900">
                                ₦{{ number_format($outstanding, 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Payment plan
                            </p>

                            <p class="mt-2 text-lg font-bold text-stone-950">
                                {{ $purchase->payment_plan_type->label() }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-semibold text-stone-700">
                                Payment progress
                            </span>

                            <span class="font-bold text-stone-950">
                                {{ number_format($progress, 0) }}%
                            </span>
                        </div>

                        <div class="mt-2 h-3 overflow-hidden rounded-full bg-stone-200">
                            <div class="h-full rounded-full bg-emerald-700" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-stone-200 bg-stone-50 p-6">
                    <h4 class="font-bold text-stone-950">
                        Payment records
                    </h4>

                    <div class="mt-4 space-y-3">
                        @forelse ($purchase->payments as $payment)
                            <div class="rounded-xl border border-stone-200 bg-white p-4">
                                <div class="flex flex-col justify-between gap-4 sm:flex-row">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-bold text-stone-950">
                                                ₦{{ number_format((float) $payment->amount, 2) }}
                                            </p>

                                            <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-bold text-stone-700">
                                                {{ $payment->status->label() }}
                                            </span>
                                        </div>

                                        <p class="mt-2 text-sm text-stone-600">
                                            {{ $payment->payment_method->label() }}
                                        </p>

                                        <p class="mt-1 text-xs text-stone-500">
                                            Payment reference:
                                            {{ $payment->reference }}
                                        </p>

                                        @if ($payment->transaction_reference)
                                            <p class="mt-1 text-xs text-stone-500">
                                                Transaction reference:
                                                {{ $payment->transaction_reference }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="text-sm text-stone-500 sm:text-right">
                                        <p>
                                            Paid:
                                            {{ $payment->paid_at?->format('d M Y, g:i A') }}
                                        </p>

                                        @if ($payment->verified_at)
                                            <p class="mt-1">
                                                Verified:
                                                {{ $payment->verified_at->format('d M Y, g:i A') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if (
                                        $payment->status
                                        === \App\Enums\PaymentStatus::Rejected
                                        && $payment->verification_notes
                                    )
                                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                                        {{ $payment->verification_notes }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-stone-500">
                                No payment has been recorded for this purchase.
                            </p>
                        @endforelse
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No purchase payments
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Payments will appear here after a property purchase process begins.
                </p>

                <a href="{{ route('properties.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white">
                    Browse properties
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $purchases->links() }}
    </div>
@endsection