@extends('layouts.public')

@section('title', 'Property Catalogue | Ajoda GRA')

@section(
    'meta_description',
    'Search and compare published residential properties at Ajoda GRA.'
)

@section('content')
    <section class="bg-stone-950 py-16 text-white">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-300">
                Ajoda GRA property catalogue
            </p>

            <h1 class="mt-4 max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">
                Find the property that fits your plans.
            </h1>

            <p class="mt-5 max-w-2xl text-lg leading-8 text-stone-300">
                Search published properties by availability, phase,
                house type, bedrooms and price.
            </p>
        </div>
    </section>

    <section class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-8 lg:px-8">
            <form
                method="GET"
                action="{{ route('properties.index') }}"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                <div class="xl:col-span-2">
                    <label
                        for="search"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Search
                    </label>

                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ request('search') }}"
                        placeholder="Property name, code or plot number"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                </div>

                <div>
                    <label
                        for="status"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Property status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">
                            All statuses
                        </option>

                        @foreach ($statuses as $status)
                            <option
                                value="{{ $status->value }}"
                                @selected(request('status') === $status->value)
                            >
                                {{ $status->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="phase_id"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Estate phase
                    </label>

                    <select
                        id="phase_id"
                        name="phase_id"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">
                            All phases
                        </option>

                        @foreach ($phases as $phase)
                            <option
                                value="{{ $phase->id }}"
                                @selected(
                                    (string) request('phase_id')
                                    === (string) $phase->id
                                )
                            >
                                {{ $phase->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="house_type_id"
                        class="mb-2 block text-sm font-semibold"
                    >
                        House type
                    </label>

                    <select
                        id="house_type_id"
                        name="house_type_id"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">
                            All house types
                        </option>

                        @foreach ($houseTypes as $houseType)
                            <option
                                value="{{ $houseType->id }}"
                                @selected(
                                    (string) request('house_type_id')
                                    === (string) $houseType->id
                                )
                            >
                                {{ $houseType->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="bedrooms"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Bedrooms
                    </label>

                    <select
                        id="bedrooms"
                        name="bedrooms"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">
                            Any number
                        </option>

                        @foreach ([2, 3, 4, 5, 6] as $bedrooms)
                            <option
                                value="{{ $bedrooms }}"
                                @selected(
                                    (string) request('bedrooms')
                                    === (string) $bedrooms
                                )
                            >
                                {{ $bedrooms }} bedrooms
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="max_price"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Maximum price
                    </label>

                    <input
                        id="max_price"
                        name="max_price"
                        type="number"
                        min="0"
                        step="1000000"
                        value="{{ request('max_price') }}"
                        placeholder="e.g. 100000000"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                </div>

                <div>
                    <label
                        for="sort"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Sort properties
                    </label>

                    <select
                        id="sort"
                        name="sort"
                        class="w-full rounded-xl border border-stone-300 bg-white px-4 py-3 outline-none focus:border-emerald-600 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option
                            value="featured"
                            @selected(request('sort', 'featured') === 'featured')
                        >
                            Featured first
                        </option>

                        <option
                            value="newest"
                            @selected(request('sort') === 'newest')
                        >
                            Newest first
                        </option>

                        <option
                            value="price_asc"
                            @selected(request('sort') === 'price_asc')
                        >
                            Lowest price first
                        </option>

                        <option
                            value="price_desc"
                            @selected(request('sort') === 'price_desc')
                        >
                            Highest price first
                        </option>
                    </select>
                </div>

                <div class="flex flex-wrap items-end gap-3 xl:col-span-4">
                    <button
                        type="submit"
                        class="rounded-xl bg-emerald-700 px-6 py-3 font-bold text-white transition hover:bg-emerald-800"
                    >
                        Apply filters
                    </button>

                    <a
                        href="{{ route('properties.index') }}"
                        class="rounded-xl border border-stone-300 px-6 py-3 font-bold text-stone-700 transition hover:border-stone-500"
                    >
                        Clear filters
                    </a>
                </div>
            </form>
        </div>
    </section>

    <section class="bg-stone-100 py-16">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex flex-col gap-3 border-b border-stone-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Catalogue results
                    </p>

                    <h2 class="mt-2 text-3xl font-bold">
                        {{ number_format($houses->total()) }}
                        {{ Str::plural('property', $houses->total()) }}
                    </h2>
                </div>

                @if ($houses->total() > 0)
                    <p class="text-sm text-stone-500">
                        Showing {{ $houses->firstItem() }}
                        to {{ $houses->lastItem() }}
                    </p>
                @endif
            </div>

            @if ($houses->isEmpty())
                <div class="mt-10 rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-16 text-center">
                    <h3 class="text-xl font-bold">
                        No properties match those filters.
                    </h3>

                    <p class="mt-3 text-stone-500">
                        Clear one or more filters and search again.
                    </p>

                    <a
                        href="{{ route('properties.index') }}"
                        class="mt-6 inline-flex rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white"
                    >
                        View all properties
                    </a>
                </div>
            @else
                <div class="mt-10 grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($houses as $house)
                        <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <a
                                href="{{ route('properties.show', $house) }}"
                                class="relative flex h-56 items-end bg-gradient-to-br from-emerald-800 via-emerald-700 to-stone-950 p-6 text-white"
                            >
                                <span class="absolute right-5 top-5 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-emerald-900">
                                    {{ $house->status->label() }}
                                </span>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">
                                        {{ $house->property_code }}
                                    </p>

                                    <h3 class="mt-2 text-2xl font-bold">
                                        {{ $house->name }}
                                    </h3>
                                </div>
                            </a>

                            <div class="p-6">
                                <p class="text-sm font-semibold text-stone-500">
                                    {{ $house->houseType->name }}
                                </p>

                                <p class="mt-3 text-2xl font-bold text-emerald-800">
                                    ₦{{ number_format((float) $house->effectivePrice()) }}
                                </p>

                                <p class="mt-2 text-sm text-stone-500">
                                    {{ $house->phase?->name }}
                                    &middot;
                                    Plot {{ $house->plot_number }}
                                </p>

                                <div class="mt-6 grid grid-cols-3 gap-3 border-y border-stone-100 py-4 text-center">
                                    <div>
                                        <p class="font-bold">
                                            {{ $house->houseType->bedrooms }}
                                        </p>

                                        <p class="text-xs text-stone-500">
                                            Bedrooms
                                        </p>
                                    </div>

                                    <div>
                                        <p class="font-bold">
                                            {{ $house->houseType->bathrooms }}
                                        </p>

                                        <p class="text-xs text-stone-500">
                                            Bathrooms
                                        </p>
                                    </div>

                                    <div>
                                        <p class="font-bold">
                                            {{ number_format((float) $house->houseType->plot_size_sqm) }}
                                        </p>

                                        <p class="text-xs text-stone-500">
                                            Plot sqm
                                        </p>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('properties.show', $house) }}"
                                    class="mt-6 inline-flex font-bold text-emerald-700 hover:text-emerald-900"
                                >
                                    View property details
                                    <span aria-hidden="true">&nbsp;&rarr;</span>
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($houses->hasPages())
                    <div class="mt-12">
                        {{ $houses->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>
@endsection