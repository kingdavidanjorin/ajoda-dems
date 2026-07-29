@extends('layouts.portal')

@php
    $isSystemAdministrator = auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ?? false;
@endphp

@section('title', 'Construction Progress | Ajoda GRA')
@section('portal_name', $isSystemAdministrator ? 'System Administration' : 'Staff Operations Portal')

@section('navigation')
    @include('shared.contextual-navigation', [
        'staffNavigation' => 'staff.partials.portal-navigation',
    ])
@endsection

@section('content')
    <div>
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-stone-600">
            Project management
        </p>

        <h1 class="mt-3 text-3xl font-bold text-stone-950">
            Construction progress
        </h1>

        <p class="mt-3 text-sm leading-6 text-stone-600">
            Record and review dated construction updates for properties in the estate.
        </p>
    </div>

    @if (session('construction_status'))
        <div
            class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('construction_status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-bold">
                The construction update could not be saved.
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-8 {{ $canManageConstruction ? 'grid gap-6 xl:grid-cols-5' : 'block' }}">
        @if ($canManageConstruction)
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm xl:col-span-2">
                <h2 class="text-xl font-bold text-stone-950">
                    Record progress update
                </h2>

                <p class="mt-2 text-sm leading-6 text-stone-600">
                    Add a dated construction milestone for a property.
                </p>

                <form method="POST" action="{{ route('staff.construction.store') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="house_id" class="block text-sm font-bold text-stone-800">
                            Property
                        </label>

                        <select id="house_id" name="house_id" required
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="">
                                Select a property
                            </option>

                            @foreach ($houses as $house)
                                <option value="{{ $house->id }}" @selected((string) old('house_id') === (string) $house->id)>
                                    {{ $house->property_code }} — {{ $house->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="purchase_id" class="block text-sm font-bold text-stone-800">
                            Related purchase
                        </label>

                        <select id="purchase_id" name="purchase_id"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="">
                                No related purchase
                            </option>

                            @foreach ($purchases as $purchase)
                                <option value="{{ $purchase->id }}" @selected((string) old('purchase_id') === (string) $purchase->id)>
                                    {{ $purchase->reference }}
                                    —
                                    {{ $purchase->house?->property_code ?? 'Property unavailable' }}
                                    —
                                    {{ $purchase->customer?->name ?? 'Customer unavailable' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="property_allocation_id" class="block text-sm font-bold text-stone-800">
                            Related allocation
                        </label>

                        <select id="property_allocation_id" name="property_allocation_id"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="">
                                No related allocation
                            </option>

                            @foreach ($allocations as $allocation)
                                <option value="{{ $allocation->id }}" @selected((string) old('property_allocation_id') === (string) $allocation->id)>
                                    {{ $allocation->reference }}
                                    —
                                    {{ $allocation->house?->property_code ?? 'Property unavailable' }}
                                    —
                                    {{ $allocation->customer?->name ?? 'Customer unavailable' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="stage" class="block text-sm font-bold text-stone-800">
                            Construction stage
                        </label>

                        <select id="stage" name="stage" required
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                            <option value="">
                                Select a stage
                            </option>

                            @foreach ($constructionStages as $stage)
                                <option value="{{ $stage->value }}" @selected(old('stage') === $stage->value)>
                                    {{ $stage->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="progress_percentage" class="block text-sm font-bold text-stone-800">
                            Progress percentage
                        </label>

                        <input id="progress_percentage" name="progress_percentage" type="number" min="0"
                            max="100" required value="{{ old('progress_percentage', 0) }}"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-bold text-stone-800">
                            Update title
                        </label>

                        <input id="title" name="title" type="text" maxlength="150" required
                            value="{{ old('title') }}"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                    </div>

                    <div>
                        <label for="summary" class="block text-sm font-bold text-stone-800">
                            Progress summary
                        </label>

                        <textarea id="summary" name="summary" rows="5" required
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('summary') }}</textarea>
                    </div>

                    <div>
                        <label for="internal_notes" class="block text-sm font-bold text-stone-800">
                            Internal notes
                        </label>

                        <textarea id="internal_notes" name="internal_notes" rows="4"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('internal_notes') }}</textarea>
                    </div>

                    <div>
                        <label for="expected_completion_date" class="block text-sm font-bold text-stone-800">
                            Expected completion date
                        </label>

                        <input id="expected_completion_date" name="expected_completion_date" type="date"
                            value="{{ old('expected_completion_date') }}"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                    </div>

                    <div>
                        <label for="occurred_at" class="block text-sm font-bold text-stone-800">
                            Progress date and time
                        </label>

                        <input id="occurred_at" name="occurred_at" type="datetime-local" required
                            value="{{ old('occurred_at', now()->format('Y-m-d\TH:i')) }}"
                            class="mt-2 w-full rounded-xl border-stone-300 text-sm focus:border-emerald-600 focus:ring-emerald-600">
                    </div>

                    <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                        <input type="hidden" name="customer_visible" value="0">

                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="customer_visible" value="1" @checked(old('customer_visible'))
                                class="mt-1 rounded border-stone-300 text-emerald-700 focus:ring-emerald-600">

                            <span>
                                <span class="block text-sm font-bold text-stone-900">
                                    Visible to customer
                                </span>

                                <span class="mt-1 block text-xs leading-5 text-stone-600">
                                    Allow the related customer to see this progress update.
                                </span>
                            </span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-800">
                        Save progress update
                    </button>
                </form>
            </section>
        @endif

        <section class="{{ $canManageConstruction ? 'xl:col-span-3' : '' }}">
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div>
                    <h2 class="text-xl font-bold text-stone-950">
                        Construction updates
                    </h2>

                    <p class="mt-2 text-sm text-stone-600">
                        Most recent property progress updates appear first.
                    </p>
                </div>

                <div class="mt-6 space-y-5">
                    @forelse ($updates as $update)
                        <article class="rounded-2xl border border-stone-200 p-5">
                            <div class="flex flex-col justify-between gap-4 sm:flex-row">
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-emerald-700">
                                        {{ $update->stage->label() }}
                                    </p>

                                    <h3 class="mt-2 text-lg font-bold text-stone-950">
                                        {{ $update->title }}
                                    </h3>

                                    <p class="mt-1 text-sm text-stone-600">
                                        {{ $update->house?->property_code }}
                                        —
                                        {{ $update->house?->name }}
                                    </p>
                                </div>

                                <span
                                    class="h-fit rounded-full px-3 py-1 text-xs font-bold {{ $update->customer_visible ? 'bg-emerald-100 text-emerald-800' : 'bg-stone-100 text-stone-700' }}">
                                    {{ $update->customer_visible ? 'Customer visible' : 'Internal only' }}
                                </span>
                            </div>

                            <div class="mt-5">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="font-semibold text-stone-700">
                                        Overall progress
                                    </span>

                                    <span class="font-bold text-stone-950">
                                        {{ $update->progress_percentage }}%
                                    </span>
                                </div>

                                <div class="mt-2 h-3 overflow-hidden rounded-full bg-stone-200">
                                    <div class="h-full rounded-full bg-emerald-700"
                                        style="width: {{ $update->progress_percentage }}%;"></div>
                                </div>
                            </div>

                            <p class="mt-5 whitespace-pre-line text-sm leading-6 text-stone-700">
                                {{ $update->summary }}
                            </p>

                            @if ($update->internal_notes)
                                <div class="mt-4 rounded-xl bg-stone-50 p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.15em] text-stone-600">
                                        Internal notes
                                    </p>

                                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-stone-700">
                                        {{ $update->internal_notes }}
                                    </p>
                                </div>
                            @endif

                            <dl class="mt-5 grid gap-3 text-sm sm:grid-cols-2">
                                <div>
                                    <dt class="font-semibold text-stone-900">
                                        Reference
                                    </dt>

                                    <dd class="mt-1 text-stone-600">
                                        {{ $update->reference }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="font-semibold text-stone-900">
                                        Recorded
                                    </dt>

                                    <dd class="mt-1 text-stone-600">
                                        {{ $update->occurred_at?->format('d M Y, g:i A') }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="font-semibold text-stone-900">
                                        Recorded by
                                    </dt>

                                    <dd class="mt-1 text-stone-600">
                                        {{ $update->recordedBy?->name ?? 'System' }}
                                    </dd>
                                </div>

                                <div>
                                    <dt class="font-semibold text-stone-900">
                                        Expected completion
                                    </dt>

                                    <dd class="mt-1 text-stone-600">
                                        {{ $update->expected_completion_date?->format('d M Y') ?? 'Not specified' }}
                                    </dd>
                                </div>

                                @if ($update->purchase)
                                    <div>
                                        <dt class="font-semibold text-stone-900">
                                            Purchase
                                        </dt>

                                        <dd class="mt-1 text-stone-600">
                                            {{ $update->purchase->reference }}
                                        </dd>
                                    </div>
                                @endif

                                @if ($update->propertyAllocation)
                                    <div>
                                        <dt class="font-semibold text-stone-900">
                                            Allocation
                                        </dt>

                                        <dd class="mt-1 text-stone-600">
                                            {{ $update->propertyAllocation->reference }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-stone-300 p-10 text-center">
                            <h3 class="text-lg font-bold text-stone-950">
                                No construction updates
                            </h3>

                            <p class="mt-2 text-sm text-stone-600">
                                Construction progress entries will appear here after they are recorded.
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $updates->links() }}
                </div>
            </div>
        </section>
    </div>
@endsection
