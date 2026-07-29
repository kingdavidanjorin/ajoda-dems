@extends('layouts.portal')

@section('title', 'Estates and Properties | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'Estates and properties')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $label => $value)
            <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-stone-500">
                    {{ str($label)->headline() }}
                </p>

                <p class="mt-3 text-3xl font-bold text-stone-950">
                    {{ number_format($value) }}
                </p>
            </div>
        @endforeach
    </div>

    <section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-stone-950">
            Property status summary
        </h2>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($propertyStatusCounts as $status => $total)
                <div class="rounded-xl bg-stone-50 p-4">
                    <p class="text-sm text-stone-500">
                        {{ str($status)->headline() }}
                    </p>

                    <p class="mt-2 text-2xl font-bold">
                        {{ number_format($total) }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-stone-500">
                    No property status information is available.
                </p>
            @endforelse
        </div>
    </section>

    <section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-stone-950">
            Recent properties
        </h2>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200">
                        <th class="px-3 py-3">Property code</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Created</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($recentProperties as $property)
                        <tr class="border-b border-stone-100">
                            <td class="px-3 py-3 font-semibold">
                                {{ $property->property_code ?? 'Property '.$property->getKey() }}
                            </td>

                            <td class="px-3 py-3">
                                {{ str((string) ($property->status?->value ?? $property->status))->headline() }}
                            </td>

                            <td class="px-3 py-3">
                                {{ $property->created_at?->format('d M Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-8 text-center text-stone-500">
                                No properties found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection