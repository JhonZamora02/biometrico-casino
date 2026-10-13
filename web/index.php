<!DOCTYPE html>
<html lang="es">

<?php
  /*
   * index.php — Punto de entrada principal del Sistema Biométrico Casino.
   *
   * Rutas normales : index.php?module=X&controller=Y&function=Z
   * Sin parámetros : carga el formulario de registro de asistencia por defecto.
   *
   * Módulos disponibles:
   *   Asistencia / Asistencia / read      → Registro público (inicio)
   *   Empleado   / Empleado   / read      → CRUD de empleados  (requiere login)
   *   Registros  / Registros  / read      → Listado de asistencias (requiere login)
   *   Verificado / Verificado / read      → Pantalla biométrica   (requiere login)
   *   Auth       / Auth       / logout    → Cerrar sesión
   */
  include_once '../View/Partials/Head.php';
  include_once '../Lib/helpers.php';

  // Módulos que requieren sesión activa
  $modulosProtegidos = ['Empleado', 'Registros', 'Verificado'];
  $moduloActual = $_GET['module'] ?? '';

  if (in_array($moduloActual, $modulosProtegidos)) {
      requireLogin();
  }
?>

<body>

  <!-- Barra de navegación -->
  <?php include_once '../View/Partials/Header.php'; ?>

  <!-- Contenido principal -->
  <?php
    if (isset($_GET['module'])) {
        resolve();
    } else {
        // Vista por defecto: formulario de registro de asistencia
        resolve('Asistencia', 'Asistencia', 'read');
    }
  ?>

  <!-- Pie de página -->
  <section class="footer_section">
    <p>&copy; <?php echo date('Y'); ?> &mdash; Sistema Biométrico | Casino</p>
  </section>

  <!-- Scripts -->
  <script src="js/jquery-3.4.1.min.js"></script>
  <script src="js/bootstrap.js"></script>
  <script src="js/datatables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

</body>
</html>
