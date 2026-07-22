<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionName: string
{
    case DashboardView = 'dashboard.view';
    case ProfileManageOwn = 'profile.manage-own';

    case UsersView = 'users.view';
    case UsersManage = 'users.manage';
    case RolesManage = 'roles.manage';
    case DepartmentsManage = 'departments.manage';

    case EstatesView = 'estates.view';
    case EstatesManage = 'estates.manage';

    case PropertiesView = 'properties.view';
    case PropertiesManage = 'properties.manage';

    case LayoutView = 'layout.view';
    case LayoutManage = 'layout.manage';

    case ContentManage = 'content.manage';

    case EnquiriesCreate = 'enquiries.create';
    case EnquiriesView = 'enquiries.view';
    case EnquiriesManage = 'enquiries.manage';

    case ReservationsCreate = 'reservations.create';
    case ReservationsViewOwn = 'reservations.view-own';
    case ReservationsView = 'reservations.view';
    case ReservationsManage = 'reservations.manage';

    case SalesView = 'sales.view';
    case SalesManage = 'sales.manage';

    case PurchasesConfirm = 'purchases.confirm';
    case AllocationsManage = 'allocations.manage';

    case PaymentsViewOwn = 'payments.view-own';
    case PaymentsView = 'payments.view';
    case PaymentsRecord = 'payments.record';
    case PaymentsVerify = 'payments.verify';
    case PaymentsRefund = 'payments.refund';

    case ReceiptsIssue = 'receipts.issue';

    case WorkspaceViewOwn = 'workspace.view-own';

    case ConstructionViewOwn = 'construction.view-own';
    case ConstructionView = 'construction.view';
    case ConstructionManage = 'construction.manage';

    case DocumentsViewOwn = 'documents.view-own';
    case DocumentsView = 'documents.view';
    case DocumentsManage = 'documents.manage';

    case MessagesCreate = 'messages.create';
    case MessagesManage = 'messages.manage';

    case CasesManage = 'cases.manage';

    case InspectionsRequest = 'inspections.request';
    case InspectionsViewOwn = 'inspections.view-own';
    case InspectionsView = 'inspections.view';
    case InspectionsManage = 'inspections.manage';

    case HandoverManage = 'handover.manage';

    case NotificationsViewOwn = 'notifications.view-own';
    case NotificationsManage = 'notifications.manage';

    case ReportsView = 'reports.view';
    case ReportsExport = 'reports.export';

    case AuditView = 'audit.view';
    case SettingsManage = 'settings.manage';
}
