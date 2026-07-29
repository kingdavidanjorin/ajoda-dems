@extends('layouts.portal')

@section('title', 'Staff Dashboard | Ajoda GRA')
@section('portal_name', 'Staff Operations Portal')
@section('page_heading', 'Staff dashboard')
@section('page_eyebrow', 'Ajoda GRA')

@section('navigation')
    @include('staff.partials.portal-navigation')
@endsection

@section('content')
    @php
        $user = auth()->user();

        $roleNames = $user
            ?->getRoleNames()
            ->implode(', ');

        $roleNames = filled($roleNames)
            ? $roleNames
            : 'Staff Member';

        $availableModules = collect(
            config('staff-modules', []),
        )->filter(
            function (array $module) use ($user): bool {
                if (
                    ! ($module['show_on_dashboard'] ?? true)
                ) {
                    return false;
                }

                $routeExists =
                    \Illuminate\Support\Facades\Route::has(
                        $module['route'],
                    );

                $hasPermission =
                    $user?->can($module['permission'])
                    ?? false;

                return $routeExists
                    && $hasPermission;
            },
        );

        $assignedPermissions = $user
            ?->getAllPermissions()
            ->pluck('name')
            ->sort()
            ->values()
            ?? collect();
    @endphp

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            {{ $roleNames }}
        </p>

        <h2 class="mt-2 text-3xl font-bold text-stone-950">
            Welcome, {{ $user?->name }}
        </h2>

        <p class="mt-2 text-stone-600">
            Access the modules assigned to your account.
        </p>
    </div>

    <section class="mt-8">
        <h3 class="text-xl font-bold text-stone-950">
            Available modules
        </h3>

        <div class="mt-5 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($availableModules as $module)
                <a
                    href="{{ route($module['route']) }}"
                    class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-emerald-600 hover:shadow-md"
                >
                    <h4 class="text-lg font-bold text-stone-950">
                        {{ $module['label'] }}
                    </h4>

                    <p class="mt-2 text-sm leading-6 text-stone-600">
                        {{ $module['description'] }}
                    </p>

                    <p class="mt-5 text-sm font-bold text-emerald-700">
                        Open module
                    </p>
                </a>
            @empty
                <div class="rounded-2xl border border-stone-200 bg-white p-8 text-stone-600 md:col-span-2">
                    No working modules are currently available for this account.
                </div>
            @endforelse
        </div>
    </section>

    <section class="mt-10 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div>
            <h3 class="text-xl font-bold text-stone-950">
                Assigned permissions
            </h3>

            <p class="mt-1 text-sm text-stone-600">
                {{ $assignedPermissions->count() }}
                permissions assigned to this account.
            </p>
        </div>

        <div class="mt-5 flex flex-wrap gap-2">
            @forelse ($assignedPermissions as $permission)
                <span class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">
                    {{ $permission }}
                </span>
            @empty
                <p class="text-sm text-stone-500">
                    No permissions assigned.
                </p>
            @endforelse
        </div>
    </section>
@endsection
