@echo off

echo.
echo ========================================
echo AJODA DEMS FORTIFY FILE AUDIT
echo ========================================
echo.

for %%F in (
    "app\Actions\Fortify\CreateNewUser.php"
    "app\Actions\Fortify\PasswordValidationRules.php"
    "app\Actions\Fortify\ResetUserPassword.php"
    "app\Actions\Fortify\UpdateUserPassword.php"
    "app\Actions\Fortify\UpdateUserProfileInformation.php"
    "app\Providers\FortifyServiceProvider.php"
    "config\fortify.php"
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