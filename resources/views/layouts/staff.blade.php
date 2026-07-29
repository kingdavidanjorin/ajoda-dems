<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        @yield('title', 'Staff Portal') | {{ config('app.name') }}
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="min-h-screen lg:flex">
        <aside class="w-full bg-slate-950 text-white lg:min-h-screen lg:w-72">
            <div class="border-b border-slate-800 px-6 py-6">
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-400">
                    Ajoda GRA DEMS
                </p>

                <h1 class="mt-2 text-xl font-bold">
                    Staff Operations Portal
                </h1>
            </div>

            <nav class="space-y-2 px-4 py-6">
                <a
                    href="{{ route('staff.dashboard') }}"
                    class="block rounded-lg px-4 py-3 text-sm font-medium
                        {{ request()->routeIs('staff.dashboard')
                            ? 'bg-emerald-600 text-white'
                            : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                >
                    Dashboard
                </a>

                @can('enquiries.view')
                    <a
                        href="{{ route('staff.enquiries.index') }}"
                        class="block rounded-lg px-4 py-3 text-sm font-medium
                            {{ request()->routeIs('staff.enquiries.*')
                                ? 'bg-emerald-600 text-white'
                                : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"
                    >
                        Property Enquiries
                    </a>
                @endcan
            </nav>
        </aside>

        <div class="flex min-h-screen flex-1 flex-col">
            <header class="border-b border-slate-200 bg-white px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-slate-500">
                            Signed in as
                        </p>

                        <p class="font-semibold">
                            {{ auth()->user()->name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 sm:inline">
                            {{ auth()->user()->getRoleNames()->first() ?? 'Staff' }}
                        </span>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100"
                            >
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 lg:p-8">
                @if (session('status'))
                    <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        <p class="font-semibold">
                            Please correct the following:
                        </p>

                        <ul class="mt-2 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>