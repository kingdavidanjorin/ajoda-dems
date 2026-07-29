@extends('layouts.portal')

@section('title', 'Property Enquiries | Ajoda GRA')
@section('portal_name', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'System Administration' : 'Staff Operations Portal')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
                <p class="font-semibold">
                    Please correct the following:
                </p>

                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-600">
                Sales Operations
            </p>

            <h1 class="mt-2 text-3xl font-bold text-zinc-950 dark:text-white">
                Property Enquiries
            </h1>

            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                Review and manage enquiries received from prospective property buyers.
            </p>
        </div>

        <div class="space-y-5">
            @forelse ($enquiries as $enquiry)
                @php
                    $currentStatus = $enquiry->status instanceof \BackedEnum
                        ? $enquiry->status->value
                        : (string) $enquiry->status;
                @endphp

                <article class="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="grid gap-6 lg:grid-cols-3">
                        <div class="lg:col-span-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <h2 class="text-xl font-bold text-zinc-950 dark:text-white">
                                    {{ $enquiry->full_name }}
                                </h2>

                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">
                                    {{ str($currentStatus)->headline() }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 text-sm text-zinc-600 sm:grid-cols-2 dark:text-zinc-400">
                                <p>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        Email:
                                    </span>

                                    {{ $enquiry->email }}
                                </p>

                                <p>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        Phone:
                                    </span>

                                    {{ $enquiry->phone ?: 'Not provided' }}
                                </p>

                                <p>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        Property:
                                    </span>

                                    {{ $enquiry->house?->property_code }}
                                    â€” {{ $enquiry->house?->name }}
                                </p>

                                <p>
                                    <span class="font-semibold text-zinc-900 dark:text-white">
                                        Received:
                                    </span>

                                    {{ $enquiry->created_at?->format('d M Y, g:i A') }}
                                </p>
                            </div>

                            <div class="mt-5 rounded-xl bg-zinc-50 p-4 text-sm leading-6 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                {{ $enquiry->message }}
                            </div>
                        </div>

                        @can('enquiries.manage')
                            <form
                                method="POST"
                                action="{{ route('staff.enquiries.update', $enquiry) }}"
                                class="rounded-xl border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                @csrf
                                @method('PATCH')

                                <label
                                    for="status-{{ $enquiry->id }}"
                                    class="block text-sm font-semibold text-zinc-900 dark:text-white"
                                >
                                    Enquiry status
                                </label>

                                <select
                                    id="status-{{ $enquiry->id }}"
                                    name="status"
                                    class="mt-2 block w-full rounded-lg border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-900"
                                >
                                    @foreach (\App\Enums\EnquiryStatus::cases() as $status)
                                        <option
                                            value="{{ $status->value }}"
                                            @selected($currentStatus === $status->value)
                                        >
                                            {{ str($status->value)->headline() }}
                                        </option>
                                    @endforeach
                                </select>

                                <button
                                    type="submit"
                                    class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                                >
                                    Save status
                                </button>
                            </form>
                        @endcan
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-zinc-200 bg-white px-6 py-16 text-center shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-xl font-bold text-zinc-950 dark:text-white">
                        No enquiries found
                    </h2>

                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        New property enquiries will appear here.
                    </p>
                </div>
            @endforelse
        </div>

        @if (method_exists($enquiries, 'links'))
            <div class="mt-8">
                {{ $enquiries->links() }}
            </div>
        @endif
    </div>
@endsection