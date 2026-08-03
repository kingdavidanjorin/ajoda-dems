@php
    $pageTitle = trim(strip_tags($__env->yieldContent('title', 'Ajoda GRA Portal')));

    $portalName = trim(strip_tags($__env->yieldContent('portal_name', 'Digital Estate Management')));

    $pageEyebrow = trim(strip_tags($__env->yieldContent('page_eyebrow', 'Ajoda GRA')));

    $pageHeading = trim(strip_tags($__env->yieldContent('page_heading', 'Dashboard')));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $pageTitle }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-12">

        {{-- Desktop sidebar --}}
        <aside class="hidden border-r border-stone-200 bg-white lg:col-span-3 lg:block lg:min-h-screen xl:col-span-2">
            <div class="flex h-full min-h-screen flex-col">
                <div class="border-b border-stone-200 px-6 py-6">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-700 text-sm font-bold text-white">
                            AG
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-base font-bold">
                                Ajoda GRA
                            </span>

                            <span class="block truncate text-xs text-stone-500">
                                {{ $portalName }}
                            </span>
                        </span>
                    </a>
                </div>

                <div class="flex-1 px-4 py-6">
                    @yield('navigation')
                </div>

                <div class="border-t border-stone-200 p-4">
                    <div class="mb-4 flex items-center gap-3 rounded-xl bg-stone-100 p-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-900 text-sm font-bold text-white">
                            {{ auth()->user()->initials() }}
                        </div>

                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">
                                {{ auth()->user()->name }}
                            </p>

                            <p class="truncate text-xs text-stone-500">
                                {{ auth()->user()->email }}
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <button type="submit"
                            class="flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0 lg:col-span-9 xl:col-span-10">

            {{-- Mobile navigation bar --}}
            <div class="relative z-50 border-b border-stone-200 bg-white lg:hidden">
                <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-3">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-700 text-sm font-bold text-white">
                            AG
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-sm font-bold">
                                Ajoda GRA
                            </span>

                            <span class="block truncate text-xs text-stone-500">
                                {{ $portalName }}
                            </span>
                        </span>
                    </a>

                    <details class="group relative">
                        <summary
                            class="flex h-11 w-11 cursor-pointer list-none items-center justify-center rounded-xl border border-stone-300 bg-white transition hover:border-emerald-600 hover:text-emerald-700"
                            title="Open menu">
                            <span class="sr-only">
                                Toggle navigation menu
                            </span>
                            {{-- Hamburger icon --}}
                            <svg class="h-6 w-6 group-open:hidden" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 6h16" />
                                <path d="M4 12h16" />
                                <path d="M4 18h16" />
                            </svg>

                            {{-- Close icon shown when menu is open --}}
                            <svg class="hidden h-6 w-6 group-open:block" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M6 6l12 12" />
                                <path d="M18 6L6 18" />
                            </svg>
                        </summary>

                        <div
                            class="absolute right-0 top-full z-50 mt-3 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-xl">
                            <div class="max-h-[60vh] overflow-y-auto px-4 py-5">
                                @yield('navigation')
                            </div>

                            <div class="border-t border-stone-200 p-4">
                                <div class="mb-4 flex items-center gap-3 rounded-xl bg-stone-100 p-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-900 text-sm font-bold text-white">
                                        {{ auth()->user()->initials() }}
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold">
                                            {{ auth()->user()->name }}
                                        </p>

                                        <p class="truncate text-xs text-stone-500">
                                            {{ auth()->user()->email }}
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf

                                    <button type="submit"
                                        class="flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
                                        Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            {{-- Page header --}}
            <header class="border-b border-stone-200 bg-white">
                <div
                    class="flex min-h-20 flex-col items-start justify-between gap-4 px-5 py-5 sm:flex-row sm:items-center sm:px-6 lg:px-8">
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                            {{ $pageEyebrow }}
                        </p>

                        <h1 class="mt-1 truncate text-xl font-bold sm:text-2xl">
                            {{ $pageHeading }}
                        </h1>
                    </div>

                    <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto sm:justify-end">
                        <a href="{{ route('properties.index') }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700 sm:flex-none">
                            Browse properties
                        </a>

                        <a href="{{ route('home') }}"
                            class="inline-flex flex-1 items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 sm:flex-none">
                            Public website
                        </a>
                    </div>
                </div>
            </header>

            <main class="px-5 py-7 sm:px-6 lg:px-8 lg:py-10">
                @if (session('status'))
                    <div
                        class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div
                        class="mb-6 rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-900">
                        {{ session('warning') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
