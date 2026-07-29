<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System Error | Ajoda GRA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <main class="flex min-h-screen items-center justify-center px-5 py-12">
        <section class="w-full max-w-xl rounded-2xl border border-stone-200 bg-white p-10 text-center shadow-sm">
            <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">Ajoda GRA</p>
            <h1 class="mt-4 text-5xl font-bold">500</h1>
            <h2 class="mt-3 text-2xl font-bold">System error</h2>
            <p class="mt-3 text-stone-600">An unexpected error occurred. Please try again later.</p>
            <a href="{{ url('/') }}" class="mt-7 inline-block rounded-xl bg-emerald-700 px-5 py-3 font-bold text-white">Return home</a>
        </section>
    </main>
</body>
</html>