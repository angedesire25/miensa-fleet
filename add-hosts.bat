@echo off
:: Auto-elevation UAC
net session >nul 2>&1
if %errorlevel% neq 0 (
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

set HOSTS=C:\Windows\System32\drivers\etc\hosts

echo.
echo === MiensaFleet : ajout des entrees hosts ===
echo.

:: Entrées tenant (SANS #laragon magic! pour survivre aux redemarrages Laragon)
findstr /C:"dev.miensa-fleet.test" "%HOSTS%" >nul 2>&1
if errorlevel 1 (
    echo 127.0.0.1      dev.miensa-fleet.test>> "%HOSTS%"
    echo [OK] dev.miensa-fleet.test ajoute
) else (
    echo [--] dev.miensa-fleet.test deja present
)

findstr /C:"geomatos.miensa-fleet.test" "%HOSTS%" >nul 2>&1
if errorlevel 1 (
    echo 127.0.0.1      geomatos.miensa-fleet.test>> "%HOSTS%"
    echo [OK] geomatos.miensa-fleet.test ajoute
) else (
    echo [--] geomatos.miensa-fleet.test deja present
)

findstr /C:"admin.miensa-fleet.test" "%HOSTS%" >nul 2>&1
if errorlevel 1 (
    echo 127.0.0.1      admin.miensa-fleet.test>> "%HOSTS%"
    echo [OK] admin.miensa-fleet.test ajoute
) else (
    echo [--] admin.miensa-fleet.test deja present
)

echo.
echo Fait ! Ces entrees n'ont PAS le tag #laragon magic!
echo Elles survivront aux redemarrages de Laragon.
echo.
echo Testez maintenant :
echo   http://miensa-fleet.test        (panel dev - fallback)
echo   http://dev.miensa-fleet.test    (panel dev - sous-domaine)
echo.
pause
