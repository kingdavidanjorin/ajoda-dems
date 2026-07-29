<?php

declare(strict_types=1);

return [
    [
        'label' => 'Dashboard',
        'description' => 'View your assigned staff workspace.',
        'route' => 'staff.dashboard',
        'active' => 'staff.dashboard',
        'permission' => 'dashboard.view',
        'show_on_dashboard' => false,
    ],

    [
        'label' => 'Property enquiries',
        'description' => 'Review and manage enquiries from prospective buyers.',
        'route' => 'staff.enquiries.index',
        'active' => 'staff.enquiries.*',
        'permission' => 'enquiries.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Reservations',
        'description' => 'Review and manage property reservations.',
        'route' => 'staff.reservations.index',
        'active' => 'staff.reservations.*',
        'permission' => 'reservations.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Purchases',
        'description' => 'Review property purchases and sales progress.',
        'route' => 'staff.purchases.index',
        'active' => 'staff.purchases.*',
        'permission' => 'sales.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Payments',
        'description' => 'Review recorded customer payments.',
        'route' => 'staff.payments.index',
        'active' => 'staff.payments.*',
        'permission' => 'payments.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Documents',
        'description' => 'Review customer and property documents.',
        'route' => 'staff.documents.index',
        'active' => 'staff.documents.*',
        'permission' => 'documents.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Properties',
        'description' => 'Review estates and available properties.',
        'route' => 'properties.index',
        'active' => 'properties.*',
        'permission' => 'properties.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Reports',
        'description' => 'Review operational and financial reports.',
        'route' => 'staff.reports.index',
        'active' => 'staff.reports.*',
        'permission' => 'reports.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Construction',
        'description' => 'Record and review property construction progress.',
        'route' => 'staff.construction.index',
        'active' => 'staff.construction.*',
        'permission' => 'construction.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Handovers',
        'description' => 'Schedule and complete property handovers.',
        'route' => 'staff.handovers.index',
        'active' => 'staff.handovers.*',
        'permission' => 'handover.manage',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Inspections',
        'description' => 'Schedule and manage customer property inspections.',
        'route' => 'staff.inspections.index',
        'active' => 'staff.inspections.*',
        'permission' => 'inspections.view',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Communications',
        'description' => 'Record customer interactions and manage follow-ups.',
        'route' => 'staff.communications.index',
        'active' => 'staff.communications.*',
        'permission' => 'messages.manage',
        'show_on_dashboard' => true,
    ],

    [
        'label' => 'Notifications',
        'description' => 'Review and manage system notifications.',
        'route' => 'staff.notifications.index',
        'active' => 'staff.notifications.*',
        'permission' => 'notifications.manage',
        'show_on_dashboard' => true,
    ],
];
