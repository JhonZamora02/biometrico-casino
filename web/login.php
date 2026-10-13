<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Sistema Biométrico Casino</title>

  <!-- Bootstrap 4 -->
  <link rel="stylesheet" href="css/bootstrap.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }
    .login-card {
      background: rgba(255,255,255,0.97);
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.4);
      padding: 45px 40px;
      width: 380px;
      text-align: center;
    }
    .login-card h2 {
      font-size: 1.4rem;
      font-weight: 700;
      color: #1a1a2e;
      margin-bottom: 8px;
    }
    .login-card p.subtitle {
      font-size: .85rem;
      color: #777;
      margin-bottom: 30px;
    }
    .login-card .icon {
      font-size: 3rem;
      margin-bottom: 15px;
    }
    .form-control {
      border-radius: 8px;
      padding: 10px 14px;
      font-size: .95rem;
    }
    .form-control:focus {
      border-color: #0f3460;
      box-shadow: 0 0 0 .2rem rgba(15,52,96,.25);
    }
    .btn-login {
      background: linear-gradient(90deg, #0f3460, #16213e);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 11px;
      font-size: 1rem;
      font-weight: 600;
      width: 100%;
      margin-top: 8px;
      transition: opacity .3s;
    }
    .btn-login:hover { opacity: .88; color: #fff; }
    .back-link {
      display: inline-block;
      margin-top: 18px;
      color: #0f3460;
      font-size: .88rem;
      text-decoration: none;
      font-weight: 600;
    }
    .back-link:hover { text-decoration: underline; }
    .alert { border-radius: 8px; font-size: .9rem; }
  </style>
</head>
<body>

<?php
session_start();

// Si ya está autenticado, redirigir al panel
if (isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $usuario    = trim($_POST['usuario']    ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    /*
     * Credenciales válidas.
     * En producción esto debe consultarse desde la tabla `usuario` de la BD.
     * Por compatibilidad con el proyecto work_luch se mantiene el array:
     */
    $usuariosValidos = [
        'admin'    => '1234',
        'empleado' => 'abcd'
    ];

    if (isset($usuariosValidos[$usuario]) && $usuariosValidos[$usuario] === $contrasena) {
        $_SESSION['usuario'] = $usuario;
        header("Location: index.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos. Inténtelo de nuevo.";
    }
}
?>

<div class="login-card">
  <div class="icon">🏢</div>
  <h2>Sistema Biométrico</h2>
  <p class="subtitle">Casino — Panel de administración</p>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-group text-left">
      <label for="usuario">👤 Usuario</label>
      <input type="text" name="usuario" id="usuario"
             class="form-control" placeholder="Ingrese su usuario" required
             value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
    </div>
    <div class="form-group text-left">
      <label for="contrasena">🔒 Contraseña</label>
      <input type="password" name="contrasena" id="contrasena"
             class="form-control" placeholder="Ingrese su contraseña" required>
    </div>
    <button type="submit" name="login" class="btn-login">Iniciar sesión</button>
  </form>

  <a href="index.php" class="back-link">← Volver al inicio</a>
</div>

</body>
</html>
