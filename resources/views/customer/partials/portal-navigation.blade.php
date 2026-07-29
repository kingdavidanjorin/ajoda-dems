<p class="px-4 text-xs font-bold uppercase tracking-[0.18em] text-stone-400">
    Main menu
</p>

<div class="mt-4 space-y-2">
    <a href="{{ route('customer.dashboard') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.dashboard')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Dashboard
    </a>

    <a href="{{ route('properties.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('properties.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Browse properties
    </a>

    <a href="{{ route('customer.payments.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.payments.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My payments
    </a>

    <a href="{{ route('customer.reservations.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.reservations.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My reservations
    </a>

    <a href="{{ route('customer.purchases.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.purchases.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My purchases
    </a>

    <a href="{{ route('customer.allocations.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.allocations.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My properties
    </a>

    <a href="{{ route('customer.construction.index') }}"
        class="block rounded-xl px-4 py-3 transition
        {{ request()->routeIs('customer.construction.*')
            ? 'bg-emerald-50 font-semibold text-emerald-800'
            : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Construction progress
    </a>

    <a href="{{ route('customer.enquiries.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.enquiries.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My enquiries
    </a>

    <a href="{{ route('customer.handovers.index') }}"
        class="block rounded-xl px-4 py-3 transition
        {{ request()->routeIs('customer.handovers.*')
            ? 'bg-emerald-50 font-semibold text-emerald-800'
            : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Handovers
    </a>

    <a href="{{ route('customer.notifications.index') }}"
        class="flex items-center justify-between rounded-xl px-4 py-3 transition
        {{ request()->routeIs('customer.notifications.*')
            ? 'bg-emerald-50 font-semibold text-emerald-800'
            : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        <span>Notifications</span>

        @if (auth()->user()->unreadNotifications()->exists())
            <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">
                {{ auth()->user()->unreadNotifications()->count() }}
            </span>
        @endif
    </a>

    <a href="{{ route('customer.inspections.index') }}"
        class="block rounded-xl px-4 py-3 transition
        {{ request()->routeIs('customer.inspections.*')
            ? 'bg-emerald-50 font-semibold text-emerald-800'
            : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Inspections
    </a>

    <a href="{{ route('customer.communications.index') }}"
        class="block rounded-xl px-4 py-3 transition
        {{ request()->routeIs('customer.communications.*')
            ? 'bg-emerald-50 font-semibold text-emerald-800'
            : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        Messages
    </a>

    <a href="{{ route('customer.documents.index') }}"
        class="block rounded-xl px-4 py-3 transition
            {{ request()->routeIs('customer.documents.*')
                ? 'bg-emerald-50 font-semibold text-emerald-800'
                : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}">
        My documents
    </a>
</div>
