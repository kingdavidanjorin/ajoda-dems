<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'Ajoda GRA Portal')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-100 text-stone-900 antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-12">
        <aside class="border-b border-stone-200 bg-white lg:col-span-3 lg:min-h-screen lg:border-b-0 lg:border-r xl:col-span-2">
            <div class="flex h-full flex-col">
                <div class="border-b border-stone-200 px-6 py-6">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center gap-3"
                    >
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-700 text-sm font-bold text-white">
                            AG
                        </span>

                        <span class="min-w-0">
                            <span class="block truncate text-base font-bold">
                                Ajoda GRA
                            </span>

                            <span class="block truncate text-xs text-stone-500">
                                @yield('portal_name', 'Digital Estate Management')
                            </span>
                        </span>
                    </a>
                </div>

                <div class="flex-1 px-4 py-6">
                    @yield('navigation')
                </div>

                <div class="border-t border-stone-200 p-4">
                    <div class="mb-4 flex items-center gap-3 rounded-xl bg-stone-100 p-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-stone-900 text-sm font-bold text-white">
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

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="flex w-full items-center justify-center rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-red-300 hover:bg-red-50 hover:text-red-700"
                        >
                            Log out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <div class="lg:col-span-9 xl:col-span-10">
            <header class="border-b border-stone-200 bg-white">
                <div class="flex min-h-20 items-center justify-between gap-6 px-6 py-4 lg:px-8">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
                            @yield('page_eyebrow', 'Ajoda GRA')
                        </p>

                        <h1 class="mt-1 text-xl font-bold sm:text-2xl">
                            @yield('page_title', 'Dashboard')
                        </h1>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('properties.index') }}"
                            class="hidden rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold transition hover:border-emerald-600 hover:text-emerald-700 sm:inline-flex"
                        >
                            Browse properties
                        </a>

                        <a
                            href="{{ route('home') }}"
                            class="rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Public website
                        </a>
                    </div>
                </div>
            </header>

            <main class="px-6 py-8 lg:px-8 lg:py-10">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>