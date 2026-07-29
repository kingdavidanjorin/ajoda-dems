@extends('layouts.portal')

@section('title', 'My Reservations | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My reservations')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        Customer portal
    </p>

    <h1 class="mt-3 text-3xl font-bold text-stone-950">
        My reservations
    </h1>

    <p class="mt-3 text-sm leading-6 text-stone-600">
        Track your pending, approved, rejected and expired reservation requests.
    </p>

    @if (session('status'))
        <div class="mt-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="mt-8 space-y-5">
        @forelse ($reservations as $reservation)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-4 md:flex-row">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-stone-500">
                            {{ $reservation->reference }}
                        </p>

                        <h2 class="mt-2 text-xl font-bold text-stone-950">
                            {{ $reservation->house?->name }}
                        </h2>

                        <p class="mt-1 text-sm text-stone-600">
                            {{ $reservation->house?->property_code }}
                        </p>
                    </div>

                    <div>
                        <span class="inline-flex rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                            {{ $reservation->status->label() }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                    <p>
                        <span class="font-semibold text-stone-900">
                            Requested:
                        </span>

                        {{ $reservation->created_at?->format('d M Y, g:i A') }}
                    </p>

                    @if ($reservation->expires_at)
                        <p>
                            <span class="font-semibold text-stone-900">
                                Hold expires:
                            </span>

                            {{ $reservation->expires_at->format('d M Y, g:i A') }}
                        </p>
                    @endif
                </div>

                @if ($reservation->review_notes)
                    <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm text-stone-700">
                        <span class="font-semibold">Review note:</span>
                        {{ $reservation->review_notes }}
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h2 class="text-xl font-bold text-stone-950">
                    No reservation requests
                </h2>

                <p class="mt-2 text-sm text-stone-600">
                    Browse the property catalogue to submit your first request.
                </p>

                <a
                    href="{{ route('properties.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white"
                >
                    Browse properties
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $reservations->links() }}
    </div>
@endsection