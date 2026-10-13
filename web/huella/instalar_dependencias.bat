@echo off
:: ═══════════════════════════════════════════════════════════════
:: instalar_dependencias.bat
:: Instala todo lo necesario para el lector DigitalPersona U.are.U
:: Ejecutar como ADMINISTRADOR con doble clic
:: ═══════════════════════════════════════════════════════════════

title Instalador - Sistema Biometrico Casino
color 0A
echo.
echo  ╔══════════════════════════════════════════════════════╗
echo  ║   INSTALADOR - Sistema Biometrico Casino             ║
echo  ║   Lector DigitalPersona U.are.U 4500 / 4000B         ║
echo  ╚══════════════════════════════════════════════════════╝
echo.

:: ── Verificar Python ───────────────────────────────────────────
echo [1/4] Verificando Python...
python --version >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  !! Python NO encontrado.
    echo  !! Descarga e instala Python 3 desde:
    echo  !!   https://www.python.org/downloads/
    echo  !! IMPORTANTE: marca la casilla "Add Python to PATH"
    echo.
    start https://www.python.org/downloads/
    pause
    exit /b 1
)
python --version
echo  OK - Python encontrado.
echo.

:: ── Instalar pyusb ─────────────────────────────────────────────
echo [2/4] Instalando pyusb...
pip install pyusb --quiet
if %ERRORLEVEL% NEQ 0 (
    echo  !! Error instalando pyusb. Intenta manualmente:
    echo  !!   pip install pyusb
) else (
    echo  OK - pyusb instalado.
)
echo.

:: ── Instalar Pillow ────────────────────────────────────────────
echo [3/4] Instalando Pillow (procesamiento de imagen)...
pip install Pillow --quiet
if %ERRORLEVEL% NEQ 0 (
    echo  !! Error instalando Pillow. Intenta manualmente:
    echo  !!   pip install Pillow
) else (
    echo  OK - Pillow instalado.
)
echo.

:: ── Descargar Zadig ────────────────────────────────────────────
echo [4/4] Descargando Zadig (instalador de driver USB)...
set ZADIG_URL=https://github.com/pbatard/zadig/releases/download/v2.9/zadig-2.9.exe
set ZADIG_DEST=%USERPROFILE%\Downloads\zadig.exe

curl -L -o "%ZADIG_DEST%" "%ZADIG_URL%" --silent --show-error
if %ERRORLEVEL% NEQ 0 (
    echo  !! No se pudo descargar Zadig automaticamente.
    echo  !! Descargalo manualmente desde: https://zadig.akeo.ie
    start https://zadig.akeo.ie
) else (
    echo  OK - Zadig descargado en: %ZADIG_DEST%
)
echo.

:: ── Resumen ────────────────────────────────────────────────────
echo  ╔══════════════════════════════════════════════════════╗
echo  ║   PASO FINAL - Instalar driver con Zadig             ║
echo  ╠══════════════════════════════════════════════════════╣
echo  ║  1. Conecta el lector U.are.U al puerto USB          ║
echo  ║  2. Abre: %USERPROFILE%\Downloads\zadig.exe         
echo  ║  3. Menu: Options → List All Devices                 ║
echo  ║  4. Selecciona "DigitalPersona U.are.U 4500"         ║
echo  ║     (o el modelo que tengas)                         ║
echo  ║  5. En el desplegable del driver elige: libusb-win32 ║
echo  ║  6. Click: "Replace Driver"  (espera ~30 segundos)   ║
echo  ║  7. Listo - ya puedes usar el sistema                ║
echo  ╚══════════════════════════════════════════════════════╝
echo.

:: Abrir Zadig automáticamente si se descargó
if exist "%ZADIG_DEST%" (
    echo  Abriendo Zadig...
    start "" "%ZADIG_DEST%"
)

echo.
pause
