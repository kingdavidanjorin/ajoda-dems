@extends('layouts.portal')

@section('title', 'System Reports | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'System reports')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <h2 class="text-2xl font-bold text-stone-950">Operational summary</h2>
            <p class="mt-2 text-sm text-stone-600">
                Current estate, sales, payment and customer-service statistics.
            </p>
        </div>

        <a
            href="{{ route('admin.reports.export') }}"
            class="rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white"
        >
            Export CSV
        </a>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($summary as $section => $metrics)
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">
                    {{ str($section)->headline() }}
                </h3>

                <div class="mt-5 space-y-3">
                    @forelse ($metrics as $metric => $value)
                        <div class="flex items-center justify-between gap-4 border-b border-stone-100 pb-3">
                            <span class="text-sm text-stone-600">
                                {{ str((string) $metric)->headline() }}
                            </span>

                            <span class="font-bold text-stone-950">
                                @if (str_contains((string) $metric, 'amount'))
                                    Ã¢â€šÂ¦{{ number_format((float) $value, 2) }}
                                @else
                                    {{ number_format((float) $value) }}
                                @endif
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500">No records available.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-lg font-bold text-stone-950">Recent audit activity</h3>
            <a href="{{ route('admin.audit.index') }}" class="text-sm font-bold text-emerald-700">
                View all
            </a>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-stone-200">
                        <th class="px-3 py-3">Date</th>
                        <th class="px-3 py-3">Actor</th>
                        <th class="px-3 py-3">Event</th>
                        <th class="px-3 py-3">Record</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentAudits as $audit)
                        <tr class="border-b border-stone-100">
                            <td class="px-3 py-3">{{ $audit->created_at?->format('d M Y, g:i A') }}</td>
                            <td class="px-3 py-3">{{ $audit->actor?->name ?? 'System' }}</td>
                            <td class="px-3 py-3">{{ str($audit->event)->headline() }}</td>
                            <td class="px-3 py-3">
                                {{ class_basename((string) $audit->auditable_type) }}
                                {{ $audit->auditable_id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-8 text-center text-stone-500">
                                No audit activity recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection