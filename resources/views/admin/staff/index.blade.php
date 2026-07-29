@extends('layouts.portal')

@section('title', 'Staff Accounts | Ajoda GRA')
@section('portal_name', 'System Administration Console')
@section('page_eyebrow', 'Access Management')
@section('page_heading', 'Staff Accounts')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold">
                Staff access management
            </h2>

            <p class="mt-2 text-stone-600">
                Create staff accounts, assign operational roles and control portal access.
            </p>
        </div>

        <a
            href="{{ route('admin.staff.create') }}"
            class="inline-flex w-fit rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-stone-700"
        >
            Create staff account
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('admin.staff.index') }}"
        class="mt-7 grid gap-4 rounded-2xl border border-stone-200 bg-white p-5 shadow-sm md:grid-cols-4"
    >
        <div class="md:col-span-2">
            <label
                for="search"
                class="mb-2 block text-sm font-semibold"
            >
                Search staff
            </label>

            <input
                id="search"
                type="search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Name, email, staff number or job title"
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
        </div>

        <div>
            <label
                for="status"
                class="mb-2 block text-sm font-semibold"
            >
                Account status
            </label>

            <select
                id="status"
                name="status"
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
                <option value="">
                    All statuses
                </option>

                @foreach (\App\Enums\AccountStatus::cases() as $status)
                    <option
                        value="{{ $status->value }}"
                        @selected(request('status') === $status->value)
                    >
                        {{ str($status->value)->headline() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label
                for="role"
                class="mb-2 block text-sm font-semibold"
            >
                Assigned role
            </label>

            <select
                id="role"
                name="role"
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
                <option value="">
                    All roles
                </option>

                @foreach ($roles as $role)
                    <option
                        value="{{ $role->name }}"
                        @selected(request('role') === $role->name)
                    >
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap gap-3 md:col-span-4">
            <button
                type="submit"
                class="rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800"
            >
                Apply filters
            </button>

            <a
                href="{{ route('admin.staff.index') }}"
                class="rounded-xl border border-stone-300 px-5 py-2.5 text-sm font-bold transition hover:bg-stone-100"
            >
                Clear filters
            </a>
        </div>
    </form>

    <div class="mt-7 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-5 py-4 text-left font-semibold text-stone-600">
                            Staff member
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-stone-600">
                            Department
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-stone-600">
                            Roles
                        </th>

                        <th class="px-5 py-4 text-left font-semibold text-stone-600">
                            Status
                        </th>

                        <th class="px-5 py-4 text-right font-semibold text-stone-600">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-stone-100">
                    @forelse ($staffMembers as $staff)
                        <tr class="align-top hover:bg-stone-50/70">
                            <td class="px-5 py-5">
                                <p class="font-bold">
                                    {{ $staff->name }}
                                </p>

                                <p class="mt-1 text-xs text-stone-500">
                                    {{ $staff->email }}
                                </p>

                                <p class="mt-1 text-xs font-medium text-stone-500">
                                    {{ $staff->staffProfile?->staff_number ?? 'No staff number' }}
                                </p>
                            </td>

                            <td class="px-5 py-5">
                                <p class="font-medium">
                                    {{ $staff->staffProfile?->department?->name ?? 'Unassigned' }}
                                </p>

                                <p class="mt-1 text-xs text-stone-500">
                                    {{ $staff->staffProfile?->job_title ?? 'No job title' }}
                                </p>
                            </td>

                            <td class="px-5 py-5">
                                <div class="flex max-w-sm flex-wrap gap-2">
                                    @forelse ($staff->roles as $role)
                                        <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-700">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-red-600">
                                            No role assigned
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-5 py-5">
                                <span
                                    @class([
                                        'inline-flex rounded-full px-3 py-1 text-xs font-bold',
                                        'bg-emerald-100 text-emerald-800' =>
                                            $staff->account_status ===
                                            \App\Enums\AccountStatus::Active,
                                        'bg-amber-100 text-amber-800' =>
                                            $staff->account_status ===
                                            \App\Enums\AccountStatus::Pending,
                                        'bg-red-100 text-red-800' =>
                                            $staff->account_status ===
                                            \App\Enums\AccountStatus::Suspended,
                                        'bg-stone-200 text-stone-700' =>
                                            $staff->account_status ===
                                            \App\Enums\AccountStatus::Deactivated,
                                    ])
                                >
                                    {{ str($staff->account_status->value)->headline() }}
                                </span>

                                @if (! $staff->hasVerifiedEmail())
                                    <p class="mt-2 text-xs font-medium text-amber-700">
                                        Email not verified
                                    </p>
                                @endif
                            </td>

                            <td class="px-5 py-5">
                                <div class="flex min-w-56 flex-wrap justify-end gap-2">
                                    <a
                                        href="{{ route('admin.staff.edit', $staff) }}"
                                        class="rounded-lg border border-stone-300 px-3 py-2 text-xs font-bold transition hover:bg-stone-100"
                                    >
                                        Edit
                                    </a>

                                    @if (
                                        $staff->account_status
                                        !== \App\Enums\AccountStatus::Active
                                    )
                                        <form
                                            method="POST"
                                            action="{{ route('admin.staff.activate', $staff) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-800"
                                            >
                                                Activate
                                            </button>
                                        </form>
                                    @else
                                        <form
                                            method="POST"
                                            action="{{ route('admin.staff.suspend', $staff) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg bg-red-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-red-800"
                                            >
                                                Suspend
                                            </button>
                                        </form>
                                    @endif

                                    <form
                                        method="POST"
                                        action="{{ route('admin.staff.resend-setup', $staff) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="rounded-lg border border-stone-300 px-3 py-2 text-xs font-bold transition hover:bg-stone-100"
                                        >
                                            Resend setup
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-16 text-center"
                            >
                                <p class="font-semibold text-stone-700">
                                    No staff accounts found
                                </p>

                                <p class="mt-2 text-sm text-stone-500">
                                    Create a staff account or change the current filters.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($staffMembers->hasPages())
        <div class="mt-7">
            {{ $staffMembers->links() }}
        </div>
    @endif
@endsection