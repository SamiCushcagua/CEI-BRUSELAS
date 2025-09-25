@echo off
title BookShop - Servidor de Desarrollo
color 0A

echo.
echo ========================================
echo    INICIANDO PROYECTO BOOKSHOP
echo ========================================
echo.

echo [1/6] Verificando XAMPP...
if not exist "C:\xampp\xampp-control.exe" (
    echo ERROR: XAMPP no encontrado en C:\xampp\
    echo Por favor instala XAMPP o ajusta la ruta en el script
    pause
    exit /b 1
)

echo [2/6] Verificando si XAMPP Control Panel ya esta abierto...
tasklist /FI "IMAGENAME eq xampp-control.exe" 2>NUL | find /I /N "xampp-control.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo XAMPP Control Panel ya esta abierto
) else (
    echo Abriendo XAMPP Control Panel...
    start "" "C:\xampp\xampp-control.exe"
    timeout /t 2 /nobreak >nul
)

echo [3/6] Verificando servicios...
sc query "Apache2.4" | find "RUNNING" >nul
if %errorlevel% neq 0 (
    echo Iniciando Apache...
    net start "Apache2.4" 2>nul
    if %errorlevel% neq 0 (
        echo ADVERTENCIA: No se pudo iniciar Apache automaticamente
        echo Por favor inicia Apache manualmente desde XAMPP Control Panel
    )
) else (
    echo Apache ya esta corriendo
)

sc query "mysql" | find "RUNNING" >nul
if %errorlevel% neq 0 (
    echo Iniciando MySQL...
    net start "mysql" 2>nul
    if %errorlevel% neq 0 (
        echo ADVERTENCIA: No se pudo iniciar MySQL automaticamente
        echo Por favor inicia MySQL manualmente desde XAMPP Control Panel
    )
) else (
    echo MySQL ya esta corriendo
)

echo [4/6] Esperando que los servicios esten listos...
timeout /t 3 /nobreak >nul

echo [5/6] Verificando PHP...
php --version >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: PHP no encontrado en el PATH
    echo Por favor agrega C:\xampp\php\ al PATH del sistema
    pause
    exit /b 1
)

echo [6/6] Iniciando Laravel Server...
echo.
echo ========================================
echo    SERVIDOR INICIADO EXITOSAMENTE
echo ========================================
echo.
echo URL: http://localhost:8000
echo Proyecto: BookShop
echo Base de datos: CEI-BOOKSHOP
echo Usuarios Admin disponibles:

echo.
echo Presiona Ctrl+C para detener el servidor
echo.
timeout /t 2 /nobreak >nul
start "" "http://localhost:8000"
php artisan serve

echo.
echo Servidor detenido. Presiona cualquier tecla para cerrar...
pause >nul