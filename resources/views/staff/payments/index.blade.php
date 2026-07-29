@extends('layouts.portal')

@section('title', 'Payments | Ajoda GRA')

@section('portal_name',
    auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value)
    ? 'System
    Administration'
    : 'Staff Operations Portal')

@section('page_heading', 'Payments')

@section('page_eyebrow', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'Administration' :
    'Staff Operations')

@section('navigation')
    @include('shared.contextual-navigation', [
        'staffNavigation' => 'staff.partials.portal-navigation',
    ])

@endsection

@section('content')

    @php
        $canRecordPayments = auth()->user()?->can('payments.record') ?? false;

        $canVerifyPayments = auth()->user()?->can('payments.verify') ?? false;
    @endphp

    @if (session('status'))
        <div
            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
            <p class="font-bold">
                Please correct the following:
            </p>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">
            Financial operations
        </p>

        <h2 class="mt-2 text-3xl font-bold text-stone-950">
            Purchase payments
        </h2>

        <p class="mt-2 text-sm text-stone-600">
            Record customer payments and verify or reject pending transactions.
        </p>
    </div>

    <div class="mt-8 space-y-6">
        @forelse ($purchases as $purchase)
            @php
                $purchaseAmount =
                    (float) (data_get($purchase, 'purchase_price') ??
                        (data_get($purchase, 'total_amount') ??
                            (data_get($purchase, 'agreed_price') ?? (data_get($purchase, 'amount') ?? 0))));

                $verifiedAmount = (float) $purchase->payments
                    ->filter(fn($payment) => $payment->status === \App\Enums\PaymentStatus::Verified)
                    ->sum('amount');

                $outstandingAmount = max($purchaseAmount - $verifiedAmount, 0);

                $purchaseReference =
                    data_get($purchase, 'reference') ??
                    (data_get($purchase, 'purchase_reference') ?? 'Purchase ' . $purchase->getKey());

                $propertyCode =
                    data_get($purchase, 'house.property_code') ??
                    (data_get($purchase, 'house.code') ?? 'Property ' . $purchase->house_id);

                $houseType =
                    data_get($purchase, 'house.houseType.name') ?? data_get($purchase, 'house.houseType.title');

                $customerName =
                    data_get($purchase, 'customer.name') ?? (data_get($purchase, 'customer.email') ?? 'Customer');
            @endphp

            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                <div class="border-b border-stone-200 bg-stone-50 px-6 py-5">
                    <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-emerald-700">
                                {{ $purchaseReference }}
                            </p>

                            <h3 class="mt-2 text-xl font-bold text-stone-950">
                                {{ $propertyCode }}
                                @if ($houseType)
                                    <span class="font-normal text-stone-500">
                                        - {{ $houseType }}
                                    </span>
                                @endif
                            </h3>

                            <p class="mt-2 text-sm text-stone-600">
                                Customer: {{ $customerName }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-right sm:grid-cols-3">
                            <div class="rounded-xl bg-white px-4 py-3">
                                <p class="text-xs text-stone-500">
                                    Purchase amount
                                </p>

                                <p class="mt-1 font-bold text-stone-950">
                                    &#8358;{{ number_format($purchaseAmount, 2) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-white px-4 py-3">
                                <p class="text-xs text-stone-500">
                                    Verified
                                </p>

                                <p class="mt-1 font-bold text-emerald-700">
                                    &#8358;{{ number_format($verifiedAmount, 2) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-white px-4 py-3">
                                <p class="text-xs text-stone-500">
                                    Outstanding
                                </p>

                                <p class="mt-1 font-bold text-amber-700">
                                    &#8358;{{ number_format($outstandingAmount, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-6 p-6
        {{ $canRecordPayments ? 'xl:grid-cols-5' : 'xl:grid-cols-1' }}">
                    <div class="{{ $canRecordPayments ? 'xl:col-span-3' : '' }}">
                        <h4 class="font-bold text-stone-950">
                            Payment history
                        </h4>

                        <div class="mt-4 space-y-4">
                            @forelse ($purchase->payments as $payment)
                                <section class="rounded-xl border border-stone-200 p-5">
                                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                                        <div>
                                            <p class="font-bold text-stone-950">
                                                {{ $payment->reference }}
                                            </p>

                                            <p class="mt-1 text-sm text-stone-600">
                                                &#8358;{{ number_format((float) $payment->amount, 2) }}
                                                via
                                                {{ $payment->payment_method->label() }}
                                            </p>

                                            <p class="mt-1 text-xs text-stone-500">
                                                Paid:
                                                {{ $payment->paid_at?->format('d M Y, g:i A') ?? 'Not supplied' }}
                                            </p>

                                            @if ($payment->transaction_reference)
                                                <p class="mt-1 text-xs text-stone-500">
                                                    Transaction reference:
                                                    {{ $payment->transaction_reference }}
                                                </p>
                                            @endif
                                        </div>

                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold
                                            @class([
                                                'bg-amber-100 text-amber-800' =>
                                                    $payment->status === \App\Enums\PaymentStatus::PendingVerification,
                                                'bg-emerald-100 text-emerald-800' =>
                                                    $payment->status === \App\Enums\PaymentStatus::Verified,
                                                'bg-red-100 text-red-800' =>
                                                    $payment->status === \App\Enums\PaymentStatus::Rejected,
                                                'bg-stone-100 text-stone-700' =>
                                                    $payment->status === \App\Enums\PaymentStatus::Refunded,
                                            ])">
                                            {{ $payment->status->label() }}
                                        </span>
                                    </div>

                                    @if ($payment->notes)
                                        <p class="mt-4 rounded-lg bg-stone-50 p-3 text-sm text-stone-600">
                                            {{ $payment->notes }}
                                        </p>
                                    @endif

                                    <div class="mt-4 text-xs text-stone-500">
                                        Recorded by:
                                        {{ $payment->recordedBy?->name ?? 'System' }}

                                        @if ($payment->verifiedBy)
                                            | Reviewed by:
                                            {{ $payment->verifiedBy->name }}
                                        @endif
                                    </div>

                                    @if ($canVerifyPayments && $payment->status === \App\Enums\PaymentStatus::PendingVerification)
                                        <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                            @if (\Illuminate\Support\Facades\Route::has('staff.payments.verify'))
                                                <form method="POST"
                                                    action="{{ route('staff.payments.verify', $payment) }}"
                                                    class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <label class="block text-sm font-bold text-emerald-900">
                                                        Verification notes
                                                    </label>

                                                    <textarea name="verification_notes" rows="3"
                                                        class="mt-2 block w-full rounded-lg border-emerald-300 bg-white text-sm" placeholder="Optional verification note">{{ old('verification_notes') }}</textarea>

                                                    <button type="submit"
                                                        class="mt-3 w-full rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white">
                                                        Verify payment
                                                    </button>
                                                </form>
                                            @endif

                                            @if (\Illuminate\Support\Facades\Route::has('staff.payments.reject'))
                                                <form method="POST"
                                                    action="{{ route('staff.payments.reject', $payment) }}"
                                                    class="rounded-xl border border-red-200 bg-red-50 p-4">
                                                    @csrf
                                                    @method('PATCH')

                                                    <label class="block text-sm font-bold text-red-900">
                                                        Rejection reason
                                                    </label>

                                                    <textarea name="verification_notes" rows="3" required
                                                        class="mt-2 block w-full rounded-lg border-red-300 bg-white text-sm"
                                                        placeholder="State why this payment is rejected">{{ old('verification_notes') }}</textarea>

                                                    <button type="submit"
                                                        class="mt-3 w-full rounded-lg bg-red-700 px-4 py-2.5 text-sm font-bold text-white">
                                                        Reject payment
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </section>
                            @empty
                                <div
                                    class="rounded-xl border border-dashed border-stone-300 px-5 py-8 text-center text-sm text-stone-500">
                                    No payments have been recorded for this purchase.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    @if ($canRecordPayments)
                        <aside class="xl:col-span-2">
                            <h4 class="font-bold text-stone-950">
                                Record payment
                            </h4>

                            @if (\Illuminate\Support\Facades\Route::has('staff.payments.store'))
                                <form method="POST" action="{{ route('staff.payments.store', $purchase) }}"
                                    class="mt-4 space-y-4 rounded-xl border border-stone-200 bg-stone-50 p-5">
                                    @csrf

                                    <div>
                                        <label class="block text-sm font-bold text-stone-700">
                                            Amount
                                        </label>

                                        <input type="number" name="amount" min="0.01" step="0.01"
                                            value="{{ old('amount') }}" required
                                            class="mt-2 block w-full rounded-lg border-stone-300 bg-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-stone-700">
                                            Payment method
                                        </label>

                                        <select name="payment_method" required
                                            class="mt-2 block w-full rounded-lg border-stone-300 bg-white">
                                            <option value="">
                                                Select method
                                            </option>

                                            @foreach (\App\Enums\PaymentMethod::cases() as $method)
                                                <option value="{{ $method->value }}" @selected(old('payment_method') === $method->value)>
                                                    {{ $method->label() }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-stone-700">
                                            Date paid
                                        </label>

                                        <input type="datetime-local" name="paid_at"
                                            value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required
                                            class="mt-2 block w-full rounded-lg border-stone-300 bg-white">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-stone-700">
                                            Transaction reference
                                        </label>

                                        <input type="text" name="transaction_reference"
                                            value="{{ old('transaction_reference') }}"
                                            class="mt-2 block w-full rounded-lg border-stone-300 bg-white"
                                            placeholder="Bank or payment reference">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-stone-700">
                                            Notes
                                        </label>

                                        <textarea name="notes" rows="3" class="mt-2 block w-full rounded-lg border-stone-300 bg-white"
                                            placeholder="Optional payment note">{{ old('notes') }}</textarea>
                                    </div>

                                    <button type="submit"
                                        class="w-full rounded-lg bg-stone-900 px-4 py-3 text-sm font-bold text-white">
                                        Record payment
                                    </button>
                                </form>
                            @else
                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                                    The payment recording route is not available.
                                </div>
                            @endif
                        </aside>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center shadow-sm">
                <h3 class="text-lg font-bold text-stone-950">
                    No purchases found
                </h3>

                <p class="mt-2 text-sm text-stone-500">
                    Payments can be recorded after a purchase has been created.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $purchases->links() }}
    </div>
@endsection
