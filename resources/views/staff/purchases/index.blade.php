@extends('layouts.portal')

@php
    $isSystemAdministrator = auth()->user()?->hasRole(
        \App\Enums\RoleName::SystemAdministrator->value,
    ) ?? false;

    $canConfirmPurchases = auth()->user()?->can(
        'purchases.confirm',
    ) ?? false;
@endphp

@section('title', 'Purchases | Ajoda GRA')
@section('portal_name', $isSystemAdministrator ? 'System Administration' : 'Staff Operations Portal')
@section('page_heading', 'Purchases')
@section('page_eyebrow', $isSystemAdministrator ? 'Administration' : 'Sales operations')

@section('navigation')
    @include('shared.contextual-navigation', [
        'staffNavigation' => 'staff.partials.portal-navigation',
    ])
@endsection

@section('content')
    @if (session('status'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-bold">
                The purchase could not be updated.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            Sales operations
        </p>

        <h1 class="mt-3 text-3xl font-bold text-stone-950">
            Property purchases
        </h1>

        <p class="mt-3 text-sm leading-6 text-stone-600">
            Track purchase processes initiated from approved reservations.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($purchases as $purchase)
            @php
                $statusValue = $purchase->status instanceof \BackedEnum
                    ? $purchase->status->value
                    : (string) $purchase->status;

                $isConfirmed = $statusValue === 'confirmed';
            @endphp

            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-6 lg:flex-row">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-stone-950">
                                {{ $purchase->house?->name ?? 'Property purchase' }}
                            </h2>

                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold {{ $isConfirmed
                                    ? 'bg-emerald-100 text-emerald-800'
                                    : 'bg-amber-100 text-amber-800' }}"
                            >
                                {{ $purchase->status->label() }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Purchase reference:
                                </span>

                                {{ $purchase->reference }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Reservation:
                                </span>

                                {{ $purchase->reservation?->reference ?? 'Not available' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Property:
                                </span>

                                {{ $purchase->house?->property_code ?? 'Not available' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Customer:
                                </span>

                                {{ $purchase->customer?->name ?? 'Not available' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Approved price:
                                </span>

                                &#8358;{{ number_format(
                                    (float) $purchase->approved_price,
                                    2,
                                ) }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Payment plan:
                                </span>

                                {{ $purchase->payment_plan_type->label() }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Initiated by:
                                </span>

                                {{ $purchase->initiatedBy?->name ?? 'System' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Initiated:
                                </span>

                                {{ $purchase->initiated_at?->format('d M Y, g:i A') ?? 'Not available' }}
                            </p>
                        </div>

                        @if ($purchase->sales_notes)
                            <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm leading-6 text-stone-700">
                                <p class="font-semibold text-stone-900">
                                    Sales notes
                                </p>

                                <p class="mt-2">
                                    {{ $purchase->sales_notes }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <aside class="w-full lg:w-72">
                        @if ($isConfirmed)
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                                <p class="font-bold">
                                    Purchase confirmed
                                </p>

                                <p class="mt-2 leading-6">
                                    This purchase has been formally confirmed.
                                </p>
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
                                <p class="font-bold">
                                    Awaiting purchase confirmation
                                </p>

                                <p class="mt-2 leading-6">
                                    Confirm the purchase after the required payment checks have been completed.
                                </p>
                            </div>

                            @if ($canConfirmPurchases)
                                <form
                                    method="POST"
                                    action="{{ route('staff.purchases.confirm', $purchase) }}"
                                    class="mt-4"
                                    onsubmit="return confirm('Confirm this purchase and complete the sale?');"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                                    >
                                        Confirm purchase
                                    </button>
                                </form>
                            @endif
                        @endif

                        @if (
                            \Illuminate\Support\Facades\Route::has('staff.payments.index')
                            && (auth()->user()?->can('payments.view') ?? false)
                        )
                            <a
                                href="{{ route('staff.payments.index') }}"
                                class="mt-3 flex w-full items-center justify-center rounded-xl border border-stone-300 px-5 py-3 text-sm font-bold text-stone-700 transition hover:border-emerald-600 hover:text-emerald-700"
                            >
                                View payments
                            </a>
                        @endif
                    </aside>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h2 class="text-xl font-bold text-stone-950">
                    No purchases initiated
                </h2>

                <p class="mt-2 text-sm text-stone-600">
                    Approved reservations can be converted into purchases.
                </p>

                @if (
                    \Illuminate\Support\Facades\Route::has('staff.reservations.index')
                    && (auth()->user()?->can('reservations.view') ?? false)
                )
                    <a
                        href="{{ route('staff.reservations.index') }}"
                        class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white"
                    >
                        View reservations
                    </a>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $purchases->links() }}
    </div>
@endsection