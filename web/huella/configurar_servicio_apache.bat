@echo off
:: ================================================================
:: configurar_servicio_apache.bat
:: Configura el servicio Apache2.4 para correr como el usuario actual
:: Ejecutar como ADMINISTRADOR con doble clic
:: ================================================================
title Configurar Apache - Sistema Biometrico Casino
color 0A

echo.
echo  Configurando servicio Apache para acceso al lector biometrico...
echo.

:: Obtener nombre del usuario actual
for /f "tokens=*" %%U in ('whoami') do set USUARIO=%%U
echo  Usuario actual: %USUARIO%
echo.

:: Solicitar contraseña
set /p PASSWORD="  Ingresa tu contrasena de Windows: "
echo.

:: Detener Apache si está corriendo
echo  [1/3] Deteniendo Apache...
net stop Apache2.4 2>nul
sc stop Apache2.4 2>nul

:: Cambiar el usuario del servicio
echo  [2/3] Configurando servicio con usuario: %USUARIO%
sc config Apache2.4 obj= "%USUARIO%" password= "%PASSWORD%"

if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  [!!] Error configurando el servicio.
    echo       Asegurate de ejecutar este .bat como Administrador.
    pause
    exit /b 1
)

:: Reiniciar Apache
echo  [3/3] Iniciando Apache...
net start Apache2.4
sc start Apache2.4 2>nul

echo.
echo  ============================================================
echo   Listo! Apache ahora corre como: %USUARIO%
echo   El lector de huella deberia funcionar desde el navegador.
echo  ============================================================
echo.
echo  Abre el sistema y prueba: Empleados - Capturar huella
echo.
pause
