@extends('layouts.portal')

@section('title', 'Reservations | Ajoda GRA')
@section('portal_name', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'System Administration' : 'Staff Operations Portal')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        Sales operations
    </p>

    <h1 class="mt-3 text-3xl font-bold text-stone-950">
        Property reservations
    </h1>

    <p class="mt-3 text-sm leading-6 text-stone-600">
        Review customer reservation requests and approve temporary property holds.
    </p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 space-y-5">
        @forelse ($reservations as $reservation)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="grid gap-6 xl:grid-cols-[1fr_340px]">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-stone-950">
                                {{ $reservation->customer?->name }}
                            </h2>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $reservation->status->label() }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">Reference:</span>
                                {{ $reservation->reference }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">Property:</span>
                                {{ $reservation->house?->property_code }}
                                â€” {{ $reservation->house?->name }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">Customer email:</span>
                                {{ $reservation->customer?->email }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">Requested:</span>
                                {{ $reservation->created_at?->format('d M Y, g:i A') }}
                            </p>
                        </div>

                        @if ($reservation->customer_notes)
                            <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm leading-6 text-stone-700">
                                {{ $reservation->customer_notes }}
                            </div>
                        @endif
                    </div>

                    @if ($reservation->status === \App\Enums\ReservationStatus::PendingReview)
                        @can('reservations.manage')
                            <div class="space-y-4">
                                <form method="POST" action="{{ route('staff.reservations.approve', $reservation) }}"
                                    class="rounded-xl border border-green-200 bg-green-50 p-4">
                                    @csrf
                                    @method('PATCH')

                                    <label class="block text-sm font-semibold text-green-900">
                                        Approval note
                                    </label>

                                    <textarea name="review_notes" rows="3" class="mt-2 block w-full rounded-lg border-green-300"></textarea>

                                    <button type="submit"
                                        class="mt-3 w-full rounded-lg bg-green-700 px-4 py-2.5 text-sm font-bold text-white">
                                        Approve reservation
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('staff.reservations.reject', $reservation) }}"
                                    class="rounded-xl border border-red-200 bg-red-50 p-4">
                                    @csrf
                                    @method('PATCH')

                                    <label class="block text-sm font-semibold text-red-900">
                                        Rejection reason
                                    </label>

                                    <textarea name="review_notes" rows="3" required class="mt-2 block w-full rounded-lg border-red-300"></textarea>

                                    <button type="submit"
                                        class="mt-3 w-full rounded-lg bg-red-700 px-4 py-2.5 text-sm font-bold text-white">
                                        Reject reservation
                                    </button>
                                </form>
                            </div>
                        @endcan
                    @elseif ($reservation->status === \App\Enums\ReservationStatus::Approved && $reservation->purchase === null)
                        @can('sales.manage')
                            <form method="POST" action="{{ route('staff.purchases.store', $reservation) }}"
                                class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                                @csrf

                                <p class="text-sm font-bold text-blue-950">
                                    Initiate purchase
                                </p>

                                <label for="approved-price-{{ $reservation->id }}"
                                    class="mt-4 block text-sm font-semibold text-blue-900">
                                    Approved price
                                </label>

                                <input id="approved-price-{{ $reservation->id }}" type="number" name="approved_price"
                                    step="0.01" min="0.01" required
                                    value="{{ old('approved_price', $reservation->house?->effectivePrice()) }}"
                                    class="mt-2 block w-full rounded-lg border-blue-300">

                                <label for="payment-plan-{{ $reservation->id }}"
                                    class="mt-4 block text-sm font-semibold text-blue-900">
                                    Payment plan
                                </label>

                                <select id="payment-plan-{{ $reservation->id }}" name="payment_plan_type" required
                                    class="mt-2 block w-full rounded-lg border-blue-300">
                                    @foreach (\App\Enums\PaymentPlanType::cases() as $plan)
                                        <option value="{{ $plan->value }}">
                                            {{ $plan->label() }}
                                        </option>
                                    @endforeach
                                </select>

                                <label for="sales-notes-{{ $reservation->id }}"
                                    class="mt-4 block text-sm font-semibold text-blue-900">
                                    Sales note
                                </label>

                                <textarea id="sales-notes-{{ $reservation->id }}" name="sales_notes" rows="3"
                                    class="mt-2 block w-full rounded-lg border-blue-300"></textarea>

                                <button type="submit"
                                    class="mt-4 w-full rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white">
                                    Start purchase process
                                </button>
                            </form>
                        @endcan
                    @elseif ($reservation->purchase !== null)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4 text-sm">
                            <p class="font-bold text-stone-900">
                                Purchase initiated
                            </p>

                            <p class="mt-2 text-stone-600">
                                {{ $reservation->purchase->reference }}
                            </p>

                            <a href="{{ route('staff.purchases.index') }}"
                                class="mt-3 inline-flex font-semibold text-stone-900 underline">
                                View purchases
                            </a>
                        </div>
                    @elseif ($reservation->review_notes)
                        <div class="rounded-xl bg-stone-50 p-4 text-sm text-stone-700">
                            <p class="font-semibold text-stone-900">
                                Review note
                            </p>

                            <p class="mt-2">
                                {{ $reservation->review_notes }}
                            </p>

                            @if ($reservation->expires_at)
                                <p class="mt-3">
                                    Hold expires:
                                    {{ $reservation->expires_at->format('d M Y, g:i A') }}
                                </p>
                            @endif
                        </div>
                    @endif
                    @if ($reservation->status === \App\Enums\ReservationStatus::PendingReview)
                        @can('reservations.manage')
                            <div class="space-y-4">
                                <form method="POST" action="{{ route('staff.reservations.approve', $reservation) }}"
                                    class="rounded-xl border border-green-200 bg-green-50 p-4">
                                    @csrf
                                    @method('PATCH')

                                    <label class="block text-sm font-semibold text-green-900">
                                        Approval note
                                    </label>

                                    <textarea name="review_notes" rows="3" class="mt-2 block w-full rounded-lg border-green-300"></textarea>

                                    <button type="submit"
                                        class="mt-3 w-full rounded-lg bg-green-700 px-4 py-2.5 text-sm font-bold text-white">
                                        Approve reservation
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('staff.reservations.reject', $reservation) }}"
                                    class="rounded-xl border border-red-200 bg-red-50 p-4">
                                    @csrf
                                    @method('PATCH')

                                    <label class="block text-sm font-semibold text-red-900">
                                        Rejection reason
                                    </label>

                                    <textarea name="review_notes" rows="3" required class="mt-2 block w-full rounded-lg border-red-300"></textarea>

                                    <button type="submit"
                                        class="mt-3 w-full rounded-lg bg-red-700 px-4 py-2.5 text-sm font-bold text-white">
                                        Reject reservation
                                    </button>
                                </form>
                            </div>
                        @endcan
                    @elseif ($reservation->review_notes)
                        <div class="rounded-xl bg-stone-50 p-4 text-sm text-stone-700">
                            <p class="font-semibold text-stone-900">
                                Review note
                            </p>

                            <p class="mt-2">
                                {{ $reservation->review_notes }}
                            </p>

                            @if ($reservation->expires_at)
                                <p class="mt-3">
                                    Hold expires:
                                    {{ $reservation->expires_at->format('d M Y, g:i A') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center">
                <h2 class="text-xl font-bold">
                    No reservation requests
                </h2>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $reservations->links() }}
    </div>
@endsection
