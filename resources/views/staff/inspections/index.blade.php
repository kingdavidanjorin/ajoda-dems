@extends('layouts.portal')

@section('title', 'Property Inspections | Ajoda GRA')
@section('portal_name', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'System Administration' : 'Staff Operations')
@section('page_heading', 'Property inspections')
@section('page_eyebrow', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'Administration' : 'Staff Operations')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    @if (session('inspection_status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('inspection_status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-3">
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-stone-950">
                Schedule inspection
            </h2>

            <p class="mt-2 text-sm leading-6 text-stone-600">
                Create an inspection appointment for a customer and property.
            </p>

            <form method="POST" action="{{ route('staff.inspections.store') }}" class="mt-6 space-y-5">
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
                        Inspection type
                    </label>

                    <select name="inspection_type" required class="mt-2 w-full rounded-xl border-stone-300">
                        @foreach (\App\Enums\InspectionType::cases() as $type)
                            <option value="{{ $type->value }}" @selected(old('inspection_type') === $type->value)>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
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
                    Schedule inspection
                </button>
            </form>
        </section>

        <section class="xl:col-span-2">
            <h2 class="text-xl font-bold text-stone-950">
                Inspection records
            </h2>

            <p class="mt-2 text-sm text-stone-600">
                Upcoming and completed property inspections.
            </p>

            <div class="mt-6 space-y-5">
                @forelse ($inspections as $inspection)
                    <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-stone-950">
                                {{ $inspection->house?->property_code }}
                            </h3>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $inspection->inspection_type->label() }}
                            </span>

                            <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                {{ $inspection->status->label() }}
                            </span>
                        </div>

                        <p class="mt-2 text-xs text-stone-500">
                            {{ $inspection->reference }}
                        </p>

                        <div class="mt-5 grid gap-3 text-sm text-stone-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold text-stone-900">
                                    Customer:
                                </span>

                                {{ $inspection->customer?->name }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Scheduled:
                                </span>

                                {{ $inspection->scheduled_at?->format('d M Y, g:i A') }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Scheduled by:
                                </span>

                                {{ $inspection->scheduledBy?->name }}
                            </p>

                            <p>
                                <span class="font-semibold text-stone-900">
                                    Assigned to:
                                </span>

                                {{ $inspection->assignedTo?->name ?? 'Not assigned' }}
                            </p>
                        </div>

                        @if ($inspection->location_notes)
                            <div class="mt-4 rounded-xl bg-stone-50 p-4 text-sm text-stone-700">
                                {{ $inspection->location_notes }}
                            </div>
                        @endif

                        @if ($inspection->status === \App\Enums\InspectionStatus::Scheduled)
                            <form method="POST" action="{{ route('staff.inspections.complete', $inspection) }}"
                                class="mt-5 space-y-4 rounded-xl border border-stone-200 p-4">
                                @csrf
                                @method('PATCH')

                                <div>
                                    <label class="text-sm font-semibold text-stone-800">
                                        Inspection outcome
                                    </label>

                                    <textarea name="outcome" rows="3" required class="mt-2 w-full rounded-xl border-stone-300"></textarea>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-stone-800">
                                        Staff notes
                                    </label>

                                    <textarea name="staff_notes" rows="3" class="mt-2 w-full rounded-xl border-stone-300"></textarea>
                                </div>

                                <button type="submit"
                                    class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">
                                    Mark inspection completed
                                </button>
                            </form>
                        @elseif ($inspection->outcome)
                            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4">
                                <p class="text-sm font-bold text-green-900">
                                    Inspection outcome
                                </p>

                                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-green-800">
                                    {{ $inspection->outcome }}
                                </p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                        <h3 class="text-xl font-bold text-stone-950">
                            No inspections recorded
                        </h3>

                        <p class="mt-2 text-sm text-stone-600">
                            Scheduled property inspections will appear here.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $inspections->links() }}
            </div>
        </section>
    </div>
@endsection
