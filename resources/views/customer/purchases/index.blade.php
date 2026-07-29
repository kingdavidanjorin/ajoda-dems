@extends('layouts.portal')

@section('title', 'My Purchases | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My purchases')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        Purchase progress
    </p>

    <h2 class="mt-3 text-3xl font-bold text-stone-950">
        Track my property purchases
    </h2>

    <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
        Follow each property purchase from initiation through payment
        verification, confirmation and allocation.
    </p>

    <div class="mt-8 space-y-6">
        @forelse ($purchases as $purchase)
            @php
                $approvedPrice = (float) $purchase->approved_price;

                $verifiedTotal = (float) (
                    $purchase->verified_payments_total ?? 0
                );

                $outstanding = max(
                    $approvedPrice - $verifiedTotal,
                    0,
                );

                $progress = $approvedPrice > 0
                    ? min(($verifiedTotal / $approvedPrice) * 100, 100)
                    : 0;

                $statusClasses = match ($purchase->status) {
                    \App\Enums\PurchaseStatus::Confirmed =>
                        'bg-green-100 text-green-800',

                    \App\Enums\PurchaseStatus::ReadyForConfirmation =>
                        'bg-emerald-100 text-emerald-800',

                    \App\Enums\PurchaseStatus::PaymentUnderReview =>
                        'bg-blue-100 text-blue-800',

                    \App\Enums\PurchaseStatus::Cancelled =>
                        'bg-red-100 text-red-800',

                    default =>
                        'bg-amber-100 text-amber-800',
                };
            @endphp

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex flex-col justify-between gap-5 lg:flex-row">
                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h3 class="text-xl font-bold text-stone-950">
                                    {{ $purchase->house?->name ?? 'Property purchase' }}
                                </h3>

                                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                    {{ $purchase->status->label() }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-stone-500">
                                Purchase reference:
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
                                Property code
                            </p>

                            <p class="mt-2 font-bold text-stone-950">
                                {{ $purchase->house?->property_code }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                House type
                            </p>

                            <p class="mt-2 font-bold text-stone-950">
                                {{ $purchase->house?->houseType?->name ?? 'Not specified' }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Payment plan
                            </p>

                            <p class="mt-2 font-bold text-stone-950">
                                {{ $purchase->payment_plan_type->label() }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Started
                            </p>

                            <p class="mt-2 font-bold text-stone-950">
                                {{ $purchase->initiated_at?->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl border border-stone-200 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Approved price
                            </p>

                            <p class="mt-2 text-lg font-bold text-stone-950">
                                ₦{{ number_format($approvedPrice, 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-green-700">
                                Verified
                            </p>

                            <p class="mt-2 text-lg font-bold text-green-900">
                                ₦{{ number_format($verifiedTotal, 2) }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">
                                Outstanding
                            </p>

                            <p class="mt-2 text-lg font-bold text-amber-900">
                                ₦{{ number_format($outstanding, 2) }}
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
                            <div
                                class="h-full rounded-full bg-emerald-700"
                                style="width: {{ $progress }}%"
                            ></div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-stone-200 bg-stone-50 p-6">
                    <h4 class="font-bold text-stone-950">
                        Current stage
                    </h4>

                    <div class="mt-4 rounded-xl bg-white p-4 text-sm text-stone-700">
                        @switch($purchase->status)
                            @case(\App\Enums\PurchaseStatus::AwaitingPayment)
                                The purchase has started and is awaiting payment.
                                @break

                            @case(\App\Enums\PurchaseStatus::PaymentUnderReview)
                                A recorded payment is currently being reviewed by Accounts.

                                @if ($purchase->pending_payments_count > 0)
                                    {{ $purchase->pending_payments_count }}
                                    payment record(s) are pending verification.
                                @endif
                                @break

                            @case(\App\Enums\PurchaseStatus::ReadyForConfirmation)
                                The required payment has been verified. The purchase
                                is awaiting final confirmation by Sales.
                                @break

                            @case(\App\Enums\PurchaseStatus::Confirmed)
                                The purchase has been confirmed and the property
                                has been marked as sold.
                                @break

                            @case(\App\Enums\PurchaseStatus::Cancelled)
                                This purchase process has been cancelled.
                                @break
                        @endswitch
                    </div>

                    <div class="mt-5 flex flex-wrap gap-3">
                        <a
                            href="{{ route('customer.payments.index') }}"
                            class="inline-flex rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700"
                        >
                            View payment history
                        </a>

                        @if ($purchase->allocation)
                            <a
                                href="{{ route('customer.allocations.index') }}"
                                class="inline-flex rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                            >
                                View allocated property
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No purchases yet
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    A purchase will appear here after an approved reservation
                    is converted by the Sales team.
                </p>

                <a
                    href="{{ route('customer.reservations.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white"
                >
                    View reservations
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $purchases->links() }}
    </div>
@endsection
