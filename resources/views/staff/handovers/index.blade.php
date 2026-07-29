@extends('layouts.portal')

@section('title', 'Property Handovers | Ajoda GRA')
@section('portal_name', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'System Administration' : 'Staff Operations')
@section('page_heading', 'Property handovers')
@section('page_eyebrow', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'Administration' : 'Staff Operations')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    @if (session('handover_status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('handover_status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-3">
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-stone-950">
                Schedule handover
            </h2>

            <p class="mt-2 text-sm leading-6 text-stone-600">
                Schedule the release of a completed property to its customer.
            </p>

            <form method="POST" action="{{ route('staff.handovers.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Customer
                    </label>

                    <select name="customer_id" required class="mt-2 w-full rounded-xl border-stone-300">
                        <option value="">Select customer</option>

                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                {{ $customer->name }} â€” {{ $customer->email }}
                            </option>
                        @endforeach
                    </select>

                    @error('customer_id')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Property
                    </label>

                    <select name="house_id" required class="mt-2 w-full rounded-xl border-stone-300">
                        <option value="">Select property</option>

                        @foreach ($houses as $house)
                            <option value="{{ $house->id }}" @selected(old('house_id') == $house->id)>
                                {{ $house->property_code }}

                                @if ($house->name)
                                    â€” {{ $house->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('house_id')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Scheduled date and time
                    </label>

                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required
                        class="mt-2 w-full rounded-xl border-stone-300">

                    @error('scheduled_at')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Assign to staff member
                    </label>

                    <select name="assigned_to" class="mt-2 w-full rounded-xl border-stone-300">
                        <option value="">Not assigned</option>

                        @foreach ($staffMembers as $staffMember)
                            <option value="{{ $staffMember->id }}" @selected(old('assigned_to') == $staffMember->id)>
                                {{ $staffMember->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Customer representative
                    </label>

                    <input type="text" name="customer_representative_name"
                        value="{{ old('customer_representative_name') }}" maxlength="150"
                        class="mt-2 w-full rounded-xl border-stone-300">
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Location instructions
                    </label>

                    <textarea name="location_notes" rows="3" class="mt-2 w-full rounded-xl border-stone-300">{{ old('location_notes') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Customer notes
                    </label>

                    <textarea name="customer_notes" rows="3" class="mt-2 w-full rounded-xl border-stone-300">{{ old('customer_notes') }}</textarea>
                </div>

                <div>
                    <label class="text-sm font-semibold text-stone-800">
                        Internal staff notes
                    </label>

                    <textarea name="staff_notes" rows="3" class="mt-2 w-full rounded-xl border-stone-300">{{ old('staff_notes') }}</textarea>
                </div>

                <button type="submit"
                    class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                    Schedule handover
                </button>
            </form>
        </section>

        <section class="xl:col-span-2">
            <h2 class="text-xl font-bold text-stone-950">
                Handover records
            </h2>

            <p class="mt-2 text-sm text-stone-600">
                Upcoming and completed property handovers.
            </p>

            <div class="mt-6 space-y-5">
                @forelse ($handovers as $handover)
                    @php
                        $statusClasses = match ($handover->status) {
                            \App\Enums\HandoverStatus::Completed => 'bg-green-100 text-green-800',

                            \App\Enums\HandoverStatus::Cancelled => 'bg-red-100 text-red-800',

                            default => 'bg-blue-100 text-blue-800',
                        };
                    @endphp

                    <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-stone-950">
                                {{ $handover->house?->property_code }}
                            </h3>

                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses }}">
                                {{ $handover->status->label() }}
                            </span>

                            @if ($handover->handover_condition)
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                    {{ $handover->handover_condition->label() }}
                                </span>
                            @endif

                            @if ($handover->keys_released)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    Keys released
                                </span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-stone-500">
                            {{ $handover->reference }}
                        </p>

                        <div class="mt-5 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Customer:
                                </span>

                                {{ $handover->customer?->name }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Scheduled:
                                </span>

                                {{ $handover->scheduled_at?->format('d M Y, g:i A') }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Scheduled by:
                                </span>

                                {{ $handover->scheduledBy?->name }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Assigned to:
                                </span>

                                {{ $handover->assignedTo?->name ?? 'Not assigned' }}
                            </p>

                            @if ($handover->customer_representative_name)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Representative:
                                    </span>

                                    {{ $handover->customer_representative_name }}
                                </p>
                            @endif

                            @if ($handover->completed_at)
                                <p>
                                    <span class="font-semibold text-stone-900">
                                        Completed:
                                    </span>

                                    {{ $handover->completed_at->format('d M Y, g:i A') }}
                                </p>
                            @endif
                        </div>

                        @if ($handover->location_notes)
                            <div class="mt-4 rounded-xl bg-stone-50 p-4 text-sm leading-6 text-stone-700">
                                {{ $handover->location_notes }}
                            </div>
                        @endif

                        @if ($handover->status === \App\Enums\HandoverStatus::Scheduled)
                            <form method="POST" action="{{ route('staff.handovers.complete', $handover) }}"
                                class="mt-5 space-y-4 rounded-xl border border-stone-200 p-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="text-sm font-semibold text-stone-800">
                                        Handover condition
                                    </label>

                                    <select name="handover_condition" required
                                        class="mt-2 w-full rounded-xl border-stone-300">
                                        @foreach (\App\Enums\HandoverCondition::cases() as $condition)
                                            <option value="{{ $condition->value }}">
                                                {{ $condition->label() }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-stone-800">
                                        Handover outcome
                                    </label>

                                    <textarea name="outcome" rows="3" required class="mt-2 w-full rounded-xl border-stone-300"></textarea>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-stone-800">
                                        Completion notes
                                    </label>

                                    <textarea name="staff_notes" rows="3" class="mt-2 w-full rounded-xl border-stone-300"></textarea>
                                </div>

                                <label class="flex items-start gap-3 rounded-xl bg-stone-50 p-4">
                                    <input type="hidden" name="keys_released" value="0">

                                    <input type="checkbox" name="keys_released" value="1"
                                        class="mt-1 rounded border-stone-300 text-emerald-700">

                                    <span>
                                        <span class="block text-sm font-semibold text-stone-900">
                                            Keys and possession released
                                        </span>

                                        <span class="mt-1 block text-xs leading-5 text-stone-500">
                                            Confirm that the customer received
                                            the keys and possession of the property.
                                        </span>
                                    </span>
                                </label>

                                <button type="submit"
                                    class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">
                                    Complete handover
                                </button>
                            </form>
                        @elseif ($handover->outcome)
                            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                                <p class="text-sm font-bold text-green-900">
                                    Handover outcome
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-green-800">
                                    {{ $handover->outcome }}
                                </p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                        <h3 class="text-xl font-bold text-stone-950">
                            No handovers recorded
                        </h3>

                        <p class="mt-2 text-sm text-stone-600">
                            Scheduled property handovers will appear here.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $handovers->links() }}
            </div>
        </section>
    </div>
@endsection
