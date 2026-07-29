@extends('layouts.portal')

@section('title', 'Audit Logs | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'Audit logs')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <form method="GET" class="grid gap-4 rounded-2xl border border-stone-200 bg-white p-5 md:grid-cols-4">
        <input type="text" name="event" value="{{ request('event') }}" placeholder="Event" class="rounded-xl border-stone-300">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border-stone-300">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border-stone-300">
        <button class="rounded-xl bg-emerald-700 px-4 py-3 font-bold text-white">Filter</button>
    </form>

    <div class="mt-6 space-y-4">
        @forelse ($auditLogs as $audit)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-stone-950">{{ str($audit->event)->headline() }}</h3>
                        <p class="mt-1 text-sm text-stone-500">
                            {{ $audit->actor?->name ?? 'System' }}
                            Ã‚Â· {{ $audit->created_at?->format('d M Y, g:i A') }}
                        </p>
                    </div>

                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">
                        {{ class_basename((string) $audit->auditable_type) }}
                        {{ $audit->auditable_id }}
                    </span>
                </div>

                <div class="mt-5 grid gap-4 lg:grid-cols-2">
                    <div>
                        <p class="text-sm font-bold">Previous values</p>
                        <pre class="mt-2 overflow-x-auto rounded-xl bg-stone-950 p-4 text-xs text-white">{{ json_encode($audit->old_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    <div>
                        <p class="text-sm font-bold">New values</p>
                        <pre class="mt-2 overflow-x-auto rounded-xl bg-stone-950 p-4 text-xs text-white">{{ json_encode($audit->new_values, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>

                <p class="mt-4 text-xs text-stone-500">
                    {{ $audit->request_method }} Ã‚Â· {{ $audit->route_name }} Ã‚Â· {{ $audit->ip_address }}
                </p>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center">
                No audit logs found.
            </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $auditLogs->links() }}</div>
@endsection