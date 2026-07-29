@extends('layouts.portal')

@section('title', 'Document Management | Ajoda GRA')
@section('portal_name',
    auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value)
    ? 'System
    Administration'
    : 'Staff Operations')
@section('page_heading', 'Documents')
@section('page_eyebrow', auth()->user()?->hasRole(\App\Enums\RoleName::SystemAdministrator->value) ? 'Administration' :
    'Staff Operations')

@section('navigation')
    @include('shared.contextual-navigation', ['staffNavigation' => 'staff.partials.portal-navigation'])
@endsection

@section('content')
    @if (session('document_status'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
            {{ session('document_status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-3">
        @if ($canManageDocuments)
            <section class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold">
                    Upload document
                </h2>

                @if ($canManageDocuments)
                    <form method="POST" action="{{ route('staff.documents.store') }}" enctype="multipart/form-data"
                        class="mt-6 space-y-5">
                        @csrf

                        <div>
                            <label class="text-sm font-semibold">
                                Customer
                            </label>

                            <select name="customer_id" required class="mt-2 w-full rounded-xl border-stone-300">
                                <option value="">Select customer</option>

                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" @selected(old('customer_id') == $customer->id)>
                                        {{ $customer->name }} â€” {{ $customer->email }}
                                    </option>
                                @endforeach
                            </select>

                            @error('customer_id')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Purchase, when applicable
                            </label>

                            <select name="purchase_id" class="mt-2 w-full rounded-xl border-stone-300">
                                <option value="">No purchase selected</option>

                                @foreach ($purchases as $purchase)
                                    <option value="{{ $purchase->id }}" @selected(old('purchase_id') == $purchase->id)>
                                        {{ $purchase->reference }}
                                        â€” {{ $purchase->customer?->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('purchase_id')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Document type
                            </label>

                            <select name="document_type" required class="mt-2 w-full rounded-xl border-stone-300">
                                @foreach (\App\Enums\DocumentType::cases() as $type)
                                    <option value="{{ $type->value }}" @selected(old('document_type') === $type->value)>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Visibility
                            </label>

                            <select name="visibility" required class="mt-2 w-full rounded-xl border-stone-300">
                                @foreach (\App\Enums\DocumentVisibility::cases() as $visibility)
                                    <option value="{{ $visibility->value }}" @selected(old('visibility') === $visibility->value)>
                                        {{ $visibility->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Title
                            </label>

                            <input type="text" name="title" value="{{ old('title') }}" required
                                class="mt-2 w-full rounded-xl border-stone-300">

                            @error('title')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                File
                            </label>

                            <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                                class="mt-2 block w-full text-sm">

                            <p class="mt-1 text-xs text-stone-500">
                                Maximum file size: 10 MB.
                            </p>

                            @error('file')
                                <p class="mt-1 text-sm text-red-700">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">
                                Description
                            </label>

                            <textarea name="description" rows="3" class="mt-2 w-full rounded-xl border-stone-300">{{ old('description') }}</textarea>
                        </div>

                        <button type="submit"
                            class="w-full rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white hover:bg-emerald-800">
                            Upload securely
                        </button>
                    </form>
                @endif
            </section>
        @endif

        <section class="xl:col-span-2">
            <h2 class="text-xl font-bold">
                Uploaded documents
            </h2>

            <div class="mt-6 space-y-4">
                @forelse ($documents as $document)
                    <article class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col justify-between gap-4 sm:flex-row">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-bold">
                                        {{ $document->title }}
                                    </h3>

                                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold">
                                        {{ $document->visibility->label() }}
                                    </span>
                                </div>

                                <p class="mt-2 text-sm text-stone-600">
                                    {{ $document->reference }}
                                </p>

                                <p class="mt-1 text-sm text-stone-600">
                                    {{ $document->customer?->name }}
                                    Â· {{ $document->document_type->label() }}
                                </p>

                                <p class="mt-1 text-xs text-stone-500">
                                    {{ $document->original_name }}
                                    Â· {{ number_format($document->size_bytes / 1024, 1) }} KB
                                </p>
                            </div>

                            <a href="{{ route('staff.documents.download', $document) }}"
                                class="inline-flex self-start rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-semibold hover:border-emerald-600 hover:text-emerald-700">
                                Download
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-stone-200 bg-white p-10 text-center">
                        No documents have been uploaded.
                    </div>
                @endforelse
            </div>

            <div class="mt-8">
                {{ $documents->links() }}
            </div>
        </section>
    </div>
@endsection
