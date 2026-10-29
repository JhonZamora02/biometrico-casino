# Sistema Biométrico — Casino

Aplicación web para **controlar la asistencia al almuerzo de los empleados de un casino**, combinando el registro por cédula con la **verificación por huella dactilar** mediante un lector **DigitalPersona U.are.U 4500**.

El flujo es simple: el empleado reserva su almuerzo del día ingresando su cédula y, al momento de reclamarlo, valida su identidad poniendo el dedo en el lector. El personal administrativo gestiona empleados, huellas y consulta el historial desde un panel protegido con login.

---

## Tabla de contenido

- [Características](#características)
- [¿Cómo funciona?](#cómo-funciona)
- [Arquitectura](#arquitectura)
- [Tecnologías](#tecnologías)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Requisitos](#requisitos)
- [Instalación rápida](#instalación-rápida)
- [Uso](#uso)
- [Base de datos](#base-de-datos)
- [Capturas de pantalla](#capturas-de-pantalla)
- [Documentación adicional](#documentación-adicional)
- [Limitaciones conocidas y mejoras sugeridas](#limitaciones-conocidas-y-mejoras-sugeridas)

---

## Características

- **Registro de asistencia público** por número de cédula (pantalla de inicio, sin login).
- **Verificación biométrica** de la reserva de almuerzo: captura la huella, identifica al empleado y confirma que tiene reserva para el día actual.
- **Gestión de empleados (CRUD)**: crear, modificar, eliminar y consultar, con indicador de si el empleado tiene huella registrada.
- **Registro y actualización de huellas** desde el propio formulario de empleados, con vista previa de la imagen capturada.
- **Historial de asistencias** con filtros por fecha y por área, y tabla interactiva con DataTables.
- **Panel protegido por sesión** para los módulos administrativos (Empleados, Registros y Verificado).
- Zona horaria configurada en `America/Bogota`.

## ¿Cómo funciona?

```
1. Registro        El empleado ingresa su cédula en la pantalla de inicio
                   → se guarda su asistencia/reserva del día en la BD.

2. Verificación    En el puesto de entrega, el empleado presiona "Verificar huella"
                   y coloca el dedo en el lector.

3. Identificación  El sistema compara la imagen capturada contra las huellas
                   registradas y localiza al empleado.

4. Confirmación    Si la huella coincide Y el empleado tiene reserva hoy,
                   se muestra la pantalla "¡Disfrute de su almuerzo!".
                   Si no, se informa el motivo (huella no reconocida / sin reserva).
```

### Integración con el lector de huella

Los navegadores no pueden acceder directamente al lector USB, por lo que el sistema usa un **agente local en Python**:

```
Navegador (Chrome/Edge)
   │  AJAX
   ▼
PHP (Apache / XAMPP)  ──── socket TCP 127.0.0.1:7842 ────►  Agente de huella (Python 32-bit)
                                                                   │  COM (DPFPDevX)
                                                                   ▼
                                                      SDK DigitalPersona One Touch
                                                                   │
                                                                   ▼
                                                       Lector U.are.U 4500 (USB)
```

1. PHP envía el comando `CAPTURAR` al agente por el puerto `7842`.
2. El agente captura la muestra mediante el SDK **DigitalPersona One Touch** (objetos COM).
3. Extrae la imagen en escala de grises (300×400 px), mejora contraste y nitidez con Pillow, y la devuelve en **JPEG/base64**.
4. PHP guarda la imagen en `web/huellas_img/` (registro) o la compara contra las guardadas (verificación).

> El agente **debe ejecutarse en la sesión del usuario de Windows** (no como servicio), ya que el SDK necesita acceso interactivo al dispositivo.

## Arquitectura

El proyecto sigue un patrón **MVC con capa DAO**, sin framework, con un enrutador propio basado en parámetros GET:

```
index.php?module=Empleado&controller=Empleado&function=read
              │              │                   │
              │              │                   └─ método a ejecutar
              │              └─ clase  Controller/CtrlEmpleado.php
              └─ módulo (vista en View/Empleado/)
```

| Capa | Carpeta | Responsabilidad |
|------|---------|-----------------|
| Vista | `View/` | Formularios y componentes HTML (Asistencia, Empleado, Registros, Verificado, Partials) |
| Controlador | `Controller/` | Recibe las peticiones, valida y responde (HTML o JSON) |
| DAO | `DAO/` | Acceso a datos y lógica de comparación de huellas |
| Librería | `Lib/` | Conexión a la BD, configuración y funciones auxiliares (`getUrl`, `resolve`, sesión, alertas) |
| Web pública | `web/` | Punto de entrada (`index.php`, `ajax.php`, `login.php`), assets y scripts de huella |

## Tecnologías

| Área | Tecnología |
|------|-----------|
| Backend | PHP 8.x (mysqli) |
| Base de datos | MySQL / MariaDB 10.4+ |
| Servidor | Apache (XAMPP) |
| Frontend | HTML5, Bootstrap 4, jQuery 3.4, DataTables, SweetAlert2 |
| Agente de huella | Python 3.11 **32-bit**, pywin32, Pillow |
| Hardware / SDK | DigitalPersona U.are.U 4500 + One Touch for Windows SDK (COM) |
| Sistema operativo | Windows 10/11 x64 |

## Estructura del proyecto

```
biometrico-casino/
├── Controller/                 # Controladores (CtrlAsistencia, CtrlEmpleado, CtrlRegistros, CtrlVerificado, CtrlAuth)
├── DAO/                        # AsistenciaDAO, EmpleadoDAO
├── Lib/
│   ├── Config/
│   │   ├── configuracion.php   # Credenciales de la BD
│   │   └── conexionSqli.php    # Clase de conexión
│   └── helpers.php             # Enrutador, sesión, alertas, utilidades
├── View/
│   ├── Asistencia/             # Registro por cédula
│   ├── Empleado/               # CRUD de empleados + captura de huella
│   ├── Registros/              # Historial de asistencias
│   ├── Verificado/             # Verificación biométrica y confirmación
│   └── Partials/               # Head, Header, Dashboard
├── web/
│   ├── index.php               # Punto de entrada
│   ├── ajax.php                # Endpoint para peticiones AJAX
│   ├── login.php / logout.php  # Autenticación
│   ├── css/ js/ images/        # Recursos estáticos
│   ├── huellas_img/            # Imágenes de huellas registradas
│   └── huella/
│       ├── agente/             # Agente TCP (agente_huella.py, iniciar_agente.bat, instalar_agente.py)
│       ├── capturar_huella.php # Puente PHP → agente
│       ├── guardar_huella_img.php
│       ├── capturar_huella.py  # Captura por línea de comandos
│       ├── probar_lector.py    # Diagnóstico del lector
│       └── test_agente.php     # Prueba de conexión con el agente
├── SDK-Driver/                 # SDK DigitalPersona (zip)
├── docs/img_tests/             # Capturas de pruebas del sistema
├── db_biometrico_casino.sql    # Script de la base de datos
├── GUIA_INSTALACION.md         # Instalación paso a paso en PC limpio
├── CONFIGURACION.md            # Ruta alternativa de configuración del lector (libusb)
└── Importante.png              # Resumen de los puntos críticos de instalación
```

## Requisitos

**Hardware**
- PC con Windows 10/11 de 64 bits y 4 GB de RAM mínimo
- Lector de huella **DigitalPersona U.are.U 4500**
- Puerto USB disponible

**Software**
- XAMPP 8.1.x (Apache + MySQL + PHP 8.1) con la extensión **GD** habilitada
- Driver oficial U.are.U (HID Global, *Non-WBF*)
- SDK *DigitalPersona One Touch for Windows* (incluido en `SDK-Driver/`)
- Python 3.11 de **32 bits** con `pywin32` y `Pillow`

## Instalación rápida

> La guía completa, con soluciones a errores comunes, está en [`GUIA_INSTALACION.md`](GUIA_INSTALACION.md).

El orden correcto es: **XAMPP → Driver → SDK → Python → BD → Proyecto → Agente**.

1. **Copiar el proyecto** en `C:\xampp\htdocs\Biometrico_Casino\`.
2. **Crear la base de datos** importando `db_biometrico_casino.sql` desde phpMyAdmin.
3. **Configurar la conexión** en `Lib/Config/configuracion.php`:
   ```php
   $servidor  = "localhost";
   $usuario   = "root";
   $clave     = "";
   $puerto    = 3306;
   $baseDatos = "db_biometrico_casino";
   ```
4. **Habilitar GD** en `C:\xampp\php\php.ini` (`extension=gd`) y reiniciar Apache.
5. **Instalar dependencias de Python** (32 bits):
   ```bat
   py -3.11-32 -m pip install pywin32 Pillow
   ```
6. **Registrar e iniciar el agente de huella** (como usuario normal, no administrador):
   ```bat
   cd C:\xampp\htdocs\Biometrico_Casino\web\huella\agente
   py -3.11-32 instalar_agente.py
   ```
   Debe aparecer: `[Agente Huella] Escuchando en 127.0.0.1:7842`.
   > Si usas `iniciar_agente.bat`, ajusta dentro de él la ruta al `python.exe` de tu equipo.
7. **Abrir el sistema:** <http://localhost/Biometrico_Casino/web/index.php>

## Uso

### Acceso al panel administrativo

Ingresa en `web/login.php`. Las credenciales por defecto son de demostración (ver [mejoras sugeridas](#limitaciones-conocidas-y-mejoras-sugeridas)):

| Usuario | Contraseña |
|---------|-----------|
| `admin` | `1234` |
| `empleado` | `abcd` |

### Módulos

| Módulo | Acceso | Descripción |
|--------|--------|-------------|
| **Inicio** (Asistencia) | Público | El empleado ingresa su cédula para registrar su asistencia al almuerzo del día |
| **Empleados** | Login | Alta, edición y eliminación de empleados; captura, actualización y eliminación de huella |
| **Registros** | Login | Historial de asistencias con filtros por fecha y área |
| **Verificado** | Login | Pantalla de verificación por huella y confirmación del almuerzo |

### Registrar la huella de un empleado

1. Ir a **Empleados** → crear o editar un empleado.
2. Con el agente corriendo y el lector conectado, pulsar **👆 Capturar huella**.
3. Colocar el dedo completo y centrado en el lector; se mostrará la vista previa.
4. Guardar. La imagen queda en `web/huellas_img/huella_{cedula}.jpg` y su ruta en la BD.

## Base de datos

Base de datos `db_biometrico_casino` con dos tablas:

| Tabla | Campos | Descripción |
|-------|--------|-------------|
| `empleados` | `cedula` (PK), `nombre`, `apellido`, `correo`, `area`, `huella` | Datos del empleado. `huella` guarda la **ruta relativa** a la imagen JPG (ej. `huellas_img/huella_10000001.jpg`) |
| `asistencias` | `cedula_id_cedula` (FK), `fecha`, `hora` | Registro de asistencia/reserva diaria. Eliminación y actualización en cascada |

El script incluye 5 empleados y 3 asistencias de ejemplo.

## Capturas de pantalla

Las capturas de las pruebas realizadas se encuentran en [`docs/img_tests/`](docs/img_tests/):

| Pantalla | Archivo |
|----------|---------|
| Página principal | `PaginaPrincipal.png` |
| Inicio de sesión | `InicioSesion.png` / `LogeoExitoso.png` |
| Gestión de empleados | `Empleados.png` / `GestionEmpleado.png` |
| Actualizar huella | `ActualizarHuella.png` |
| Registros de asistencia | `RegistrosAsistencia.png` |
| Verificación | `Verificado1.png` |
| Pruebas de captura | `Prueba1.png` … `Prueba4.png` |

## Documentación adicional

- [`GUIA_INSTALACION.md`](GUIA_INSTALACION.md): instalación completa en un PC limpio, con resumen de componentes y solución de problemas.
- [`CONFIGURACION.md`](CONFIGURACION.md): configuración alternativa del lector mediante `libusb-win32` + Zadig + `pyusb`. Es una ruta distinta a la del agente con SDK COM que usa actualmente la aplicación.
- [`Importante.png`](Importante.png): los 3 puntos que cambian entre equipos (ruta de Python, extensión GD y agente como proceso de usuario).

## Limitaciones conocidas y mejoras sugeridas

Este proyecto es funcional y adecuado como prototipo o sistema interno. Antes de un uso en producción se recomienda:

- **Autenticación:** las credenciales están fijas en `login.php`. Migrarlas a una tabla de usuarios con contraseñas cifradas (`password_hash`).
- **Reglas de negocio:** las restricciones de horario (cierre a las 10:00 AM) y de días no laborables (domingos) están **comentadas** en `CtrlVerificado.php`; descomentarlas para activarlas.
- **Comparación de huellas:** la identificación se hace comparando **imágenes** (histograma con GD), no plantillas de minucias. Es suficiente para un entorno controlado, pero menos robusta y segura que el matching nativo del SDK.
- **Privacidad de datos biométricos:** las imágenes de huella se guardan en disco; proteger `web/huellas_img/` (ya incluye `.htaccess`) y cumplir la normativa de protección de datos personales aplicable.
- **Seguridad general:** usar consultas preparadas en lugar de `mysqli_real_escape_string`, y restringir el uso de archivos de prueba (`test_verificador.php`, `test_agente.php`) fuera de desarrollo.
- **Portabilidad:** parametrizar las rutas de Python que hoy están fijas en algunos scripts.

## Licencia y créditos

Proyecto desarrollado por **JhonZamora02**. El SDK de DigitalPersona incluido en `SDK-Driver/` pertenece a sus respectivos propietarios y se rige por su propia licencia (EULA).
