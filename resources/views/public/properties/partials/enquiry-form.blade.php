<section class="mt-10 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
    <p class="text-sm font-bold uppercase tracking-[0.18em] text-emerald-700">
        Property enquiry
    </p>

    <h2 class="mt-3 text-2xl font-bold">
        Ask about {{ $house->name }}
    </h2>

    <p class="mt-2 text-stone-600">
        Submit your details and a sales representative will contact you.
    </p>

    @if (session('enquiry_status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('enquiry_status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('properties.enquiries.store', $house) }}"
        class="mt-7 grid gap-5 md:grid-cols-2"
    >
        @csrf

        <div>
            <label
                for="full_name"
                class="mb-2 block text-sm font-semibold"
            >
                Full name
            </label>

            <input
                id="full_name"
                name="full_name"
                value="{{ old(
                    'full_name',
                    auth()->user()?->name,
                ) }}"
                required
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
        </div>

        <div>
            <label
                for="enquiry_email"
                class="mb-2 block text-sm font-semibold"
            >
                Email address
            </label>

            <input
                id="enquiry_email"
                type="email"
                name="email"
                value="{{ old(
                    'email',
                    auth()->user()?->email,
                ) }}"
                required
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
        </div>

        <div class="md:col-span-2">
            <label
                for="phone"
                class="mb-2 block text-sm font-semibold"
            >
                Phone number
            </label>

            <input
                id="phone"
                name="phone"
                value="{{ old('phone') }}"
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
            >
        </div>

        <div class="md:col-span-2">
            <label
                for="message"
                class="mb-2 block text-sm font-semibold"
            >
                Message
            </label>

            <textarea
                id="message"
                name="message"
                rows="5"
                required
                class="w-full rounded-xl border border-stone-300 px-4 py-3"
                placeholder="I would like more information about this property."
            >{{ old('message') }}</textarea>
        </div>

        <div class="md:col-span-2">
            <button
                type="submit"
                class="rounded-xl bg-emerald-700 px-6 py-3 font-bold text-white transition hover:bg-emerald-800"
            >
                Submit enquiry
            </button>
        </div>
    </form>
</section>