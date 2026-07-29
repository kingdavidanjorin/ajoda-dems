@extends('layouts.portal')

@section('title', 'Construction Progress | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Construction progress')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
            Property development
        </p>

        <h2 class="mt-3 text-3xl font-bold text-stone-950">
            Your construction updates
        </h2>

        <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
            Review construction milestones and progress updates published for
            properties connected to your account.
        </p>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($updates as $update)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-xl font-bold text-stone-950">
                                {{ $update->title }}
                            </h3>

                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                {{ $update->stage->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-sm text-stone-500">
                            Reference: {{ $update->reference }}
                        </p>

                        <div class="mt-5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-stone-700">
                                    Construction progress
                                </span>

                                <span class="font-bold text-stone-950">
                                    {{ $update->progress_percentage }}%
                                </span>
                            </div>

                            <div class="mt-2 h-3 overflow-hidden rounded-full bg-stone-200">
                                <div
                                    class="h-full rounded-full bg-emerald-700"
                                    style="width: {{ $update->progress_percentage }}%;"
                                ></div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 text-sm text-stone-600 sm:grid-cols-2">
                            <div>
                                <p class="font-semibold text-stone-900">
                                    Property
                                </p>

                                <p class="mt-1">
                                    {{ $update->house?->property_code ?? 'Not available' }}

                                    @if ($update->house?->name)
                                        &mdash; {{ $update->house->name }}
                                    @endif
                                </p>
                            </div>

                            <div>
                                <p class="font-semibold text-stone-900">
                                    Update date
                                </p>

                                <p class="mt-1">
                                    {{ $update->occurred_at?->format('d M Y, g:i A') }}
                                </p>
                            </div>

                            @if ($update->expected_completion_date)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Expected completion
                                    </p>

                                    <p class="mt-1">
                                        {{ $update->expected_completion_date->format('d M Y') }}
                                    </p>
                                </div>
                            @endif

                            @if ($update->purchase)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Purchase reference
                                    </p>

                                    <p class="mt-1">
                                        {{ $update->purchase->reference }}
                                    </p>
                                </div>
                            @endif

                            @if ($update->propertyAllocation)
                                <div>
                                    <p class="font-semibold text-stone-900">
                                        Allocation reference
                                    </p>

                                    <p class="mt-1">
                                        {{ $update->propertyAllocation->reference }}
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div class="mt-5 rounded-xl bg-stone-50 p-5">
                            <p class="text-sm font-bold text-stone-900">
                                Progress summary
                            </p>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                {{ $update->summary }}
                            </p>
                        </div>
                    </div>

                    @if ($update->house)
                        <div class="shrink-0 lg:w-48">
                            <a
                                href="{{ route('properties.show', $update->house) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold text-stone-800 transition hover:border-emerald-600 hover:text-emerald-700"
                            >
                                View property
                            </a>
                        </div>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No construction updates available
                </h3>

                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Customer-visible construction updates linked to your
                    property purchase will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $updates->links() }}
    </div>
@endsection