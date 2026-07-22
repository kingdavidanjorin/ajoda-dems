@extends('layouts.portal')

@section('title', 'Account Access | Ajoda GRA')
@section('portal_name', 'Account Access')

@section('navigation')
    <a
        href="{{ route('home') }}"
        class="block rounded-lg px-4 py-3 text-sm font-semibold hover:bg-stone-100"
    >
        Return to website
    </a>
@endsection

@section('content')
    <div class="rounded-2xl border border-amber-200 bg-white p-8">
        <p class="text-sm font-bold uppercase tracking-[0.18em] text-amber-700">
            Account access status
        </p>

        <h1 class="mt-3 text-3xl font-bold">
            Portal access is unavailable
        </h1>

        <p class="mt-5 text-lg text-stone-600">
            {{ $message }}
        </p>

        <dl class="mt-8 grid gap-4 border-t border-stone-200 pt-6 sm:grid-cols-2">
            <div>
                <dt class="text-sm text-stone-500">
                    Account type
                </dt>

                <dd class="mt-1 font-semibold">
                    {{ str($user->account_type->value)->headline() }}
                </dd>
            </div>

            <div>
                <dt class="text-sm text-stone-500">
                    Account status
                </dt>

                <dd class="mt-1 font-semibold">
                    {{ str($user->account_status->value)->headline() }}
                </dd>
            </div>
        </dl>
    </div>
@endsection