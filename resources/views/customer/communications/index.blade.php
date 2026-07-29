@extends('layouts.portal')

@section('title', 'Messages | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Messages')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@extends('layouts.portal')

@section('title', 'Messages | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Messages')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Communication history
        </p>

        <h2 class="mt-3 text-3xl font-bold text-stone-950">
            Messages from the estate team
        </h2>

        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
            Review updates, follow-ups and communication records released to
            your customer account.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($communications as $communication)
            @php
                $statusClasses = match ($communication->status) {
                    \App\Enums\CommunicationStatus::Completed => 'bg-green-100 text-green-800',

                    \App\Enums\CommunicationStatus::FollowUpPending => 'bg-amber-100 text-amber-800',

                    default => 'bg-stone-100 text-stone-700',
                };

                $directionClasses = match ($communication->direction) {
                    \App\Enums\CommunicationDirection::Incoming => 'bg-blue-100 text-blue-800',

                    \App\Enums\CommunicationDirection::Outgoing => 'bg-emerald-100 text-emerald-800',

                    default => 'bg-stone-100 text-stone-700',
                };
            @endphp

            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-stone-950">
                                {{ $communication->subject }}
                            </h3>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $communication->channel->label() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $directionClasses }}">
                                {{ $communication->direction->label() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $communication->status->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-stone-500">
                            Reference: {{ $communication->reference }}
                        </p>

                        <div class="mt-5 rounded-xl bg-stone-50 p-4">
                            <p class="whitespace-pre-line text-sm leading-6 text-stone-700">
                                {{ $communication->summary }}
                            </p>
                        </div>

                        <div class="mt-5 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Communication date:
                                </span>

                                {{ $communication->occurred_at?->format('d M Y, g:i A') }}
                            </p>

                            @if ($communication->follow_up_at)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Follow-up date:
                                    </span>

                                    {{ $communication->follow_up_at->format('d M Y, g:i A') }}
                                </p>
                            @endif

                            @if ($communication->house)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Property:
                                    </span>

                                    {{ $communication->house->property_code }}
                                </p>
                            @endif

                            @if ($communication->purchase)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Purchase:
                                    </span>

                                    {{ $communication->purchase->reference }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($communication->house)
                        <div class="shrink-0 lg:w-48">
                            <a href="{{ route('properties.show', $communication->house) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700">
                                View property
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No messages available
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Customer-visible messages and follow-up records will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $communications->links() }}
    </div>
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Communication history
        </p>

        <h2 class="mt-3 text-3xl font-bold text-stone-950">
            Messages from the estate team
        </h2>

        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
            Review updates, follow-ups and communication records released to
            your customer account.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($communications as $communication)
            @php
                $statusClasses = match ($communication->status) {
                    \App\Enums\CommunicationStatus::Completed => 'bg-green-100 text-green-800',

                    \App\Enums\CommunicationStatus::FollowUpPending => 'bg-amber-100 text-amber-800',

                    default => 'bg-stone-100 text-stone-700',
                };

                $directionClasses = match ($communication->direction) {
                    \App\Enums\CommunicationDirection::Incoming => 'bg-blue-100 text-blue-800',

                    \App\Enums\CommunicationDirection::Outgoing => 'bg-emerald-100 text-emerald-800',

                    default => 'bg-stone-100 text-stone-700',
                };
            @endphp

            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-stone-950">
                                {{ $communication->subject }}
                            </h3>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $communication->channel->label() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $directionClasses }}">
                                {{ $communication->direction->label() }}
                            </span>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $communication->status->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-stone-500">
                            Reference: {{ $communication->reference }}
                        </p>

                        <div class="mt-5 rounded-xl bg-stone-50 p-4">
                            <p class="whitespace-pre-line text-sm leading-6 text-stone-700">
                                {{ $communication->summary }}
                            </p>
                        </div>

                        <div class="mt-5 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Communication date:
                                </span>

                                {{ $communication->occurred_at?->format('d M Y, g:i A') }}
                            </p>

                            @if ($communication->follow_up_at)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Follow-up date:
                                    </span>

                                    {{ $communication->follow_up_at->format('d M Y, g:i A') }}
                                </p>
                            @endif

                            @if ($communication->house)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Property:
                                    </span>

                                    {{ $communication->house->property_code }}
                                </p>
                            @endif

                            @if ($communication->purchase)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Purchase:
                                    </span>

                                    {{ $communication->purchase->reference }}
                                </p>
                            @endif
                        </div>
                    </div>

                    @if ($communication->house)
                        <div class="shrink-0 lg:w-48">
                            <a href="{{ route('properties.show', $communication->house) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700">
                                View property
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No messages available
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Customer-visible messages and follow-up records will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $communications->links() }}
    </div>
@endsection
