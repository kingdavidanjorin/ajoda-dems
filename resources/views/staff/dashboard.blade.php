@extends('layouts.portal')

@section('title', 'Staff Operations | Ajoda GRA')
@section('portal_name', 'Staff Operations Portal')

@section('navigation')
    <p class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
        Authorized modules
    </p>

    <nav class="space-y-2 text-sm font-semibold">
        <a
            href="{{ route('staff.dashboard') }}"
            class="block rounded-lg bg-emerald-50 px-4 py-3 text-emerald-800"
        >
            Dashboard
        </a>

        @can('reservations.manage')
            <span class="block rounded-lg px-4 py-3">
                Reservations
            </span>
        @endcan

        @can('sales.manage')
            <span class="block rounded-lg px-4 py-3">
                Sales processing
            </span>
        @endcan

        @can('payments.verify')
            <span class="block rounded-lg px-4 py-3">
                Payment verification
            </span>
        @endcan

        @can('construction.manage')
            <span class="block rounded-lg px-4 py-3">
                Construction management
            </span>
        @endcan

        @can('documents.manage')
            <span class="block rounded-lg px-4 py-3">
                Document management
            </span>
        @endcan

        @can('cases.manage')
            <span class="block rounded-lg px-4 py-3">
                Customer service cases
            </span>
        @endcan

        @can('properties.manage')
            <span class="block rounded-lg px-4 py-3">
                Property management
            </span>
        @endcan

        @can('reports.view')
            <span class="block rounded-lg px-4 py-3">
                Reports
            </span>
        @endcan
    </nav>
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
        Staff operations portal
    </p>

    <h1 class="mt-3 text-3xl font-bold">
        Welcome, {{ auth()->user()->name }}
    </h1>

    <div class="mt-8 grid gap-5 md:grid-cols-2">
        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">
                Assigned roles
            </p>

            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($roleNames as $roleName)
                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-800">
                        {{ $roleName }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">
                Authorized permissions
            </p>

            <p class="mt-3 text-3xl font-bold">
                {{ $permissionCount }}
            </p>
        </div>
    </div>
@endsection