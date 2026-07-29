@extends('layouts.portal')

@section('title', 'System Settings | Ajoda GRA')
@section('portal_name', 'System Administration')
@section('page_heading', 'System settings')
@section('page_eyebrow', 'Administration')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div>
        <h2 class="text-2xl font-bold text-stone-950">
            Environment summary
        </h2>

        <p class="mt-2 text-sm text-stone-600">
            Safe, non-secret configuration values currently used by Ajoda DEMS.
        </p>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        @foreach ($settings as $label => $value)
            <div class="flex flex-col justify-between gap-2 border-b border-stone-100 px-6 py-4 sm:flex-row sm:items-center">
                <span class="text-sm font-semibold text-stone-700">
                    {{ $label }}
                </span>

                <span class="break-all text-sm text-stone-600">
                    {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}
                </span>
            </div>
        @endforeach
    </div>
@endsection