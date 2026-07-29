<p class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
    Administration
</p>

<nav class="space-y-2 text-sm font-semibold">
    @php
        $adminLinks = [
            ['admin.dashboard', 'admin.dashboard', 'Dashboard'],
            ['admin.staff.index', 'admin.staff.*', 'Staff accounts'],
            ['admin.customers.index', 'admin.customers.*', 'Customers'],
            ['admin.estate-properties.index', 'admin.estate-properties.*', 'Estates and properties'],
            ['admin.access-control.index', 'admin.access-control.*', 'Roles and permissions'],
            ['admin.reports.index', 'admin.reports.*', 'Reports'],
            ['admin.audit.index', 'admin.audit.*', 'Audit logs'],
            ['admin.system-settings.index', 'admin.system-settings.*', 'System settings'],
        ];

        $operationLinks = [
            ['staff.enquiries.index', 'staff.enquiries.*', 'Property enquiries'],
            ['staff.reservations.index', 'staff.reservations.*', 'Reservations'],
            ['staff.purchases.index', 'staff.purchases.*', 'Purchases'],
            ['staff.payments.index', 'staff.payments.*', 'Payments'],
            ['staff.documents.index', 'staff.documents.*', 'Documents'],
            ['staff.communications.index', 'staff.communications.*', 'Communications'],
            ['staff.inspections.index', 'staff.inspections.*', 'Inspections'],
            ['staff.handovers.index', 'staff.handovers.*', 'Handovers'],
        ];
    @endphp

    @foreach ($adminLinks as [$routeName, $activePattern, $label])
        @if (\Illuminate\Support\Facades\Route::has($routeName))
            <a
                href="{{ route($routeName) }}"
                class="block rounded-xl px-4 py-3 transition
                    {{ request()->routeIs($activePattern)
                        ? 'bg-stone-900 text-white'
                        : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}"
            >
                {{ $label }}
            </a>
        @endif
    @endforeach

    <p class="px-4 pb-1 pt-5 text-[10px] font-bold uppercase tracking-[0.16em] text-stone-400">
        Operations
    </p>

    @foreach ($operationLinks as [$routeName, $activePattern, $label])
        @if (\Illuminate\Support\Facades\Route::has($routeName))
            <a
                href="{{ route($routeName) }}"
                class="block rounded-xl px-4 py-3 transition
                    {{ request()->routeIs($activePattern)
                        ? 'bg-emerald-50 text-emerald-800'
                        : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}"
            >
                {{ $label }}
            </a>
        @endif
    @endforeach
</nav>