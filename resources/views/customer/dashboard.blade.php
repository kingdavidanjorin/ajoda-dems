@extends('layouts.portal')

@section('title', 'Customer Dashboard | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Dashboard')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
                Account overview
            </p>

            <h2 class="mt-3 text-3xl font-bold text-stone-950">
                Welcome, {{ auth()->user()->name }}
            </h2>

            <p class="mt-3 max-w-2xl text-sm leading-6 text-stone-600">
                Track your reservations, purchases, payments and allocated
                properties from one customer workspace.
            </p>
        </div>

        <a href="{{ route('properties.index') }}"
            class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
            Browse properties
        </a>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-5">
        <a href="{{ route('customer.reservations.index') }}"
            class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                Reservations
            </p>

            <p class="mt-3 text-3xl font-bold text-stone-950">
                {{ $summary['reservations'] }}
            </p>

            <p class="mt-2 text-sm text-stone-500">
                View reservation requests
            </p>
        </a>

        <a href="{{ route('customer.purchases.index') }}"
            class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                Purchases
            </p>

            <p class="mt-3 text-3xl font-bold text-stone-950">
                {{ $summary['purchases'] }}
            </p>

            <p class="mt-2 text-sm text-stone-500">
                Track purchase progress
            </p>
        </a>

        <a href="{{ route('customer.allocations.index') }}"
            class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-emerald-300 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                Properties
            </p>

            <p class="mt-3 text-3xl font-bold text-stone-950">
                {{ $summary['allocated_properties'] }}
            </p>

            <p class="mt-2 text-sm text-stone-500">
                Allocated properties
            </p>
        </a>

        <a href="{{ route('customer.payments.index') }}"
            class="rounded-2xl border border-green-200 bg-green-50 p-5 shadow-sm transition hover:border-green-400 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-green-700">
                Verified payments
            </p>

            <p class="mt-3 text-xl font-bold text-green-900">
                ₦{{ number_format($summary['verified_total'], 2) }}
            </p>

            <p class="mt-2 text-sm text-green-700">
                Confirmed payment total
            </p>
        </a>

        <a href="{{ route('customer.payments.index') }}"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm transition hover:border-amber-400 hover:shadow-md">
            <p class="text-xs font-bold uppercase tracking-wide text-amber-700">
                Outstanding
            </p>

            <p class="mt-3 text-xl font-bold text-amber-900">
                ₦{{ number_format($summary['outstanding_total'], 2) }}
            </p>

            <p class="mt-2 text-sm text-amber-700">
                Remaining purchase balance
            </p>
        </a>
    </div>

    <div class="mt-10 grid gap-8 xl:grid-cols-2">
        <section class="rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-200 p-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-950">
                        Recent reservations
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Your latest reservation requests
                    </p>
                </div>

                <a href="{{ route('customer.reservations.index') }}"
                    class="text-sm font-bold text-emerald-700 hover:text-emerald-900">
                    View all
                </a>
            </div>

            <div class="divide-y divide-stone-200">
                @forelse ($recentReservations as $reservation)
                    <div class="p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row">
                            <div>
                                <p class="font-bold text-stone-950">
                                    {{ $reservation->house?->name ?? 'Property reservation' }}
                                </p>

                                <p class="mt-1 text-sm text-stone-500">
                                    {{ $reservation->reference }}
                                </p>

                                <p class="mt-1 text-sm text-stone-500">
                                    {{ $reservation->house?->houseType?->name ?? 'House type not specified' }}
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <span class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                    {{ $reservation->status->label() }}
                                </span>

                                <p class="mt-2 text-xs text-stone-500">
                                    {{ $reservation->created_at?->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-semibold text-stone-700">
                            No reservations yet
                        </p>

                        <p class="mt-2 text-sm text-stone-500">
                            Your property reservations will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-stone-200 p-6">
                <div>
                    <h3 class="text-lg font-bold text-stone-950">
                        Recent purchases
                    </h3>

                    <p class="mt-1 text-sm text-stone-500">
                        Current property purchase processes
                    </p>
                </div>

                <a href="{{ route('customer.purchases.index') }}"
                    class="text-sm font-bold text-emerald-700 hover:text-emerald-900">
                    View all
                </a>
            </div>

            <div class="divide-y divide-stone-200">
                @forelse ($recentPurchases as $purchase)
                    @php
                        $verifiedTotal = (float) (
                            $purchase->verified_payments_total ?? 0
                        );

                        $approvedPrice = (float) $purchase->approved_price;

                        $progress = $approvedPrice > 0
                            ? min(
                                ($verifiedTotal / $approvedPrice) * 100,
                                100,
                            )
                            : 0;
                    @endphp

                    <div class="p-6">
                        <div class="flex flex-col justify-between gap-3 sm:flex-row">
                            <div>
                                <p class="font-bold text-stone-950">
                                    {{ $purchase->house?->name ?? 'Property purchase' }}
                                </p>

                                <p class="mt-1 text-sm text-stone-500">
                                    {{ $purchase->reference }}
                                </p>
                            </div>

                            <span class="self-start rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $purchase->status->label() }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <div class="flex justify-between text-xs text-stone-600">
                                <span>Payment progress</span>

                                <span class="font-bold">
                                    {{ number_format($progress, 0) }}%
                                </span>
                            </div>

                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-stone-200">
                                <div class="h-full rounded-full bg-emerald-700" style="width: {{ $progress }}%"></div>
                            </div>
                        </div>

                        @if ($purchase->allocation)
                            <p class="mt-3 text-sm font-semibold text-green-700">
                                Property allocated:
                                {{ $purchase->allocation->reference }}
                            </p>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-semibold text-stone-700">
                            No purchases yet
                        </p>

                        <p class="mt-2 text-sm text-stone-500">
                            Approved reservations converted into purchases will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
@endsection