@extends('layouts.public')

@section('title', $house->name.' | Ajoda GRA')

@section(
    'meta_description',
    $house->description
        ?: 'View property information and specifications at Ajoda GRA.'
)

@section('content')
    <section class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-7xl px-6 py-6 lg:px-8">
            <nav class="flex flex-wrap gap-2 text-sm text-stone-500">
                <a
                    href="{{ route('home') }}"
                    class="hover:text-emerald-700"
                >
                    Home
                </a>

                <span>/</span>

                <a
                    href="{{ route('properties.index') }}"
                    class="hover:text-emerald-700"
                >
                    Properties
                </a>

                <span>/</span>

                <span class="text-stone-900">
                    {{ $house->name }}
                </span>
            </nav>
        </div>
    </section>

    <section class="bg-stone-950 py-16 text-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-6 lg:grid-cols-[1.3fr_0.7fr] lg:px-8">
            <div class="flex min-h-96 items-end rounded-3xl bg-gradient-to-br from-emerald-700 via-emerald-900 to-stone-950 p-8 shadow-2xl">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-200">
                        {{ $house->property_code }}
                    </p>

                    <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">
                        {{ $house->name }}
                    </h1>

                    <p class="mt-4 text-lg text-stone-300">
                        {{ $house->houseType->name }}
                    </p>
                </div>
            </div>

            <aside class="rounded-3xl border border-white/10 bg-white/5 p-8">
                <span class="inline-flex rounded-full bg-emerald-100 px-4 py-2 text-sm font-bold text-emerald-900">
                    {{ $house->status->label() }}
                </span>

                <p class="mt-7 text-sm font-semibold uppercase tracking-[0.15em] text-stone-400">
                    Property price
                </p>

                <p class="mt-2 text-4xl font-bold">
                    ₦{{ number_format((float) $house->effectivePrice()) }}
                </p>

                <div class="mt-8 space-y-4 border-t border-white/10 pt-8 text-sm">
                    <div class="flex justify-between gap-5">
                        <span class="text-stone-400">Estate</span>
                        <span class="font-semibold">{{ $house->estate->name }}</span>
                    </div>

                    <div class="flex justify-between gap-5">
                        <span class="text-stone-400">Phase</span>
                        <span class="font-semibold">
                            {{ $house->phase?->name ?? 'Not assigned' }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-5">
                        <span class="text-stone-400">Plot</span>
                        <span class="font-semibold">{{ $house->plot_number }}</span>
                    </div>

                    <div class="flex justify-between gap-5">
                        <span class="text-stone-400">House number</span>
                        <span class="font-semibold">
                            {{ $house->house_number ?? 'Not assigned' }}
                        </span>
                    </div>
                </div>

                @guest
                    <a
                        href="{{ route('register') }}"
                        class="mt-9 flex w-full justify-center rounded-xl bg-emerald-500 px-6 py-3.5 font-bold text-white transition hover:bg-emerald-400"
                    >
                        Create account to continue
                    </a>
                @else
                    <a
                        href="{{ route('dashboard') }}"
                        class="mt-9 flex w-full justify-center rounded-xl bg-emerald-500 px-6 py-3.5 font-bold text-white transition hover:bg-emerald-400"
                    >
                        Open customer dashboard
                    </a>
                @endguest

                <p class="mt-4 text-center text-xs text-stone-400">
                    Reservation functionality will be added in the sales checkpoint.
                </p>
            </aside>
        </div>
    </section>

    <section class="bg-white py-16">
        <div class="mx-auto grid max-w-7xl gap-14 px-6 lg:grid-cols-[1fr_0.7fr] lg:px-8">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-700">
                    Property overview
                </p>

                <h2 class="mt-4 text-3xl font-bold">
                    About this home
                </h2>

                <p class="mt-6 leading-8 text-stone-600">
                    {{ $house->description }}
                </p>

                @if (! empty($house->houseType->features))
                    <h3 class="mt-10 text-xl font-bold">
                        Property features
                    </h3>

                    <ul class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach ($house->houseType->features as $feature)
                            <li class="flex items-start gap-3 rounded-xl bg-stone-100 px-4 py-3 text-sm font-medium">
                                <span class="mt-0.5 text-emerald-700">✓</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div>
                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-7">
                    <h2 class="text-xl font-bold">
                        Property specifications
                    </h2>

                    <dl class="mt-6 divide-y divide-stone-200">
                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Bedrooms</dt>
                            <dd class="font-bold">{{ $house->houseType->bedrooms }}</dd>
                        </div>

                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Bathrooms</dt>
                            <dd class="font-bold">{{ $house->houseType->bathrooms }}</dd>
                        </div>

                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Toilets</dt>
                            <dd class="font-bold">{{ $house->houseType->toilets }}</dd>
                        </div>

                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Floors</dt>
                            <dd class="font-bold">{{ $house->houseType->floors }}</dd>
                        </div>

                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Plot size</dt>
                            <dd class="font-bold">
                                {{ number_format((float) $house->houseType->plot_size_sqm) }}
                                sqm
                            </dd>
                        </div>

                        <div class="flex justify-between gap-5 py-4">
                            <dt class="text-stone-500">Building size</dt>
                            <dd class="font-bold">
                                {{ number_format((float) $house->houseType->building_size_sqm) }}
                                sqm
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>

    @if ($relatedHouses->isNotEmpty())
        <section class="border-t border-stone-200 bg-stone-100 py-16">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <h2 class="text-3xl font-bold">
                    Similar properties
                </h2>

                <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($relatedHouses as $relatedHouse)
                        <a
                            href="{{ route('properties.show', $relatedHouse) }}"
                            class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                        >
                            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                                {{ $relatedHouse->property_code }}
                            </p>

                            <h3 class="mt-3 text-xl font-bold">
                                {{ $relatedHouse->name }}
                            </h3>

                            <p class="mt-2 text-sm text-stone-500">
                                {{ $relatedHouse->phase?->name }}
                                &middot;
                                {{ $relatedHouse->status->label() }}
                            </p>

                            <p class="mt-5 text-xl font-bold text-emerald-800">
                                ₦{{ number_format((float) $relatedHouse->effectivePrice()) }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection