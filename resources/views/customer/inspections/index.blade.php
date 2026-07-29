@extends('layouts.portal')

@section('title', 'Property Inspections | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Property inspections')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Inspection schedule
        </p>

        <h2 class="mt-3 text-3xl font-bold text-stone-950">
            Your property inspections
        </h2>

        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
            Review scheduled inspection appointments and completed inspection
            outcomes connected to your account.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($inspections as $inspection)
            @php
                $statusClasses = match ($inspection->status) {
                    \App\Enums\InspectionStatus::Completed => 'bg-green-100 text-green-800',

                    \App\Enums\InspectionStatus::Cancelled => 'bg-red-100 text-red-800',

                    \App\Enums\InspectionStatus::NoShow => 'bg-amber-100 text-amber-800',

                    default => 'bg-blue-100 text-blue-800',
                };
            @endphp

            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-stone-950">
                                {{ $inspection->house?->property_code ?? 'Property inspection' }}
                            </h3>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $inspection->inspection_type->label() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $inspection->status->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-stone-500">
                            Reference: {{ $inspection->reference }}
                        </p>

                        <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                            <div>
                                <p class="font-semibold text-stone-900">
                                    Scheduled date
                                </p>

                                <p class="mt-1">
                                    {{ $inspection->scheduled_at?->format('d M Y, g:i A') }}
                                </p>
                            </div>

                            <div>
                                <p class="font-semibold text-stone-900">
                                    Property
                                </p>

                                <p class="mt-1">
                                    {{ $inspection->house?->property_code ?? 'Not available' }}

                                    @if ($inspection->house?->name)
                                        — {{ $inspection->house->name }}
                                    @endif
                                </p>
                            </div>

                            @if ($inspection->completed_at)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Completed date
                                    </p>

                                    <p class="mt-1">
                                        {{ $inspection->completed_at->format('d M Y, g:i A') }}
                                    </p>
                                </div>
                            @endif

                            @if ($inspection->purchase)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Purchase reference
                                    </p>

                                    <p class="mt-1">
                                        {{ $inspection->purchase->reference }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        @if ($inspection->location_notes)
                            <div class="mt-5 rounded-xl bg-stone-50 p-4">
                                <p class="text-sm font-bold text-stone-900">
                                    Location instructions
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                    {{ $inspection->location_notes }}
                                </p>
                            </div>
                        @endif

                        @if ($inspection->customer_notes)
                            <div class="mt-4 rounded-xl border border-stone-200 p-4">
                                <p class="text-sm font-bold text-stone-900">
                                    Appointment notes
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                    {{ $inspection->customer_notes }}
                                </p>
                            </div>
                        @endif

                        @if ($inspection->status === \App\Enums\InspectionStatus::Completed && $inspection->outcome)
                            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                                <p class="text-sm font-bold text-green-900">
                                    Inspection outcome
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-green-800">
                                    {{ $inspection->outcome }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if ($inspection->house)
                        <div class="shrink-0 lg:w-48">
                            <a href="{{ route('properties.show', $inspection->house) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-800 transition hover:border-emerald-600 hover:text-emerald-700">
                                View property
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No inspections available
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Scheduled and completed property inspections will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $inspections->links() }}
    </div>
@endsection
