@extends('layouts.portal')

@section('title', 'Create Staff Account | Ajoda GRA')
@section('portal_name', 'System Administration Console')
@section('page_eyebrow', 'Access Management')
@section('page_title', 'Create Staff Account')

@section('navigation')
    @include('admin.partials.navigation')
@endsection

@section('content')
    <div class="max-w-4xl">
        <div class="mb-7">
            <h2 class="text-2xl font-bold">
                Add an authorized staff member
            </h2>

            <p class="mt-2 text-stone-600">
                The account will begin in Pending status until an administrator
                activates it.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-5 text-sm text-red-800">
                <p class="font-bold">
                    The staff account could not be created.
                </p>

                <ul class="mt-3 list-disc space-y-1 pl-5">
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
            action="{{ route('admin.staff.store') }}"
            class="rounded-2xl border border-stone-200 bg-white p-7 shadow-sm"
        >
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label
                        for="name"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Full name
                    </label>

                    <input
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autocomplete="name"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                </div>

                <div>
                    <label
                        for="email"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Email address
                    </label>

                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autocomplete="email"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                </div>

                <div>
                    <label
                        for="department_id"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Department
                    </label>

                    <select
                        id="department_id"
                        name="department_id"
                        required
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                        <option value="">
                            Select department
                        </option>

                        @foreach ($departments as $department)
                            <option
                                value="{{ $department->id }}"
                                @selected(
                                    (string) old('department_id')
                                    === (string) $department->id
                                )
                            >
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label
                        for="job_title"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Job title
                    </label>

                    <input
                        id="job_title"
                        name="job_title"
                        value="{{ old('job_title') }}"
                        required
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                </div>

                <div>
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
                        autocomplete="tel"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                </div>

                <div>
                    <label
                        for="employment_date"
                        class="mb-2 block text-sm font-semibold"
                    >
                        Employment date
                    </label>

                    <input
                        id="employment_date"
                        type="date"
                        name="employment_date"
                        value="{{ old('employment_date') }}"
                        class="w-full rounded-xl border border-stone-300 px-4 py-3"
                    >
                </div>
            </div>

            <fieldset class="mt-8 border-t border-stone-200 pt-7">
                <legend class="text-lg font-bold">
                    Operational roles
                </legend>

                <p class="mt-2 text-sm text-stone-500">
                    Select one or more roles approved for this staff member.
                </p>

                <div class="mt-5 grid gap-3 md:grid-cols-2">
                    @forelse ($roles as $role)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-4 transition hover:border-emerald-400 hover:bg-emerald-50/40">
                            <input
                                type="checkbox"
                                name="roles[]"
                                value="{{ $role->name }}"
                                @checked(
                                    in_array(
                                        $role->name,
                                        old('roles', []),
                                        true,
                                    )
                                )
                                class="rounded border-stone-300"
                            >

                            <span class="text-sm font-semibold">
                                {{ $role->name }}
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-red-700 md:col-span-2">
                            No assignable staff roles are available. Run the role
                            and permission seeder.
                        </p>
                    @endforelse
                </div>
            </fieldset>

            <div class="mt-8 flex flex-wrap gap-3">
                <button
                    type="submit"
                    class="rounded-xl bg-stone-900 px-6 py-3 font-bold text-white transition hover:bg-stone-700"
                >
                    Create staff account
                </button>

                <a
                    href="{{ route('admin.staff.index') }}"
                    class="rounded-xl border border-stone-300 px-6 py-3 font-bold transition hover:bg-stone-100"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection