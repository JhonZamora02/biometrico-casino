<?php
// Asegurar que la sesión esté activa para mostrar el usuario
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!-- Barra de navegación superior del sistema biométrico -->
<header class="site-header">
  <div class="container-fluid">
    <nav class="navbar navbar-expand-lg navbar-dark">

      <!-- Logo / nombre del sistema -->
      <a class="navbar-brand" href="<?php echo getUrl('Asistencia','Asistencia','read'); ?>">
        🏢 Sistema Biométrico — Casino
      </a>

      <!-- Botón colapso móvil -->
      <button class="navbar-toggler" type="button"
              data-toggle="collapse" data-target="#navPrincipal"
              aria-controls="navPrincipal" aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Ítems del menú -->
      <div class="collapse navbar-collapse" id="navPrincipal">
        <ul class="navbar-nav ml-auto">

          <li class="nav-item">
            <a class="nav-link"
               href="<?php echo getUrl('Asistencia','Asistencia','read'); ?>">
              🏠 Inicio
            </a>
          </li>

          <?php if (isset($_SESSION['usuario'])): ?>
          <li class="nav-item">
            <a class="nav-link"
               href="<?php echo getUrl('Empleado','Empleado','read'); ?>">
              👤 Empleados
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link"
               href="<?php echo getUrl('Registros','Registros','read'); ?>">
              📋 Registros
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link"
               href="<?php echo getUrl('Verificado','Verificado','read'); ?>">
              🔍 Verificado
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link text-warning font-weight-bold"
               href="index.php?module=Auth&controller=Auth&function=logout">
              🔒 Cerrar sesión (<?php echo htmlspecialchars($_SESSION['usuario']); ?>)
            </a>
          </li>
          <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="login.php">🔑 Iniciar sesión</a>
          </li>
          <?php endif; ?>

        </ul>
      </div>

    </nav>
  </div>
</header>
