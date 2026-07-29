@extends('layouts.public')

@section('title', 'Interactive Estate Layout | Ajoda GRA')

@section('content')
    @php
        $selectedStatus = $selectedHouse
            ? (
                $selectedHouse->status instanceof \BackedEnum
                    ? $selectedHouse->status->value
                    : (string) $selectedHouse->status
            )
            : null;
    @endphp

    <section class="border-b border-stone-200 bg-stone-950 text-white">
        <div class="mx-auto max-w-7xl px-6 py-14 lg:px-8">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-emerald-400">
                Ajoda GRA
            </p>

            <h1 class="mt-4 text-4xl font-bold tracking-tight sm:text-5xl">
                Interactive Estate Layout
            </h1>

            <p class="mt-5 max-w-3xl text-lg leading-8 text-stone-300">
                Explore published properties by location, development phase,
                house type and current availability.
            </p>
        </div>
    </section>

    <section class="bg-stone-100">
        <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
            <form
                method="GET"
                action="{{ route('estate.layout') }}"
                class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm md:grid-cols-4"
            >
                <div>
                    <label
                        for="phase"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Development phase
                    </label>

                    <select
                        id="phase"
                        name="phase"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                        <option value="">
                            All phases
                        </option>

                        @foreach ($phases as $phase)
                            <option
                                value="{{ $phase->id }}"
                                @selected(
                                    (string) request('phase')
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
                        for="type"
                        class="mb-2 block text-sm font-semibold"
                    >
                        House type
                    </label>

                    <select
                        id="type"
                        name="type"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                        <option value="">
                            All house types
                        </option>

                        @foreach ($houseTypes as $houseType)
                            <option
                                value="{{ $houseType->id }}"
                                @selected(
                                    (string) request('type')
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
                        for="status"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Property status
                    </label>

                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                        <option value="">
                            All statuses
                        </option>

                        @foreach ($statusOptions as $status)
                            <option
                                value="{{ $status->value }}"
                                @selected(request('status') === $status->value)
                            >
                                {{ str($status->value)->headline() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white transition hover:bg-emerald-800"
                    >
                        Apply filters
                    </button>

                    <a
                        href="{{ route('estate.layout') }}"
                        class="rounded-xl border border-stone-300 px-4 py-3 font-bold transition hover:bg-stone-100"
                    >
                        Clear
                    </a>
                </div>
            </form>

            <div class="mt-7 flex flex-wrap gap-3">
                @foreach ($statusPresentation as $statusValue => $presentation)
                    <div class="flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-xs font-semibold shadow-sm">
                        <span
                            class="h-3 w-3 rounded-full {{ $presentation['class'] }}"
                        ></span>

                        <span>
                            {{ $presentation['label'] }}
                        </span>

                        <span class="text-stone-400">
                            {{ $statusCounts[$statusValue] ?? 0 }}
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="mt-7 grid gap-6 xl:grid-cols-[1fr_320px]">
                <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-stone-200 px-6 py-4">
                        <div>
                            <h2 class="font-bold">
                                Estate plan
                            </h2>

                            <p class="mt-1 text-sm text-stone-500">
                                Select a property marker to inspect it.
                            </p>
                        </div>

                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">
                            {{ $houses->count() }}
                            {{ str('property')->plural($houses->count()) }}
                        </span>
                    </div>

                    <div class="overflow-auto p-5">
                        <div
                            class="relative min-h-[620px] min-w-[820px] overflow-hidden rounded-2xl border border-stone-300 bg-emerald-50"
                            style="background-image:
                                linear-gradient(rgba(120,113,108,.12) 1px, transparent 1px),
                                linear-gradient(90deg, rgba(120,113,108,.12) 1px, transparent 1px);
                                background-size: 40px 40px;"
                        >
                            <div class="absolute inset-x-[8%] top-[46%] h-14 rounded-full bg-stone-300"></div>

                            <div class="absolute bottom-[8%] top-[8%] left-[47%] w-14 rounded-full bg-stone-300"></div>

                            <div class="absolute left-[6%] top-[7%] rounded-xl bg-white/90 px-4 py-3 shadow-sm">
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">
                                    Ajoda GRA
                                </p>

                                <p class="mt-1 text-sm text-stone-500">
                                    Conceptual estate map
                                </p>
                            </div>

                            @forelse ($houses as $house)
                                @php
                                    $statusValue = $house->status instanceof \BackedEnum
                                        ? $house->status->value
                                        : (string) $house->status;

                                    $presentation = $statusPresentation[$statusValue]
                                        ?? [
                                            'label' => str($statusValue)->headline(),
                                            'class' => 'bg-stone-600 ring-stone-300',
                                        ];

                                    $left = min(
                                        96,
                                        max(4, (float) $house->layout_x),
                                    );

                                    $top = min(
                                        94,
                                        max(6, (float) $house->layout_y),
                                    );
                                @endphp

                                <button
                                    type="button"
                                    data-layout-house
                                    data-code="{{ $house->property_code }}"
                                    data-name="{{ $house->name }}"
                                    data-status="{{ $presentation['label'] }}"
                                    data-phase="{{ $house->estatePhase?->name ?? 'Not assigned' }}"
                                    data-type="{{ $house->houseType?->name ?? 'Not assigned' }}"
                                    data-price="₦{{ number_format($house->effectivePrice(), 2) }}"
                                    data-url="{{ route('properties.show', $house) }}"
                                    title="{{ $house->property_code }} — {{ $presentation['label'] }}"
                                    class="absolute flex h-11 min-w-11 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-xl px-2 text-xs font-bold text-white shadow-lg ring-4 transition hover:z-20 hover:scale-110 focus:z-20 focus:outline-none focus:ring-4 {{ $presentation['class'] }}"
                                    style="left: {{ $left }}%; top: {{ $top }}%;"
                                >
                                    {{ $house->plot_number ?: $house->property_code }}
                                </button>
                            @empty
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center shadow-sm">
                                        <p class="font-bold">
                                            No mapped properties found
                                        </p>

                                        <p class="mt-2 text-sm text-stone-500">
                                            Clear the filters or add layout coordinates
                                            to published properties.
                                        </p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <aside class="h-fit rounded-3xl border border-stone-200 bg-white p-6 shadow-sm xl:sticky xl:top-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                        Selected property
                    </p>

                    @if ($selectedHouse)
                        <div id="layout-property-panel">
                            <h2
                                id="layout-property-name"
                                class="mt-3 text-2xl font-bold"
                            >
                                {{ $selectedHouse->name }}
                            </h2>

                            <p
                                id="layout-property-code"
                                class="mt-1 text-sm font-semibold text-stone-500"
                            >
                                {{ $selectedHouse->property_code }}
                            </p>

                            <dl class="mt-6 space-y-4 text-sm">
                                <div>
                                    <dt class="text-stone-500">
                                        Status
                                    </dt>

                                    <dd
                                        id="layout-property-status"
                                        class="mt-1 font-bold"
                                    >
                                        {{ $statusPresentation[$selectedStatus]['label'] ?? str($selectedStatus)->headline() }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-stone-500">
                                        Phase
                                    </dt>

                                    <dd
                                        id="layout-property-phase"
                                        class="mt-1 font-bold"
                                    >
                                        {{ $selectedHouse->estatePhase?->name ?? 'Not assigned' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-stone-500">
                                        House type
                                    </dt>

                                    <dd
                                        id="layout-property-type"
                                        class="mt-1 font-bold"
                                    >
                                        {{ $selectedHouse->houseType?->name ?? 'Not assigned' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-stone-500">
                                        Price
                                    </dt>

                                    <dd
                                        id="layout-property-price"
                                        class="mt-1 text-lg font-bold text-emerald-700"
                                    >
                                        ₦{{ number_format($selectedHouse->effectivePrice(), 2) }}
                                    </dd>
                                </div>
                            </dl>

                            <a
                                id="layout-property-link"
                                href="{{ route('properties.show', $selectedHouse) }}"
                                class="mt-7 flex w-full items-center justify-center rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700"
                            >
                                View property details
                            </a>
                        </div>
                    @else
                        <div class="mt-5 rounded-xl bg-stone-100 p-5 text-sm text-stone-500">
                            No property is available under the current filters.
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>

    @if ($selectedHouse)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const markers = document.querySelectorAll(
                    '[data-layout-house]',
                );

                const fields = {
                    name: document.getElementById('layout-property-name'),
                    code: document.getElementById('layout-property-code'),
                    status: document.getElementById('layout-property-status'),
                    phase: document.getElementById('layout-property-phase'),
                    type: document.getElementById('layout-property-type'),
                    price: document.getElementById('layout-property-price'),
                    link: document.getElementById('layout-property-link'),
                };

                markers.forEach((marker) => {
                    marker.addEventListener('click', () => {
                        fields.name.textContent = marker.dataset.name;
                        fields.code.textContent = marker.dataset.code;
                        fields.status.textContent = marker.dataset.status;
                        fields.phase.textContent = marker.dataset.phase;
                        fields.type.textContent = marker.dataset.type;
                        fields.price.textContent = marker.dataset.price;
                        fields.link.href = marker.dataset.url;
                    });
                });
            });
        </script>
    @endif
@endsection