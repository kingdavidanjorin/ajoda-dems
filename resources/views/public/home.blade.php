@extends('layouts.public')

@section('title', 'Ajoda GRA | Modern Homes in Ibadan')

@section(
    'meta_description',
    'Explore available residential properties, house types and development phases at Ajoda GRA.'
)

@section('content')
    <section class="relative overflow-hidden bg-stone-950 text-white">
        <div class="absolute inset-0">
            <div class="absolute -left-28 top-20 h-96 w-96 rounded-full bg-emerald-700/20 blur-3xl"></div>
            <div class="absolute -right-28 bottom-0 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-16 px-6 py-24 lg:grid-cols-2 lg:px-8 lg:py-32">
            <div>
                <p class="mb-5 inline-flex rounded-full border border-emerald-400/30 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-300">
                    Modern living in a planned residential community
                </p>

                <h1 class="max-w-3xl text-5xl font-bold tracking-tight sm:text-6xl">
                    Find a home built around your future.
                </h1>

                <p class="mt-7 max-w-2xl text-lg leading-8 text-stone-300">
                    Explore Ajoda GRA properties, compare house types and
                    follow your ownership journey through one secure digital
                    platform.
                </p>

                <div class="mt-10 flex flex-wrap gap-4">
                    <a
                        href="#homes"
                        class="rounded-xl bg-emerald-600 px-6 py-3.5 font-semibold text-white shadow-lg transition hover:bg-emerald-500"
                    >
                        Explore available homes
                    </a>

                    @guest
                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-xl border border-stone-600 px-6 py-3.5 font-semibold text-white transition hover:border-stone-400 hover:bg-white/5"
                            >
                                Create an account
                            </a>
                        @endif
                    @endguest
                </div>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur">
                <div class="rounded-2xl border border-white/10 bg-gradient-to-br from-emerald-700 to-emerald-950 p-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-emerald-200">
                        Ajoda GRA
                    </p>

                    <h2 class="mt-5 text-3xl font-bold">
                        One estate. One connected experience.
                    </h2>

                    <div class="mt-10 grid grid-cols-3 gap-4">
                        <div class="rounded-xl bg-black/20 p-4">
                            <p class="text-3xl font-bold">
                                {{ $availableHomes }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-100">
                                Available homes
                            </p>
                        </div>

                        <div class="rounded-xl bg-black/20 p-4">
                            <p class="text-3xl font-bold">
                                {{ $estatePhases }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-100">
                                Estate phases
                            </p>
                        </div>

                        <div class="rounded-xl bg-black/20 p-4">
                            <p class="text-3xl font-bold">
                                {{ $houseTypes }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-100">
                                House types
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        id="about"
        class="bg-white py-20"
    >
        <div class="mx-auto grid max-w-7xl gap-14 px-6 lg:grid-cols-2 lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">
                    About Ajoda GRA
                </p>

                <h2 class="mt-4 text-4xl font-bold tracking-tight text-stone-950">
                    A planned estate with a transparent property journey.
                </h2>
            </div>

            <div class="space-y-5 text-base leading-8 text-stone-600">
                <p>
                    Ajoda GRA brings property discovery, reservation,
                    payments, construction monitoring and handover into one
                    coordinated digital experience.
                </p>

                <p>
                    Customers can identify specific homes, understand their
                    availability and access their property records from a
                    secure personal workspace.
                </p>
            </div>
        </div>
    </section>

    <section
        id="homes"
        class="border-y border-stone-200 bg-stone-100 py-20"
    >
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">
                        Featured properties
                    </p>

                    <h2 class="mt-3 text-4xl font-bold tracking-tight">
                        Homes currently in the estate
                    </h2>

                    <p class="mt-4 max-w-2xl text-stone-600">
                        Review selected homes across Ajoda GRA’s active
                        development phases.
                    </p>
                </div>

                <span class="text-sm font-semibold text-stone-500">
                    {{ $featuredHouses->count() }} featured properties
                </span>
            </div>

            @if ($featuredHouses->isEmpty())
                <div class="mt-12 rounded-2xl border border-dashed border-stone-300 bg-white p-12 text-center">
                    <p class="text-lg font-semibold">
                        Featured properties will appear here shortly.
                    </p>

                    <p class="mt-2 text-sm text-stone-500">
                        The estate inventory is currently being prepared.
                    </p>
                </div>
            @else
                <div class="mt-12 grid gap-7 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($featuredHouses as $house)
                        <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                            <div class="relative flex h-56 items-end bg-gradient-to-br from-emerald-800 via-emerald-700 to-stone-900 p-6 text-white">
                                <div class="absolute right-5 top-5 rounded-full bg-white/90 px-3 py-1 text-xs font-bold text-emerald-800">
                                    {{ $house->status->label() }}
                                </div>

                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-200">
                                        {{ $house->property_code }}
                                    </p>

                                    <p class="mt-2 text-2xl font-bold">
                                        {{ $house->houseType->name }}
                                    </p>
                                </div>
                            </div>

                            <div class="p-6">
                                <div class="flex items-start justify-between gap-5">
                                    <div>
                                        <h3 class="text-xl font-bold">
                                            {{ $house->name }}
                                        </h3>

                                        <p class="mt-1 text-sm text-stone-500">
                                            {{ $house->phase?->name }}
                                            &middot;
                                            Plot {{ $house->plot_number }}
                                        </p>
                                    </div>

                                    <p class="whitespace-nowrap text-lg font-bold text-emerald-800">
                                        ₦{{ number_format((float) $house->effectivePrice()) }}
                                    </p>
                                </div>

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

                                <p class="mt-5 line-clamp-3 text-sm leading-6 text-stone-600">
                                    {{ $house->description }}
                                </p>

                                <div class="mt-6 flex items-center justify-between">
                                    <span class="text-sm font-semibold text-stone-500">
                                        {{ $house->estate->name }}
                                    </span>

                                    <span class="text-sm font-bold text-emerald-700">
                                        Details coming next
                                    </span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="bg-emerald-800 py-20 text-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-8 px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div>
                <h2 class="text-3xl font-bold">
                    Start your Ajoda GRA property journey.
                </h2>

                <p class="mt-3 max-w-2xl text-emerald-100">
                    Create an account to prepare for reservations, payments
                    and property-progress monitoring.
                </p>
            </div>

            @guest
                @if (Route::has('register'))
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex w-fit rounded-xl bg-white px-6 py-3.5 font-bold text-emerald-800 transition hover:bg-emerald-50"
                    >
                        Create customer account
                    </a>
                @endif
            @else
                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex w-fit rounded-xl bg-white px-6 py-3.5 font-bold text-emerald-800 transition hover:bg-emerald-50"
                >
                    Open my dashboard
                </a>
            @endguest
        </div>
    </section>
@endsection