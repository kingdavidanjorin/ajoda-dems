@extends('layouts.portal')

@section('title', 'Customer Communications | Ajoda GRA')
@section('portal_name', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'System Administration' : 'Staff Operations')
@section('page_heading', 'Customer communications')
@section('page_eyebrow', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'Administration' : 'Staff Operations')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    @if (session('communication_status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('communication_status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-3">
        <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-stone-950">
                Record communication
            </h2>

            <p class="mt-2 text-sm leading-6 text-stone-600">
                Record a customer call, email, meeting, message or follow-up.
            </p>

            <form
                method="POST"
                action="{{ route('staff.communications.store') }}"
                class="mt-6 space-y-5"
            >
                @csrf

                <div>
                    <label class="text-sm font-semibold">
                        Customer
                    </label>

                    <select
                        name="customer_id"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >
                        <option value="">Select customer</option>

                        @foreach ($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                @selected(old('customer_id') == $customer->id)
                            >
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

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold">
                            Channel
                        </label>

                        <select
                            name="channel"
                            required
                            class="mt-2 w-full rounded-xl border-stone-300"
                        >
                            @foreach (\App\Enums\CommunicationChannel::cases() as $channel)
                                <option
                                    value="{{ $channel->value }}"
                                    @selected(old('channel') === $channel->value)
                                >
                                    {{ $channel->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-semibold">
                            Direction
                        </label>

                        <select
                            name="direction"
                            required
                            class="mt-2 w-full rounded-xl border-stone-300"
                        >
                            @foreach (\App\Enums\CommunicationDirection::cases() as $direction)
                                <option
                                    value="{{ $direction->value }}"
                                    @selected(old('direction') === $direction->value)
                                >
                                    {{ $direction->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Subject
                    </label>

                    <input
                        type="text"
                        name="subject"
                        value="{{ old('subject') }}"
                        required
                        maxlength="150"
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >

                    @error('subject')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Summary
                    </label>

                    <textarea
                        name="summary"
                        rows="5"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >{{ old('summary') }}</textarea>

                    @error('summary')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Communication date and time
                    </label>

                    <input
                        type="datetime-local"
                        name="occurred_at"
                        value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"
                        required
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Follow-up date, when required
                    </label>

                    <input
                        type="datetime-local"
                        name="follow_up_at"
                        value="{{ old('follow_up_at') }}"
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >

                    @error('follow_up_at')
                        <p class="mt-1 text-sm text-red-700">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="text-sm font-semibold">
                        Assign follow-up to
                    </label>

                    <select
                        name="assigned_to"
                        class="mt-2 w-full rounded-xl border-stone-300"
                    >
                        <option value="">Not assigned</option>

                        @foreach ($staffMembers as $staffMember)
                            <option
                                value="{{ $staffMember->id }}"
                                @selected(old('assigned_to') == $staffMember->id)
                            >
                                {{ $staffMember->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <details class="rounded-xl border border-stone-200 p-4">
                    <summary class="cursor-pointer text-sm font-semibold">
                        Link related records
                    </summary>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-semibold">
                                Property enquiry
                            </label>

                            <select
                                name="property_enquiry_id"
                                class="mt-2 w-full rounded-xl border-stone-300"
                            >
                                <option value="">No enquiry selected</option>

                                @foreach ($enquiries as $enquiry)
                                    <option
                                        value="{{ $enquiry->id }}"
                                        @selected(old('property_enquiry_id') == $enquiry->id)
                                    >
                                        {{ $enquiry->customer?->name }}
                                        â€” {{ $enquiry->house?->property_code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Purchase
                            </label>

                            <select
                                name="purchase_id"
                                class="mt-2 w-full rounded-xl border-stone-300"
                            >
                                <option value="">No purchase selected</option>

                                @foreach ($purchases as $purchase)
                                    <option
                                        value="{{ $purchase->id }}"
                                        @selected(old('purchase_id') == $purchase->id)
                                    >
                                        {{ $purchase->reference }}
                                        â€” {{ $purchase->customer?->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Property
                            </label>

                            <select
                                name="house_id"
                                class="mt-2 w-full rounded-xl border-stone-300"
                            >
                                <option value="">No property selected</option>

                                @foreach ($houses as $house)
                                    <option
                                        value="{{ $house->id }}"
                                        @selected(old('house_id') == $house->id)
                                    >
                                        {{ $house->property_code }}
                                        â€” {{ $house->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </details>

                <label class="flex items-start gap-3 rounded-xl bg-stone-50 p-4">
                    <input
                        type="hidden"
                        name="customer_visible"
                        value="0"
                    >

                    <input
                        type="checkbox"
                        name="customer_visible"
                        value="1"
                        @checked(old('customer_visible'))
                        class="mt-1 rounded border-stone-300 text-emerald-700"
                    >

                    <span>
                        <span class="block text-sm font-semibold">
                            Customer visible
                        </span>

                        <span class="mt-1 block text-xs leading-5 text-stone-500">
                            Allow the customer to view this communication in
                            their portal.
                        </span>
                    </span>
                </label>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800"
                >
                    Record communication
                </button>
            </form>
        </section>

        <section class="xl:col-span-2">
            <div>
                <h2 class="text-xl font-bold text-stone-950">
                    Communication history
                </h2>

                <p class="mt-2 text-sm text-stone-600">
                    Recorded customer interactions and pending follow-ups.
                </p>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($communications as $communication)
                    <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col justify-between gap-5 lg:flex-row">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold text-stone-950">
                                        {{ $communication->subject }}
                                    </h3>

                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                        {{ $communication->channel->label() }}
                                    </span>

                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-700">
                                        {{ $communication->status->label() }}
                                    </span>

                                    @if ($communication->customer_visible)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-800">
                                            Customer visible
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2 text-sm text-stone-500">
                                    {{ $communication->reference }}
                                </p>

                                <p class="mt-4 whitespace-pre-line text-sm leading-6 text-stone-700">
                                    {{ $communication->summary }}
                                </p>

                                <div class="mt-5 grid gap-2 text-xs text-stone-500 sm:grid-cols-2">
                                    <p>
                                        Customer:
                                        <span class="font-semibold text-stone-700">
                                            {{ $communication->customer?->name }}
                                        </span>
                                    </p>

                                    <p>
                                        Direction:
                                        <span class="font-semibold text-stone-700">
                                            {{ $communication->direction->label() }}
                                        </span>
                                    </p>

                                    <p>
                                        Recorded by:
                                        <span class="font-semibold text-stone-700">
                                            {{ $communication->recordedBy?->name }}
                                        </span>
                                    </p>

                                    <p>
                                        Occurred:
                                        <span class="font-semibold text-stone-700">
                                            {{ $communication->occurred_at?->format('d M Y, g:i A') }}
                                        </span>
                                    </p>

                                    @if ($communication->assignedTo)
                                        <p>
                                            Assigned to:
                                            <span class="font-semibold text-stone-700">
                                                {{ $communication->assignedTo->name }}
                                            </span>
                                        </p>
                                    @endif

                                    @if ($communication->follow_up_at)
                                        <p>
                                            Follow-up:
                                            <span class="font-semibold text-stone-700">
                                                {{ $communication->follow_up_at->format('d M Y, g:i A') }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if (
                                $communication->status
                                !== \App\Enums\CommunicationStatus::Completed
                            )
                                <form
                                    method="POST"
                                    action="{{ route('staff.communications.complete', $communication) }}"
                                    class="shrink-0"
                                >
                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="rounded-xl border border-green-300 bg-green-50 px-4 py-2.5 text-sm font-bold text-green-800 transition hover:bg-green-100"
                                    >
                                        Mark completed
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                        <h3 class="text-xl font-bold text-stone-950">
                            No communications recorded
                        </h3>

                        <p class="mt-2 text-sm text-stone-600">
                            Recorded calls, emails, meetings and follow-ups will
                            appear here.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $communications->links() }}
            </div>
        </section>
    </div>
@endsection