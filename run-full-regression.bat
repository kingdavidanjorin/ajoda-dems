@echo off
setlocal

cd /d "%~dp0"

echo.
echo ============================================================
echo AJODA DEMS - FULL SYSTEM REGRESSION TEST
echo ============================================================
echo.

echo [1] Checking Laravel application boot...
php artisan about
if errorlevel 1 goto :failed

echo.
echo [2] Clearing cached application state...
php artisan optimize:clear
if errorlevel 1 goto :failed

echo.
echo [3] Checking migration status...
php artisan migrate:status
if errorlevel 1 goto :failed

echo.
echo [4] Creating route audit...
if not exist storage\logs mkdir storage\logs
php artisan route:list > storage\logs\route-audit.txt
if errorlevel 1 goto :failed

echo.
echo [5] Checking PHP code formatting...
call vendor\bin\pint --test
if errorlevel 1 goto :failed

echo.
echo [6] Building frontend assets...
call npm run build
if errorlevel 1 goto :failed

echo.
echo [7] Running complete automated test suite...
php artisan test
if errorlevel 1 goto :failed

echo.
echo ============================================================
echo FULL REGRESSION PASSED
echo ============================================================
exit /b 0

:failed
echo.
echo ============================================================
echo REGRESSION FAILED
echo Review the first error displayed above.
echo ============================================================
exit /b 1