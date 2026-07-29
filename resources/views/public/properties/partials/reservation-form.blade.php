@auth
    @php
        $currentUser = auth()->user();

        $accountType = $currentUser->account_type instanceof \BackedEnum
            ? $currentUser->account_type->value
            : $currentUser->account_type;

        $isCustomer = $accountType
            === \App\Enums\AccountType::Customer->value;
    @endphp

    @if ($isCustomer)
        <section class="mt-8 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-stone-500">
                Property reservation
            </p>

            <h2 class="mt-2 text-2xl font-bold text-stone-950">
                Request a temporary reservation
            </h2>

            @if ($house->status === \App\Enums\PropertyStatus::Available)
                <p class="mt-3 text-sm leading-6 text-stone-600">
                    Submit a request for review. The property will only be
                    temporarily held after approval by a Sales Officer.
                </p>

                <form
                    method="POST"
                    action="{{ route('customer.reservations.store', $house) }}"
                    class="mt-6"
                >
                    @csrf

                    <label
                        for="customer_notes"
                        class="block text-sm font-semibold text-stone-800"
                    >
                        Additional notes
                    </label>

                    <textarea
                        id="customer_notes"
                        name="customer_notes"
                        rows="4"
                        maxlength="2000"
                        class="mt-2 block w-full rounded-xl border-stone-300"
                        placeholder="Provide any information relevant to your reservation request."
                    >{{ old('customer_notes') }}</textarea>

                    @error('customer_notes')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    @error('house_id')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                    <button
                        type="submit"
                        class="mt-4 rounded-xl bg-stone-900 px-5 py-3 text-sm font-bold text-white hover:bg-stone-700"
                    >
                        Submit reservation request
                    </button>
                </form>
            @else
                <div class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    This property is currently unavailable for reservation.
                </div>
            @endif
        </section>
    @endif
@endauth