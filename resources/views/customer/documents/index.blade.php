@extends('layouts.portal')

@section('title', 'My Documents | Ajoda GRA')
@section('portal_name', 'Customer Portal')
@section('page_heading', 'My documents')
@section('page_eyebrow', 'Customer Portal')

@section('navigation')
    @include('customer.partials.portal-navigation')
@endsection

@section('content')
    <h2 class="text-3xl font-bold">
        My property documents
    </h2>

    <p class="mt-3 text-sm text-stone-600">
        Securely access documents released to your customer account.
    </p>

    <div class="mt-8 space-y-4">
        @forelse ($documents as $document)
            <article class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col justify-between gap-5 sm:flex-row">
                    <div>
                        <h3 class="text-lg font-bold">
                            {{ $document->title }}
                        </h3>

                        <p class="mt-2 text-sm text-stone-600">
                            {{ $document->document_type->label() }}
                        </p>

                        <p class="mt-1 text-sm text-stone-500">
                            Reference: {{ $document->reference }}
                        </p>

                        @if ($document->purchase)
                            <p class="mt-1 text-sm text-stone-500">
                                Purchase: {{ $document->purchase->reference }}
                            </p>
                        @endif

                        <p class="mt-1 text-xs text-stone-500">
                            Uploaded {{ $document->uploaded_at?->format('d M Y') }}
                        </p>

                        @if ($document->description)
                            <p class="mt-4 text-sm leading-6 text-stone-700">
                                {{ $document->description }}
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('customer.documents.download', $document) }}"
                        class="inline-flex self-start rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                        Download
                    </a>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-stone-200 bg-white p-12 text-center">
                <h3 class="text-xl font-bold">
                    No documents available
                </h3>

                <p class="mt-2 text-sm text-stone-600">
                    Documents released by the estate team will appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $documents->links() }}
    </div>
@endsection