@extends('layouts.portal')

@section('title', 'Customers | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'Customers')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <h2 class="text-2xl font-bold text-stone-950">
                Customer accounts
            </h2>

            <p class="mt-2 text-sm text-stone-600">
                Review registered customer accounts.
            </p>
        </div>

        <form method="GET" class="flex gap-2">
            <input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search name or email"
                class="rounded-xl border-stone-300"
            >

            <button class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white">
                Search
            </button>
        </form>
    </div>

    <div class="mt-6 overflow-x-auto rounded-2xl border border-stone-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-stone-50">
                <tr>
                    <th class="px-5 py-4">Customer</th>
                    <th class="px-5 py-4">Email</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Registered</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($customers as $customer)
                    <tr class="border-t border-stone-100">
                        <td class="px-5 py-4 font-semibold">
                            {{ $customer->name }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $customer->email }}
                        </td>

                        <td class="px-5 py-4">
                            {{ str((string) $customer->account_status?->value ?? (string) $customer->account_status)->headline() }}
                        </td>

                        <td class="px-5 py-4">
                            {{ $customer->created_at?->format('d M Y') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-stone-500">
                            No customer accounts found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $customers->links() }}
    </div>
@endsection