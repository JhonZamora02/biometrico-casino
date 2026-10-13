# Configuración del lector DigitalPersona U.are.U 4500
## Sistema Biométrico — Casino | Windows 10/11

---

## ¿Por qué necesito instalar algo?

El lector U.are.U 4500 viene con un driver propietario de DigitalPersona
que **solo funciona con su propio software**. Para usarlo desde una
aplicación web propia necesitamos reemplazar ese driver por uno
universal (`libusb-win32`) que permite acceso directo al hardware.

> Este cambio solo afecta al lector de huella.
> No afecta a ningún otro dispositivo.
> Puede revertirse fácilmente desde el Administrador de dispositivos.

---

## PASO 1 — Instalar Python 3

1. Descarga Python desde: **https://www.python.org/downloads/**
2. En el instalador **marca** `Add Python to PATH`  ← MUY IMPORTANTE
3. Click en "Install Now"
4. Verifica abriendo cmd:
   ```
   python --version
   ```
   Debe mostrar algo como `Python 3.11.x`

---

## PASO 2 — Ejecutar el instalador automático

1. Navega a la carpeta del proyecto:
   ```
   C:\xampp\htdocs\Biometrico_Casino\web\huella\
   ```
2. Haz **doble clic** en `instalar_dependencias.bat`
   (clic derecho → "Ejecutar como administrador")
3. El script instala `pyusb` y `Pillow`, y descarga `Zadig.exe`
4. Zadig se abrirá automáticamente al terminar

---

## PASO 3 — Instalar driver libusb con Zadig

> Zadig es una herramienta oficial y segura: **https://zadig.akeo.ie**

Con el lector **conectado al USB**:

1. En Zadig, ve al menú: **Options → List All Devices**
2. En el desplegable de dispositivos busca:
   ```
   DigitalPersona U.are.U 4500
   ```
   (puede aparecer como "DigitalPersona Fingerprint Reader")

3. Verifica que en el campo del driver destino diga:
   ```
   libusb-win32
   ```
   (usa las flechas ◀ ▶ para cambiar si es necesario)

4. Haz click en **"Replace Driver"**

5. Espera ~30 segundos. Aparecerá: *"Driver installed successfully"*

---

## PASO 4 — Verificar que todo funciona

Abre **cmd** en la carpeta del proyecto y ejecuta:

```bat
cd C:\xampp\htdocs\Biometrico_Casino\web\huella
python probar_lector.py
```

Coloca el dedo en el lector cuando lo pida.

**Si todo está correcto verás:**
```
[OK] pyusb instalado
[OK] Pillow instalado
[OK] Lector detectado: U.are.U 4500
[OK] Lector abierto correctamente
[OK] Captura exitosa!
[OK] Imagen guardada en: prueba_huella.jpg
```

Abre `prueba_huella.jpg` para ver la imagen capturada.

---

## Flujo técnico completo

```
Botón "Capturar huella" (navegador Chrome/Edge)
  │
  ├─→ AJAX GET → capturar_huella.php  (PHP/XAMPP)
  │       │
  │       └─→ exec("python capturar_huella.py")
  │               │
  │               ├─→ pyusb detecta el lector (USB VID=05ba PID=000a)
  │               ├─→ envía comando de captura al sensor
  │               ├─→ espera que el usuario coloque el dedo
  │               ├─→ lee 120,000 bytes de píxeles raw del sensor
  │               ├─→ Pillow convierte a JPEG con mejora de contraste
  │               └─→ JSON con imagen en base64 → stdout
  │
  ├─→ Imagen JPG se muestra en el panel del modal
  │
  └─→ Al guardar el empleado:
        AJAX POST → guardar_huella_img.php
          ├─→ Guarda JPG en: web/huellas_img/huella_{cedula}.jpg
          └─→ Actualiza columna `huella` en la BD
```

---

## Solución de errores comunes

| Error | Causa | Solución |
|-------|-------|----------|
| `pyusb no instalado` | pip no ejecutado | Ejecutar `instalar_dependencias.bat` |
| `Lector NO detectado` | Driver incorrecto | Verificar en Zadig que diga `libusb-win32` |
| `No se pudo abrir el lector` | Otro proceso lo usa | Cerrar software de DigitalPersona si está abierto |
| `Tiempo agotado` | No se detectó el dedo | Colocar bien el dedo y repetir |
| `Python no encontrado` | PATH no configurado | Reinstalar Python marcando "Add to PATH" |
| `disable_functions` (PHP) | php.ini restrictivo | Dejar `disable_functions =` vacío en XAMPP |

---

## ¿Cómo revertir el driver?

Si en algún momento quieres volver al driver original de DigitalPersona:

1. Abrir **Administrador de dispositivos**
2. Buscar el lector bajo "Dispositivos de bus serie universal"
3. Clic derecho **Actualizar controlador**
4. Buscar automáticamente — Windows reinstalará el driver original

---

## Requisitos de PHP (XAMPP)

Verifica en `C:\xampp\php\php.ini`:

```ini
; Dejar vacío para permitir exec()
disable_functions =

; Asegurar que el tiempo de ejecución permite el timeout del lector
max_execution_time = 30
```

Reinicia Apache desde el panel de XAMPP después de cualquier cambio.
