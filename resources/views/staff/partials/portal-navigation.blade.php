@php
    $user = auth()->user();

    $modules = collect(
        config('staff-modules', []),
    );
@endphp

<p class="mb-4 text-xs font-bold uppercase tracking-[0.16em] text-stone-400">
    Staff operations
</p>

<nav class="space-y-2 text-sm font-semibold">
    @foreach ($modules as $module)
        @php
            $routeExists =
                \Illuminate\Support\Facades\Route::has(
                    $module['route'],
                );

            $hasPermission =
                $user?->can($module['permission'])
                ?? false;
        @endphp

        @if ($routeExists && $hasPermission)
            <a
                href="{{ route($module['route']) }}"
                class="block rounded-xl px-4 py-3 transition
                    {{ request()->routeIs($module['active'])
                        ? 'bg-stone-900 text-white'
                        : 'text-stone-700 hover:bg-stone-100 hover:text-stone-950' }}"
            >
                {{ $module['label'] }}
            </a>
        @endif
    @endforeach
</nav>