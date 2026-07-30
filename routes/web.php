<?php

declare(strict_types=1);

use App\Enums\RoleName;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\EnquiryHistoryController;
use App\Http\Controllers\Customer\PaymentHistoryController;
use App\Http\Controllers\Customer\PropertyAllocationController;
use App\Http\Controllers\Customer\PropertyReservationController;
use App\Http\Controllers\Customer\PurchaseProgressController;
use App\Http\Controllers\Customer\CommunicationHistoryController;
use App\Http\Controllers\Customer\InspectionHistoryController;
use App\Http\Controllers\Customer\HandoverHistoryController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ConstructionProgressController as CustomerConstructionProgressController;
use App\Http\Controllers\Staff\CustomerCommunicationController;
use App\Http\Controllers\Staff\ConstructionProgressController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Portal\AccountAccessController;
use App\Http\Controllers\Portal\DashboardRedirectController;
use App\Http\Controllers\Public\EstateLayoutController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\PropertyCatalogueController;
use App\Http\Controllers\Public\PropertyEnquiryController;
use App\Http\Controllers\Staff\EnquiryManagementController;
use App\Http\Controllers\Staff\PaymentManagementController;
use App\Http\Controllers\Staff\PurchaseManagementController;
use App\Http\Controllers\Staff\ReservationManagementController;
use App\Http\Controllers\Staff\StaffDashboardController;
use App\Http\Controllers\Customer\CustomerDocumentController;
use App\Http\Controllers\Staff\PropertyDocumentController;
use App\Http\Controllers\Staff\PropertyInspectionController;
use App\Http\Controllers\Staff\PropertyHandoverController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)
    ->name('home');

Route::get('/health', HealthCheckController::class)
    ->name('health');

Route::get(
    '/properties',
    [PropertyCatalogueController::class, 'index'],
)->name('properties.index');

Route::get(
    '/properties/{house}',
    [PropertyCatalogueController::class, 'show'],
)->name('properties.show');

Route::post(
    '/properties/{house}/enquiries',
    [PropertyEnquiryController::class, 'store'],
)->middleware('throttle:10,1')
    ->name('properties.enquiries.store');

Route::get(
    '/estate-layout',
    EstateLayoutController::class,
)->name('estate.layout');

Route::middleware([
    'auth',
    'auth.session',
    'verified',
])->group(function (): void {
    Route::get(
        '/dashboard',
        DashboardRedirectController::class,
    )->name('dashboard');
});

/*
 * Keep the access-status page outside the active and auth.session
 * middleware so an authenticated inactive user can still see why
 * access has been restricted.
 */
Route::middleware([
    'auth',
    'verified',
])->group(function (): void {
    Route::get(
        '/access-status',
        AccountAccessController::class,
    )->name('access.status');
});

Route::middleware([
    'auth',
    'auth.session',
    'verified',
    'active',
    'customer',
])->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            CustomerDashboardController::class,
        )->name('dashboard');

        Route::get(
            '/communications',
            [CommunicationHistoryController::class, 'index'],
        )->name('communications.index');

        Route::get(
            '/reservations',
            [PropertyReservationController::class, 'index'],
        )->middleware('permission:reservations.view-own')
            ->name('reservations.index');

        Route::get(
            '/notifications',
            [NotificationController::class, 'index'],
        )->name('notifications.index');

        Route::patch(
            '/notifications/read-all',
            [NotificationController::class, 'markAllAsRead'],
        )->name('notifications.read-all');

        Route::patch(
            '/notifications/{notification}/read',
            [NotificationController::class, 'markAsRead'],
        )->name('notifications.read');

        Route::get(
            '/construction',
            [CustomerConstructionProgressController::class, 'index'],
        )->middleware('permission:construction.view-own')
            ->name('construction.index');

        Route::get(
            '/handovers',
            [HandoverHistoryController::class, 'index'],
        )->name('handovers.index');

        Route::post(
            '/properties/{house}/reservations',
            [PropertyReservationController::class, 'store'],
        )->middleware('permission:reservations.create')
            ->name('reservations.store');

        Route::get(
            '/inspections',
            [InspectionHistoryController::class, 'index'],
        )->name('inspections.index');

        Route::get(
            '/allocations',
            [PropertyAllocationController::class, 'index'],
        )->name('allocations.index');

        Route::get(
            '/payments',
            [PaymentHistoryController::class, 'index'],
        )->name('payments.index');

        Route::get(
            '/purchases',
            [PurchaseProgressController::class, 'index'],
        )->name('purchases.index');

        Route::get(
            '/enquiries',
            [EnquiryHistoryController::class, 'index'],
        )->name('enquiries.index');

        Route::get(
            '/documents',
            [CustomerDocumentController::class, 'index'],
        )->name('documents.index');

        Route::get(
            '/documents/{document}/download',
            [CustomerDocumentController::class, 'download'],
        )->name('documents.download');
    });

Route::middleware([
    'auth',
    'auth.session',
    'verified',
    'active',
    'staff',
])->prefix('staff')
    ->name('staff.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            StaffDashboardController::class,
        )->name('dashboard');

        Route::get(
            '/payments',
            [PaymentManagementController::class, 'index'],
        )->middleware('permission:payments.view')
            ->name('payments.index');

        Route::get(
            '/construction',
            [ConstructionProgressController::class, 'index'],
        )->middleware('permission:construction.view|construction.manage')
            ->name('construction.index');

        Route::post(
            '/construction',
            [ConstructionProgressController::class, 'store'],
        )->middleware('permission:construction.manage')
            ->name('construction.store');
        Route::get(
            '/handovers',
            [PropertyHandoverController::class, 'index'],
        )->middleware('permission:handover.manage')
            ->name('handovers.index');

        Route::post(
            '/handovers',
            [PropertyHandoverController::class, 'store'],
        )->middleware('permission:handover.manage')
            ->name('handovers.store');

        Route::patch(
            '/handovers/{handover}/complete',
            [PropertyHandoverController::class, 'complete'],
        )->middleware('permission:handover.manage')
            ->name('handovers.complete');

        Route::get(
            '/inspections',
            [PropertyInspectionController::class, 'index'],
        )->middleware('permission:inspections.view|inspections.manage')
            ->name('inspections.index');

        Route::post(
            '/inspections',
            [PropertyInspectionController::class, 'store'],
        )->middleware('permission:inspections.manage')
            ->name('inspections.store');

        Route::patch(
            '/inspections/{inspection}/complete',
            [PropertyInspectionController::class, 'complete'],
        )->middleware('permission:inspections.manage')
            ->name('inspections.complete');

        Route::post(
            '/purchases/{purchase}/payments',
            [PaymentManagementController::class, 'store'],
        )->middleware('permission:payments.record')
            ->name('payments.store');

        Route::patch(
            '/payments/{payment}/verify',
            [PaymentManagementController::class, 'verify'],
        )->middleware('permission:payments.verify')
            ->name('payments.verify');

        Route::patch(
            '/payments/{payment}/reject',
            [PaymentManagementController::class, 'reject'],
        )->middleware('permission:payments.verify')
            ->name('payments.reject');

        Route::get(
            '/enquiries',
            [EnquiryManagementController::class, 'index'],
        )->middleware('permission:enquiries.view')
            ->name('enquiries.index');

        Route::patch(
            '/enquiries/{enquiry}',
            [EnquiryManagementController::class, 'update'],
        )->middleware('permission:enquiries.manage')
            ->name('enquiries.update');

        Route::get(
            '/reservations',
            [ReservationManagementController::class, 'index'],
        )->middleware('permission:reservations.view')
            ->name('reservations.index');

        Route::patch(
            '/reservations/{reservation}/approve',
            [ReservationManagementController::class, 'approve'],
        )->middleware('permission:reservations.manage')
            ->name('reservations.approve');

        Route::patch(
            '/reservations/{reservation}/reject',
            [ReservationManagementController::class, 'reject'],
        )->middleware('permission:reservations.manage')
            ->name('reservations.reject');

        Route::get(
            '/purchases',
            [PurchaseManagementController::class, 'index'],
        )->middleware('permission:sales.view')
            ->name('purchases.index');

        Route::post(
            '/reservations/{reservation}/purchase',
            [PurchaseManagementController::class, 'store'],
        )->middleware('permission:sales.manage')
            ->name('purchases.store');

        Route::patch(
            '/purchases/{purchase}/confirm',
            [PurchaseManagementController::class, 'confirm'],
        )->middleware('permission:purchases.confirm')
            ->name('purchases.confirm');

        Route::get(
            '/documents',
            [PropertyDocumentController::class, 'index'],
        )->middleware('permission:documents.view|documents.manage')
            ->name('documents.index');

        Route::post(
            '/documents',
            [PropertyDocumentController::class, 'store'],
        )->middleware('permission:documents.manage')
            ->name('documents.store');

        Route::get(
            '/documents/{document}/download',
            [PropertyDocumentController::class, 'download'],
        )->middleware('permission:documents.view|documents.manage')
            ->name('documents.download');

        Route::middleware('permission:messages.manage')
            ->group(function (): void {
                Route::get(
                    '/communications',
                    [CustomerCommunicationController::class, 'index'],
                )->name('communications.index');

                Route::post(
                    '/communications',
                    [CustomerCommunicationController::class, 'store'],
                )->name('communications.store');

                Route::patch(
                    '/communications/{communication}/complete',
                    [CustomerCommunicationController::class, 'complete'],
                )->name('communications.complete');
            });
    });

Route::middleware([
    'auth',
    'auth.session',
    'verified',
    'active',
    'staff',
    'role:' . RoleName::SystemAdministrator->value,
])->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get(
            '/dashboard',
            AdminDashboardController::class,
        )->name('dashboard');

        Route::get(
            '/staff',
            [StaffManagementController::class, 'index'],
        )->middleware('permission:users.view')
            ->name('staff.index');

        Route::get(
            '/staff/create',
            [StaffManagementController::class, 'create'],
        )->middleware('permission:users.manage')
            ->name('staff.create');

        Route::post(
            '/staff',
            [StaffManagementController::class, 'store'],
        )->middleware('permission:users.manage')
            ->name('staff.store');

        Route::get(
            '/staff/{staff}/edit',
            [StaffManagementController::class, 'edit'],
        )->middleware('permission:users.manage')
            ->name('staff.edit');

        Route::put(
            '/staff/{staff}',
            [StaffManagementController::class, 'update'],
        )->middleware('permission:users.manage')
            ->name('staff.update');

        Route::patch(
            '/staff/{staff}/activate',
            [StaffManagementController::class, 'activate'],
        )->middleware('permission:users.manage')
            ->name('staff.activate');

        Route::patch(
            '/staff/{staff}/suspend',
            [StaffManagementController::class, 'suspend'],
        )->middleware('permission:users.manage')
            ->name('staff.suspend');

        Route::post(
            '/staff/{staff}/resend-setup',
            [StaffManagementController::class, 'resendSetup'],
        )->middleware('permission:users.manage')
            ->name('staff.resend-setup');
    });

require __DIR__ . '/settings.php';

// CHECKPOINT 19 ROUTES
Route::middleware([
    'auth',
    'auth.session',
    'verified',
    \App\Http\Middleware\EnsureAccountIsActive::class,
    \App\Http\Middleware\EnsureUserIsStaff::class,
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get(
            '/reports',
            [\App\Http\Controllers\Admin\SystemReportController::class, 'index'],
        )->name('reports.index');

        Route::get(
            '/reports/export',
            [\App\Http\Controllers\Admin\SystemReportController::class, 'export'],
        )->name('reports.export');

        Route::get(
            '/audit-logs',
            [\App\Http\Controllers\Admin\AuditLogController::class, 'index'],
        )->name('audit.index');
    });
// END CHECKPOINT 19 ROUTES

// ADMIN CONTROL CENTRE ROUTES
Route::middleware([
    'auth',
    'auth.session',
    'verified',
    \App\Http\Middleware\EnsureAccountIsActive::class,
    \App\Http\Middleware\EnsureUserIsStaff::class,
])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get(
            '/customers',
            [\App\Http\Controllers\Admin\AdminCustomerController::class, 'index'],
        )->name('customers.index');

        Route::get(
            '/estate-properties',
            [\App\Http\Controllers\Admin\AdminEstatePropertyController::class, 'index'],
        )->name('estate-properties.index');

        Route::get(
            '/access-control',
            [\App\Http\Controllers\Admin\AdminRolePermissionController::class, 'index'],
        )->name('access-control.index');

        Route::get(
            '/system-settings',
            [\App\Http\Controllers\Admin\AdminSystemSettingsController::class, 'index'],
        )->name('system-settings.index');
    });
// END ADMIN CONTROL CENTRE ROUTES