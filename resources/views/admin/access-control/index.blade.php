@extends('layouts.portal')

@section('title', 'Roles and Permissions | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'Roles and permissions')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-stone-950">
            Access-control matrix
        </h2>

        <p class="mt-2 text-sm text-stone-600">
            Review the permissions currently assigned to every role.
        </p>
    </div>

    <div class="mt-6 grid gap-5 lg:grid-cols-2">
        @foreach ($roles as $role)
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-lg font-bold text-stone-950">
                        {{ $role->name }}
                    </h3>

                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">
                        {{ $role->permissions->count() }} permissions
                    </span>
                </div>

                <div class="mt-5 flex flex-wrap gap-2">
                    @forelse ($role->permissions as $permission)
                        <span class="rounded-lg bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-800">
                            {{ $permission->name }}
                        </span>
                    @empty
                        <p class="text-sm text-stone-500">
                            No permissions assigned.
                        </p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
@endsection