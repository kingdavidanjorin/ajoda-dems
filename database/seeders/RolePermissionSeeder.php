<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

final class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        $registrar->forgetCachedPermissions();

        foreach (PermissionName::cases() as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission->value,
                'guard_name' => 'web',
            ]);
        }

        foreach (RoleName::cases() as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName->value,
                'guard_name' => 'web',
            ]);
        }

        $rolePermissions = [
            RoleName::Customer->value => [
                PermissionName::DashboardView,
                PermissionName::ProfileManageOwn,
                PermissionName::PropertiesView,
                PermissionName::LayoutView,
                PermissionName::EnquiriesCreate,
                PermissionName::ReservationsCreate,
                PermissionName::ReservationsViewOwn,
                PermissionName::PaymentsViewOwn,
                PermissionName::WorkspaceViewOwn,
                PermissionName::ConstructionViewOwn,
                PermissionName::DocumentsViewOwn,
                PermissionName::MessagesCreate,
                PermissionName::InspectionsRequest,
                PermissionName::InspectionsViewOwn,
                PermissionName::NotificationsViewOwn,
            ],

            RoleName::SalesOfficer->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::LayoutView,
                PermissionName::EnquiriesView,
                PermissionName::EnquiriesManage,
                PermissionName::ReservationsView,
                PermissionName::ReservationsManage,
                PermissionName::PurchasesConfirm,
                PermissionName::SalesView,
                PermissionName::SalesManage,
                PermissionName::AllocationsManage,
                PermissionName::PaymentsView,
                PermissionName::DocumentsView,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::AccountsOfficer->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::SalesView,
                PermissionName::PaymentsView,
                PermissionName::PaymentsRecord,
                PermissionName::PaymentsVerify,
                PermissionName::PaymentsRefund,
                PermissionName::ReceiptsIssue,
                PermissionName::DocumentsView,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::ProjectManager->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::ConstructionView,
                PermissionName::ConstructionManage,
                PermissionName::DocumentsView,
                PermissionName::DocumentsManage,
                PermissionName::MessagesManage,
                PermissionName::InspectionsView,
                PermissionName::InspectionsManage,
                PermissionName::HandoverManage,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::CustomerServiceOfficer->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::EnquiriesView,
                PermissionName::EnquiriesManage,
                PermissionName::ReservationsView,
                PermissionName::SalesView,
                PermissionName::PaymentsView,
                PermissionName::ConstructionView,
                PermissionName::DocumentsView,
                PermissionName::MessagesManage,
                PermissionName::CasesManage,
                PermissionName::InspectionsView,
                PermissionName::InspectionsManage,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::PropertyContentOfficer->value => [
                PermissionName::DashboardView,
                PermissionName::EstatesView,
                PermissionName::EstatesManage,
                PermissionName::PropertiesView,
                PermissionName::PropertiesManage,
                PermissionName::LayoutView,
                PermissionName::LayoutManage,
                PermissionName::ContentManage,
                PermissionName::EnquiriesView,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::DocumentOfficer->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::SalesView,
                PermissionName::PaymentsView,
                PermissionName::DocumentsView,
                PermissionName::DocumentsManage,
                PermissionName::MessagesManage,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::NotificationsManage,
            ],

            RoleName::ExecutiveManagement->value => [
                PermissionName::DashboardView,
                PermissionName::UsersView,
                PermissionName::EstatesView,
                PermissionName::PropertiesView,
                PermissionName::LayoutView,
                PermissionName::EnquiriesView,
                PermissionName::ReservationsView,
                PermissionName::SalesView,
                PermissionName::PaymentsView,
                PermissionName::ConstructionView,
                PermissionName::DocumentsView,
                PermissionName::InspectionsView,
                PermissionName::ReportsView,
                PermissionName::ReportsExport,
                PermissionName::AuditView,
            ],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::findByName($roleName, 'web');

            $role->syncPermissions(
                array_map(
                    static fn (PermissionName $permission): string => $permission->value,
                    $permissions,
                ),
            );
        }

        $systemAdministrator = Role::findByName(
            RoleName::SystemAdministrator->value,
            'web',
        );

        $systemAdministrator->syncPermissions(
            Permission::query()
                ->where('guard_name', 'web')
                ->pluck('name')
                ->all(),
        );

        User::query()
            ->where('account_type', AccountType::Customer->value)
            ->each(
                static function (User $user): void {
                    $user->syncRoles([
                        RoleName::Customer->value,
                    ]);
                },
            );

        $registrar->forgetCachedPermissions();
    }
}
