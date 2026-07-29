@extends('layouts.portal')

@section('title', 'Notifications | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'Notifications')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    @if (session('notification_status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('notification_status') }}
        </div>
    @endif

    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
                Notification centre
            </p>

            <h2 class="mt-3 text-3xl font-bold text-stone-950">
                Account updates
            </h2>

            <p class="mt-3 text-sm text-stone-600">
                You currently have
                <span class="font-bold text-stone-900">
                    {{ $unreadCount }}
                </span>
                unread {{ Str::plural('notification', $unreadCount) }}.
            </p>
        </div>

        @if ($unreadCount > 0)
            <form method="POST" action="{{ route('customer.notifications.read-all') }}">
                @csrf
                @method('PATCH')

                <button type="submit"
                    class="rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-bold text-stone-800 transition hover:border-emerald-600 hover:text-emerald-700">
                    Mark all as read
                </button>
            </form>
        @endif
    </div>

    <div class="mt-8 space-y-4">
        @forelse ($notifications as $notification)
            @php
                $isUnread = $notification->read_at === null;
                $data = $notification->data;
                $url = $notification->getAttribute('destination_url');
            @endphp

            <article
                class="rounded-2xl border bg-white p-6 shadow-sm
                    {{ $isUnread ? 'border-emerald-300 ring-1 ring-emerald-100' : 'border-stone-200' }}">
                <div class="flex flex-col justify-between gap-5 sm:flex-row">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-bold text-stone-950">
                                {{ $data['title'] ?? 'Account notification' }}
                            </h3>

                            @if ($isUnread)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">
                                    New
                                </span>
                            @else
                                <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold text-stone-600">
                                    Read
                                </span>
                            @endif
                        </div>

                        <p class="mt-3 text-sm leading-6 text-stone-700">
                            {{ $data['message'] ?? 'A new account update is available.' }}
                        </p>

                        @if (!empty($data['reference']))
                            <p class="mt-3 text-xs font-semibold text-stone-500">
                                Reference: {{ $data['reference'] }}
                            </p>
                        @endif

                        <p class="mt-2 text-xs text-stone-500">
                            {{ $notification->created_at?->format('d M Y, g:i A') }}
                        </p>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-start gap-3">
                        @if (is_string($url) && $url !== '')
                            <a href="{{ $url }}"
                                class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-800">
                                View details
                            </a>
                        @endif

                        @if ($isUnread)
                            <form method="POST" action="{{ route('customer.notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')

                                <button type="submit"
                                    class="rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-bold text-stone-700 transition hover:border-emerald-600 hover:text-emerald-700">
                                    Mark as read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-xl font-bold text-stone-950">
                    No notifications
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Inspection, handover and other account updates will appear
                    here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $notifications->links() }}
    </div>
@endsection
