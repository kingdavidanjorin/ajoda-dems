<p class="px-3 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
    Administration
</p>

<nav class="mt-4 space-y-1 text-sm font-semibold">
    <a
        href="{{ route('admin.dashboard') }}"
        @class([
            'block rounded-xl px-4 py-3 transition',
            'bg-stone-900 text-white' =>
                request()->routeIs('admin.dashboard'),
            'text-stone-700 hover:bg-stone-100' =>
                ! request()->routeIs('admin.dashboard'),
        ])
    >
        Dashboard
    </a>

    <a
        href="{{ route('admin.staff.index') }}"
        @class([
            'block rounded-xl px-4 py-3 transition',
            'bg-stone-900 text-white' =>
                request()->routeIs('admin.staff.*'),
            'text-stone-700 hover:bg-stone-100' =>
                ! request()->routeIs('admin.staff.*'),
        ])
    >
        Staff accounts
    </a>

    <span class="flex items-center justify-between rounded-xl px-4 py-3 text-stone-400">
        <span>Customers</span>

        <span class="text-[10px] font-bold uppercase">
            Soon
        </span>
    </span>

    <span class="flex items-center justify-between rounded-xl px-4 py-3 text-stone-400">
        <span>Estates and properties</span>

        <span class="text-[10px] font-bold uppercase">
            Soon
        </span>
    </span>

    <span class="flex items-center justify-between rounded-xl px-4 py-3 text-stone-400">
        <span>Roles and permissions</span>

        <span class="text-[10px] font-bold uppercase">
            Later
        </span>
    </span>

    <span class="flex items-center justify-between rounded-xl px-4 py-3 text-stone-400">
        <span>Audit logs</span>

        <span class="text-[10px] font-bold uppercase">
            Later
        </span>
    </span>

    <span class="flex items-center justify-between rounded-xl px-4 py-3 text-stone-400">
        <span>System settings</span>

        <span class="text-[10px] font-bold uppercase">
            Later
        </span>
    </span>
</nav>