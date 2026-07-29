@extends('layouts.portal')

@section('title', 'System Administration | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'Administration dashboard')
@section('page_eyebrow', 'Ajoda GRA')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            System administration
        </p>

        <h2 class="mt-2 text-3xl font-bold text-stone-950">
            Administration Control Centre
        </h2>

        <p class="mt-2 text-stone-600">
            Open every administration and operational area from this dashboard.
        </p>
    </div>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('admin.staff.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Staff accounts</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($stats['staff']) }}</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">Manage staff</p>
        </a>

        <a href="{{ route('admin.customers.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Customers</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($stats['customers']) }}</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">View customers</p>
        </a>

        <a href="{{ route('admin.estate-properties.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Estates and properties</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($stats['properties']) }}</p>
            <p class="mt-1 text-xs text-stone-500">{{ number_format($stats['estates']) }} estate(s)</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">View estate data</p>
        </a>

        <a href="{{ route('admin.access-control.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Roles and permissions</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($stats['roles']) }}</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">Review access control</p>
        </a>

        <a href="{{ route('admin.reports.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Reports</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">Open</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">View reports</p>
        </a>

        <a href="{{ route('admin.audit.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">Audit logs</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($stats['audit_logs']) }}</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">Review activity</p>
        </a>

        <a href="{{ route('admin.system-settings.index') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md">
            <p class="text-sm text-stone-500">System settings</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">View</p>
            <p class="mt-4 text-sm font-bold text-emerald-700">Open settings</p>
        </a>
    </div>

    @php
        $operations = [
            ['staff.enquiries.index', 'Property enquiries'],
            ['staff.reservations.index', 'Reservations'],
            ['staff.purchases.index', 'Purchases'],
            ['staff.payments.index', 'Payments'],
            ['staff.documents.index', 'Documents'],
            ['staff.communications.index', 'Communications'],
            ['staff.inspections.index', 'Inspections'],
            ['staff.handovers.index', 'Handovers'],
        ];
    @endphp

    <section class="mt-10 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h3 class="text-xl font-bold text-stone-950">
            Operations
        </h3>

        <p class="mt-2 text-sm text-stone-600">
            Open operational workspaces without manually entering staff URLs.
        </p>

        <div class="mt-5 flex flex-wrap gap-3">
            @foreach ($operations as [$routeName, $label])
                @if (\Illuminate\Support\Facades\Route::has($routeName))
                    <a href="{{ route($routeName) }}" class="rounded-xl border border-stone-300 px-4 py-3 text-sm font-bold text-stone-700 transition hover:border-emerald-600 hover:text-emerald-700">
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </div>
    </section>
@endsection