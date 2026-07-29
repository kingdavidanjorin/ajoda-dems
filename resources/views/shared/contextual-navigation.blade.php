@php
    $isSystemAdministrator = auth()->user()?->hasRole(
        \App\Enums\RoleName::SystemAdministrator->value,
    ) ?? false;
@endphp

@if ($isSystemAdministrator)
    @include('admin.partials.navigation')
@else
    @include($staffNavigation ?? 'staff.partials.portal-navigation')
@endif