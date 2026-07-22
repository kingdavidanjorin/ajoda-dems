@extends('layouts.portal')

@section('title', 'Customer Dashboard | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_eyebrow', 'Customer Portal')
@section('page_title', 'Dashboard')

@section('navigation')
    <p class="px-3 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
        Main menu
    </p>

    <nav class="mt-4 space-y-1 text-sm font-semibold">
        <a
            href="{{ route('customer.dashboard') }}"
            class="flex items-center rounded-xl bg-emerald-50 px-4 py-3 text-emerald-800"
        >
            Dashboard
        </a>

        <a
            href="{{ route('properties.index') }}"
            class="flex items-center rounded-xl px-4 py-3 text-stone-700 transition hover:bg-stone-100"
        >
            Browse properties
        </a>

        <span class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-stone-400">
            <span>My enquiries</span>

            <span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold uppercase">
                Soon
            </span>
        </span>

        <span class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-stone-400">
            <span>My reservations</span>

            <span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold uppercase">
                Soon
            </span>
        </span>

        <span class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-stone-400">
            <span>My payments</span>

            <span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold uppercase">
                Soon
            </span>
        </span>

        <span class="flex cursor-not-allowed items-center justify-between rounded-xl px-4 py-3 text-stone-400">
            <span>My documents</span>

            <span class="rounded-full bg-stone-100 px-2 py-1 text-[10px] font-bold uppercase">
                Soon
            </span>
        </span>
    </nav>
@endsection

@section('content')
    <section class="rounded-3xl bg-gradient-to-br from-emerald-800 to-emerald-950 px-7 py-8 text-white shadow-sm sm:px-9">
        <p class="text-sm font-semibold text-emerald-200">
            Welcome back
        </p>

        <h2 class="mt-2 text-3xl font-bold tracking-tight">
            {{ auth()->user()->name }}
        </h2>

        <p class="mt-3 max-w-2xl leading-7 text-emerald-100">
            Browse available properties and manage your Ajoda GRA ownership
            journey from one secure portal.
        </p>

        <div class="mt-7 flex flex-wrap gap-3">
            <a
                href="{{ route('properties.index') }}"
                class="rounded-xl bg-white px-5 py-3 text-sm font-bold text-emerald-900 transition hover:bg-emerald-50"
            >
                Browse available properties
            </a>

            <span class="rounded-xl border border-white/20 px-5 py-3 text-sm font-semibold text-emerald-100">
                Customer account active
            </span>
        </div>
    </section>

    <section class="mt-7 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-stone-500">
                        Available properties
                    </p>

                    <p class="mt-3 text-4xl font-bold">
                        {{ $availableProperties }}
                    </p>
                </div>

                <span class="rounded-xl bg-emerald-100 px-3 py-2 text-xs font-bold text-emerald-800">
                    Live
                </span>
            </div>

            <a
                href="{{ route('properties.index') }}"
                class="mt-6 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-900"
            >
                View catalogue &rarr;
            </a>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-stone-500">
                My enquiries
            </p>

            <p class="mt-3 text-4xl font-bold">
                0
            </p>

            <p class="mt-6 text-sm text-stone-500">
                Property enquiries will appear here.
            </p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-stone-500">
                My reservations
            </p>

            <p class="mt-3 text-4xl font-bold">
                0
            </p>

            <p class="mt-6 text-sm text-stone-500">
                Active and previous reservations will appear here.
            </p>
        </div>
    </section>

    <section class="mt-7 grid gap-6 xl:grid-cols-[1.4fr_0.6fr]">
        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-5">
                <div>
                    <h2 class="text-lg font-bold">
                        Property journey
                    </h2>

                    <p class="mt-1 text-sm text-stone-500">
                        Your ownership activity will be tracked here.
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-3 sm:grid-cols-4">
                @foreach ([
                    'Enquiry',
                    'Reservation',
                    'Purchase',
                    'Handover',
                ] as $step)
                    <div class="rounded-xl bg-stone-100 px-4 py-5 text-center">
                        <div class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-white text-sm font-bold text-stone-500">
                            {{ $loop->iteration }}
                        </div>

                        <p class="mt-3 text-sm font-semibold">
                            {{ $step }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-bold">
                Need assistance?
            </h2>

            <p class="mt-3 text-sm leading-6 text-stone-500">
                Customer-service messaging will be connected during the
                messaging checkpoint.
            </p>

            <span class="mt-6 inline-flex rounded-xl bg-stone-100 px-4 py-3 text-sm font-semibold text-stone-500">
                Support coming soon
            </span>
        </div>
    </section>
@endsection
