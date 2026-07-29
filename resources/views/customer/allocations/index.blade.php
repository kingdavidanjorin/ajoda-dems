@extends('layouts.portal')

@section('title', 'My Properties | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My properties')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        Property ownership
    </p>

    <h1 class="mt-3 text-3xl font-bold text-stone-950">
        My allocated properties
    </h1>

    <p class="mt-3 text-sm leading-6 text-stone-600">
        Properties officially allocated to your account after purchase confirmation.
    </p>

    <div class="mt-8 space-y-5">
        @forelse ($allocations as $allocation)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-6 lg:flex-row">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="text-xl font-bold text-stone-950">
                                {{ $allocation->house?->name ?? 'Allocated property' }}
                            </h2>

                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                                {{ $allocation->status->label() }}
                            </span>
                        </div>

                        <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Allocation reference:
                                </span>

                                {{ $allocation->reference }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Purchase reference:
                                </span>

                                {{ $allocation->purchase?->reference }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Property code:
                                </span>

                                {{ $allocation->house?->property_code }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    House type:
                                </span>

                                {{ $allocation->house?->houseType?->name ?? 'Not specified' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Allocated:
                                </span>

                                {{ $allocation->allocated_at?->format('d M Y, g:i A') }}
                            </p>
                        </div>

                        @if ($allocation->notes)
                            <div class="mt-5 rounded-xl bg-stone-50 p-4 text-sm text-stone-700">
                                {{ $allocation->notes }}
                            </div>
                        @endif
                    </div>

                    <div class="lg:w-64">
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                            <p class="font-bold">
                                Property allocated
                            </p>

                            <p class="mt-2 leading-6">
                                This property is officially linked to your customer account.
                            </p>
                        </div>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h2 class="text-xl font-bold text-stone-950">
                    No allocated properties
                </h2>

                <p class="mt-2 text-sm text-stone-600">
                    A property will appear here after payment verification and purchase confirmation.
                </p>

                <a href="{{ route('properties.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white">
                    Browse properties
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $allocations->links() }}
    </div>
@endsection