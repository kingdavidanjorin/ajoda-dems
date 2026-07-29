@extends('layouts.portal')

@section('title', 'Property Handovers | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Property handovers')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Handover schedule
        </p>

        <h2 class="mt-3 text-3xl font-bold text-stone-950">
            Your property handovers
        </h2>

        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
            Review scheduled handover appointments, possession status and
            completed handover outcomes.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($handovers as $handover)
            @php
                $statusClasses = match ($handover->status) {
                    \App\Enums\HandoverStatus::Completed => 'bg-green-100 text-green-800',

                    \App\Enums\HandoverStatus::Cancelled => 'bg-red-100 text-red-800',

                    default => 'bg-blue-100 text-blue-800',
                };
            @endphp

            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-stone-950">
                                {{ $handover->house?->property_code ?? 'Property handover' }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $handover->status->label() }}
                            </span>

                            @if ($handover->handover_condition)
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                    {{ $handover->handover_condition->label() }}
                                </span>
                            @endif

                            @if ($handover->keys_released)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    Keys released
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-sm text-stone-500">
                            Reference: {{ $handover->reference }}
                        </p>

                        <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                            <div>
                                <p class="font-semibold text-stone-900">
                                    Scheduled date
                                </p>

                                <p class="mt-1">
                                    {{ $handover->scheduled_at?->format('d M Y, g:i A') }}
                                </p>
                            </div>

                            <div>
                                <p class="font-semibold text-stone-900">
                                    Property
                                </p>

                                <p class="mt-1">
                                    {{ $handover->house?->property_code ?? 'Not available' }}

                                    @if ($handover->house?->name)
                                        — {{ $handover->house->name }}
                                    @endif
                                </p>
                            </div>

                            @if ($handover->customer_representative_name)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Customer representative
                                    </p>

                                    <p class="mt-1">
                                        {{ $handover->customer_representative_name }}
                                    </p>
                                </div>
                            @endif

                            @if ($handover->completed_at)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Completed date
                                    </p>

                                    <p class="mt-1">
                                        {{ $handover->completed_at->format('d M Y, g:i A') }}
                                    </p>
                                </div>
                            @endif

                            @if ($handover->purchase)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Purchase reference
                                    </p>

                                    <p class="mt-1">
                                        {{ $handover->purchase->reference }}
                                    </p>
                                </div>
                            @endif

                            <div>
                                <p class="font-semibold text-stone-900">
                                    Possession status
                                </p>

                                <p class="mt-1">
                                    {{ $handover->keys_released ? 'Keys and possession released' : 'Not yet released' }}
                                </p>
                            </div>
                        </div>

                        @if ($handover->location_notes)
                            <div class="mt-5 rounded-xl bg-stone-50 p-4">
                                <p class="text-sm font-bold text-stone-900">
                                    Location instructions
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                    {{ $handover->location_notes }}
                                </p>
                            </div>
                        @endif

                        @if ($handover->customer_notes)
                            <div class="mt-4 rounded-xl border border-stone-200 p-4">
                                <p class="text-sm font-bold text-stone-900">
                                    Appointment notes
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                    {{ $handover->customer_notes }}
                                </p>
                            </div>
                        @endif

                        @if ($handover->status === \App\Enums\HandoverStatus::Completed && $handover->outcome)
                            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                                <p class="text-sm font-bold text-green-900">
                                    Handover outcome
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-green-800">
                                    {{ $handover->outcome }}
                                </p>
                            </div>
                        @endif
                    </div>

                    @if ($handover->house)
                        <div class="shrink-0 lg:w-48">
                            <a href="{{ route('properties.show', $handover->house) }}"
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
                    No handovers available
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Scheduled and completed property handovers will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $handovers->links() }}
    </div>
@endsection
