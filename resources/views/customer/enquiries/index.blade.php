@extends('layouts.portal')

@section('title', 'My Enquiries | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My enquiries')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
                Enquiry history
            </p>

            <h2 class="mt-3 text-3xl font-bold text-stone-950">
                My property enquiries
            </h2>

            <p class="mt-3 max-w-3xl text-sm leading-6 text-stone-600">
                Review the property enquiries submitted from your customer
                account and track their current status.
            </p>
        </div>

        <a href="{{ route('properties.index') }}"
            class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
            Browse properties
        </a>
    </div>

    <div class="mt-8 space-y-5">
        @forelse ($enquiries as $enquiry)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 lg:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-xl font-bold text-stone-950">
                                {{ $enquiry->house?->name ?? 'Property enquiry' }}
                            </h3>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $enquiry->status->label() }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Property code:
                                </span>

                                {{ $enquiry->house?->property_code ?? 'Not available' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    House type:
                                </span>

                                {{ $enquiry->house?->houseType?->name ?? 'Not specified' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Estate phase:
                                </span>

                                {{ $enquiry->house?->estatePhase?->name ?? 'Not specified' }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Submitted:
                                </span>

                                {{ $enquiry->created_at?->format('d M Y, g:i A') }}
                            </p>
                        </div>

                        <div class="mt-5 rounded-xl bg-stone-50 p-4">
                            <p class="text-xs font-bold uppercase tracking-wide text-stone-500">
                                Your message
                            </p>

                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                {{ $enquiry->message }}
                            </p>
                        </div>

                        @if ($enquiry->responded_at)
                            <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900">
                                <p class="font-bold">
                                    Enquiry attended to
                                </p>

                                <p class="mt-1">
                                    Response recorded on
                                    {{ $enquiry->responded_at->format('d M Y, g:i A') }}.
                                </p>
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0 lg:w-52">
                        @if ($enquiry->house)
                            <a href="{{ route('properties.show', $enquiry->house) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700">
                                View property
                            </a>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No enquiries yet
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Enquiries submitted while signed into this account will
                    appear here.
                </p>

                <a href="{{ route('properties.index') }}"
                    class="mt-5 inline-flex rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white">
                    Browse properties
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $enquiries->links() }}
    </div>
@endsection