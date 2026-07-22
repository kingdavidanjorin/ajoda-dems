@extends('layouts.portal')

@section('title', 'System Administration | Ajoda GRA')
@section('portal_name', 'System Administration Console')

@section('navigation')
    <p class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
        Administration
    </p>

    <nav class="space-y-2 text-sm font-semibold">
        <a
            href="{{ route('admin.dashboard') }}"
            class="block rounded-lg bg-stone-900 px-4 py-3 text-white"
        >
            Dashboard
        </a>

        <span class="block rounded-lg px-4 py-3">
            Staff accounts
        </span>

        <span class="block rounded-lg px-4 py-3">
            Customers
        </span>

        <span class="block rounded-lg px-4 py-3">
            Roles and permissions
        </span>

        <span class="block rounded-lg px-4 py-3">
            Estates and properties
        </span>

        <span class="block rounded-lg px-4 py-3">
            Audit logs
        </span>

        <span class="block rounded-lg px-4 py-3">
            System settings
        </span>
    </nav>
@endsection

@section('content')
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
        System administration
    </p>

    <h1 class="mt-3 text-3xl font-bold">
        Administration Console
    </h1>

    <p class="mt-3 text-stone-600">
        Manage staff access, estate data and system configuration.
    </p>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">Customers</p>
            <p class="mt-3 text-3xl font-bold">{{ $customerCount }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">Staff accounts</p>
            <p class="mt-3 text-3xl font-bold">{{ $staffCount }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">Estates</p>
            <p class="mt-3 text-3xl font-bold">{{ $estateCount }}</p>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-6">
            <p class="text-sm text-stone-500">Properties</p>
            <p class="mt-3 text-3xl font-bold">{{ $propertyCount }}</p>
        </div>
    </div>
@endsection