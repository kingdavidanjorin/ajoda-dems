@echo off
echo.
echo ========================================
echo AJODA DEMS PORTAL FILE AUDIT
echo ========================================
echo.

for %%F in (
    "routes\web.php"
    "bootstrap\app.php"
    "app\Enums\RoleName.php"
    "app\Http\Middleware\EnsureAccountIsActive.php"
    "app\Http\Middleware\EnsureUserIsCustomer.php"
    "app\Http\Middleware\EnsureUserIsStaff.php"
    "app\Http\Controllers\Portal\DashboardRedirectController.php"
    "app\Http\Controllers\Portal\AccountAccessController.php"
    "app\Http\Controllers\Customer\CustomerDashboardController.php"
    "app\Http\Controllers\Staff\StaffDashboardController.php"
    "app\Http\Controllers\Admin\AdminDashboardController.php"
    "app\Console\Commands\DemsMakeAdmin.php"
    "app\Http\Controllers\Admin\StaffManagementController.php"
    "app\Http\Requests\Admin\StoreStaffRequest.php"
    "app\Http\Requests\Admin\UpdateStaffRequest.php"
    "resources\views\layouts\portal.blade.php"
    "resources\views\portal\access-status.blade.php"
    "resources\views\customer\dashboard.blade.php"
    "resources\views\staff\dashboard.blade.php"
    "resources\views\admin\dashboard.blade.php"
    "resources\views\admin\partials\navigation.blade.php"
    "resources\views\admin\staff\index.blade.php"
    "resources\views\admin\staff\create.blade.php"
    "resources\views\admin\staff\edit.blade.php"
    "tests\Feature\DashboardTest.php"
    "tests\Feature\Portal\PortalAccessTest.php"
    "tests\Feature\Admin\MakeAdminCommandTest.php"
    "tests\Feature\Admin\StaffManagementTest.php"
) do (
    if exist "%%~F" (
        echo [OK]      %%~F
    ) else (
        echo [MISSING] %%~F
    )
)

echo.
echo ========================================
echo AUDIT COMPLETE
echo ========================================