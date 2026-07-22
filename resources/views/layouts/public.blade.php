<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="description"
        content="@yield('meta_description', 'Discover modern residential properties at Ajoda GRA.')"
    >

    <title>
        @yield('title', config('app.name'))
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-stone-50 text-stone-900 antialiased">
    <header class="border-b border-stone-200 bg-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8">
            <a
                href="{{ route('home') }}"
                class="flex items-center gap-3"
            >
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-700 text-sm font-bold text-white">
                    AG
                </span>

                <span>
                    <span class="block text-lg font-bold tracking-tight">
                        Ajoda GRA
                    </span>

                    <span class="block text-xs text-stone-500">
                        Digital Estate Management
                    </span>
                </span>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-medium md:flex">
                <a
                    href="{{ route('home') }}"
                    class="text-stone-700 transition hover:text-emerald-700"
                >
                    Home
                </a>

                <a
                    href="#homes"
                    class="text-stone-700 transition hover:text-emerald-700"
                >
                    Properties
                </a>

                <a
                    href="#about"
                    class="text-stone-700 transition hover:text-emerald-700"
                >
                    About
                </a>

                <a
                    href="#contact"
                    class="text-stone-700 transition hover:text-emerald-700"
                >
                    Contact
                </a>
            </nav>

            <div class="flex items-center gap-3">
                @auth
                    <a
                        href="{{ route('dashboard') }}"
                        class="rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold transition hover:border-emerald-700 hover:text-emerald-700"
                    >
                        Dashboard
                    </a>

                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Log out
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="hidden rounded-lg border border-stone-300 px-4 py-2 text-sm font-semibold transition hover:border-emerald-700 hover:text-emerald-700 sm:inline-flex"
                    >
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a
                            href="{{ route('register') }}"
                            class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800"
                        >
                            Create account
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer
        id="contact"
        class="border-t border-stone-800 bg-stone-950 text-stone-300"
    >
        <div class="mx-auto grid max-w-7xl gap-10 px-6 py-14 md:grid-cols-3 lg:px-8">
            <div>
                <p class="text-lg font-bold text-white">
                    Ajoda GRA
                </p>

                <p class="mt-3 max-w-sm text-sm leading-6 text-stone-400">
                    A digital platform for discovering, purchasing and
                    monitoring residential properties within Ajoda GRA.
                </p>
            </div>

            <div>
                <p class="font-semibold text-white">
                    Quick links
                </p>

                <div class="mt-4 flex flex-col gap-3 text-sm">
                    <a
                        href="{{ route('home') }}"
                        class="hover:text-white"
                    >
                        Home
                    </a>

                    <a
                        href="#homes"
                        class="hover:text-white"
                    >
                        Available properties
                    </a>

                    <a
                        href="#about"
                        class="hover:text-white"
                    >
                        About the estate
                    </a>
                </div>
            </div>

            <div>
                <p class="font-semibold text-white">
                    Customer access
                </p>

                <div class="mt-4 flex flex-col gap-3 text-sm">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="hover:text-white"
                        >
                            My dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="hover:text-white"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="hover:text-white"
                            >
                                Create an account
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        <div class="border-t border-stone-800">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-6 py-6 text-xs text-stone-500 sm:flex-row sm:items-center sm:justify-between lg:px-8">
                <p>
                    &copy; {{ now()->year }} Ajoda GRA. All rights reserved.
                </p>

                <p>
                    Powered by Ajoda GRA DEMS
                </p>
            </div>
        </div>
    </footer>
</body>
</html>