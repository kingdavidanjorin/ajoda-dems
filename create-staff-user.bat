@echo off
setlocal

cd /d "%~dp0"

php artisan dems:create-staff-user %*

endlocal