# Guía de Instalación — Sistema Biométrico Casino
## Instalación en PC limpio (Windows 10/11 x64)

---

## REQUISITOS DE HARDWARE
- PC con Windows 10/11 de 64 bits
- Lector de huella DigitalPersona U.are.U 4500
- Puerto USB disponible
- 4 GB RAM mínimo

---

## PASO 1 — XAMPP (Servidor Web + MySQL)

1. Descargar: https://www.apachefriends.org/download.html
   - Versión recomendada: **XAMPP 8.1.x** (PHP 8.1)
2. Instalar en `C:\xampp\`
3. Abrir **XAMPP Control Panel** como Administrador
4. Iniciar **Apache** y **MySQL**
5. Habilitar extensión GD en PHP:
   - Abrir `C:\xampp\php\php.ini`
   - Buscar `;extension=gd`
   - Cambiar a `extension=gd` (quitar el punto y coma)
   - Guardar y reiniciar Apache

---

## PASO 2 — DRIVER DEL LECTOR DE HUELLA

### 2a. Driver oficial HID Global (Non-WBF Legacy)
1. Ir a: https://www.hidglobal.com/drivers/49061
2. Descargar `setup_x64.msi` (Windows 10/11 64-bit)
3. Instalar como Administrador
4. Conectar el lector USB → debe aparecer en:
   Administrador de dispositivos → **Dispositivos biométricos**
   → U.are.U® Fingerprint Reader (SIN ícono de advertencia ⚠️)

### 2b. SDK One Touch for Windows (DLLs COM)
1. Conseguir el instalador "DigitalPersona One Touch for Windows SDK"
   - Versión confirmada: v1.6.x
   - Durante la instalación seleccionar solo: **C/C++**
   - Instala las DLLs COM en:
     `C:\Program Files (x86)\DigitalPersona\Bin\`
   - Registra los objetos COM: DPFPDevX.DPFPCapture, etc.

---

## PASO 3 — PYTHON 32-BIT

**IMPORTANTE: Debe ser Python de 32 bits (no 64 bits)**

1. Ir a: https://www.python.org/downloads/windows/
2. Buscar Python 3.11.x
3. Descargar: **Windows installer (32-bit)**
4. Instalar con las opciones:
   - ✅ Add Python to PATH
   - ✅ Install for all users (opcional)
5. Verificar en CMD:
   ```
   py -3.11-32 --version
   ```
   Debe mostrar: `Python 3.11.x`

6. Instalar dependencias Python:
   ```
   py -3.11-32 -m pip install pywin32 Pillow
   ```

7. Verificar ruta del ejecutable (anotar esta ruta):
   ```
   py -3.11-32 -c "import sys; print(sys.executable)"
   ```
   Ejemplo: `C:\Users\TuUsuario\AppData\Local\Programs\Python\Python311-32\python.exe`

---

## PASO 4 — BASE DE DATOS

1. Abrir: http://localhost/phpmyadmin
2. Crear nueva base de datos: `db_biometrico_casino`
3. Importar el archivo: `db_biometrico_casino.sql`
   (incluido en el ZIP del proyecto)

---

## PASO 5 — PROYECTO WEB

1. Copiar la carpeta `Biometrico_Casino` a:
   `C:\xampp\htdocs\Biometrico_Casino\`

2. Configurar la conexión a BD en:
   `C:\xampp\htdocs\Biometrico_Casino\Lib\Config\conexionSqli.php`
   ```php
   $host     = "localhost";
   $user     = "root";
   $password = "";          // vacío en XAMPP por defecto
   $database = "db_biometrico_casino";
   ```

3. **IMPORTANTE:** Actualizar la ruta de Python en el archivo PHP:
   `C:\xampp\htdocs\Biometrico_Casino\web\huella\capturar_huella.php`
   
   Buscar esta línea y cambiar la ruta al ejecutable Python del PC nuevo:
   ```php
   $PYTHON_EXE = 'C:\\Users\\TuUsuario\\AppData\\Local\\Programs\\Python\\Python311-32\\python.exe';
   ```
   
   Reemplazar `TuUsuario` con el nombre de usuario de Windows del PC nuevo.

---

## PASO 6 — AGENTE DE HUELLA (inicio automático)

El agente debe correr en la sesión del usuario (no como servicio).

1. Abrir CMD como usuario normal (NO administrador)
2. Ejecutar:
   ```
   cd C:\xampp\htdocs\Biometrico_Casino\web\huella\agente
   py -3.11-32 instalar_agente.py
   ```
3. Esto registra el agente en el inicio automático de Windows
4. Verificar que aparece la ventana negra del agente con:
   ```
   [Agente Huella] Escuchando en 127.0.0.1:7842
   ```

---

## PASO 7 — VERIFICACIÓN FINAL

Abrir en el navegador: http://localhost/Biometrico_Casino/web/index.php

✅ Login: admin / 1234
✅ Empleados: capturar huella con el lector conectado
✅ Verificado: poner el dedo → confirmar almuerzo

---

## RESUMEN DE COMPONENTES INSTALADOS

| Componente | Versión | Notas |
|---|---|---|
| Windows | 10/11 x64 | Obligatorio 64-bit |
| XAMPP | 8.1.x | PHP 8.1 con GD habilitado |
| Python | 3.11.x **32-bit** | DEBE ser 32-bit por las DLLs |
| pywin32 | último | pip install pywin32 |
| Pillow | último | pip install Pillow |
| Driver U.are.U | Non-WBF 4.1.x | hidglobal.com/drivers/49061 |
| SDK One Touch | 1.6.x | DLLs COM en Program Files (x86) |

---

## SOLUCIÓN DE PROBLEMAS COMUNES

### "No se detectó ningún lector"
- Verificar que el driver Non-WBF está instalado (no el WBF/Hello)
- El lector debe aparecer en "Dispositivos biométricos", no en "Otros dispositivos"

### "El agente de huella no está corriendo"
- Ejecutar manualmente: `iniciar_agente.bat`
- O reinstalar inicio automático: `py -3.11-32 instalar_agente.py`

### "Python 32-bit no encontrado"
- Actualizar la ruta en `capturar_huella.php` con la ruta correcta del nuevo PC
- Usar: `py -3.11-32 -c "import sys; print(sys.executable)"` para obtenerla

### "imagecreatefromjpeg() no definida" (Error 500)
- Habilitar GD en php.ini: `extension=gd`
- Reiniciar Apache

### Huella no reconoce al empleado
- Verificar que GD está habilitado (mejor comparación)
- Re-registrar la huella del empleado con mejor calidad
- Asegurarse de apoyar el dedo completo y centrado

---

## ARCHIVOS CLAVE DEL PROYECTO

```
Biometrico_Casino/
├── Lib/Config/conexionSqli.php     ← Configurar BD aquí
├── web/huella/
│   ├── capturar_huella.php         ← Actualizar ruta Python aquí
│   ├── capturar_huella.py          ← Script de captura (no modificar)
│   └── agente/
│       ├── agente_huella.py        ← Agente TCP (no modificar)
│       ├── iniciar_agente.bat      ← Inicio manual del agente
│       └── instalar_agente.py      ← Registro inicio automático
└── db_biometrico_casino.sql        ← Importar en phpMyAdmin
```

