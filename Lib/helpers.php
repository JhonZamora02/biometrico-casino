<?php

date_default_timezone_set("America/Bogota");

/* ── Sesión ─────────────────────────────────────────────────────── */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function requireLogin() {
    startSession();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }
}

function logout() {
    startSession();
    session_destroy();
    header("Location: login.php");
    exit();
}

/* ── Routing MVC ─────────────────────────────────────────────────── */
function redirect($url) {
    echo "<script type='text/javascript'>window.location.href='$url'</script>";
}

function dd($var) {
    echo '<pre>';
    die(print_r($var));
}

/**
 * Genera URLs del tipo:
 *   index.php?module=X&controller=Y&function=Z
 *   ajax.php?module=X&controller=Y&function=Z  (si $ajax=true)
 */
function getUrl($modulo, $controlador, $funcion, $parametros = false, $ajax = false) {
    $pagina = $ajax ? "ajax" : "index";
    $url    = "$pagina.php?module=$modulo&controller=$controlador&function=$funcion";
    if ($parametros) {
        foreach ($parametros as $indice => $valor) {
            $url .= "&$indice=$valor";
        }
    }
    return $url;
}

/**
 * Resuelve el controlador a partir de los parámetros GET.
 * Los controladores viven en ../Controller/Ctrl{controller}.php
 */
function resolve($module = FALSE, $controller = FALSE, $function = FALSE) {
    if ($module == FALSE) {
        $module     = $_GET['module'];
        $controller = $_GET['controller'];
        $function   = $_GET['function'];
    }

    $ctrlFile = '../Controller/Ctrl' . $controller . '.php';

    if (file_exists($ctrlFile)) {
        include_once $ctrlFile;
        $nombreClase = 'Ctrl' . $controller;
        $objClase    = new $nombreClase();
        if (method_exists($objClase, $function)) {
            $objClase->$function();
        } else {
            echo "No existe la función: $function";
        }
    } else {
        echo "No existe el controlador: $ctrlFile";
    }
}

/* ── Alertas ─────────────────────────────────────────────────────── */
function messageSweetAlert($title, $text, $type, $colorBtn, $url = null) {
    echo "
        <script>
            Swal.fire({
                title: '" . $title . "',
                text: '"  . $text  . "',
                icon: '"  . $type  . "',
                confirmButtonText: 'Aceptar',
                confirmButtonColor: '" . $colorBtn . "'
            }).then(() => {
                window.location.href = '" . $url . "';
            });
        </script>";
}

/* ── Fechas ──────────────────────────────────────────────────────── */
function fechaActual() {
    $f = getdate();
    ($f['seconds'] < 10) ? $f['seconds'] = '0' . $f['seconds'] : '';
    ($f['minutes'] < 10) ? $f['minutes'] = '0' . $f['minutes'] : '';
    return $f['mday'] . " " . monthToString($f['mon'] - 1) . " " .
           $f['year'] . " a las " . $f['hours'] . ":" . $f['minutes'] . ":" . $f['seconds'];
}
