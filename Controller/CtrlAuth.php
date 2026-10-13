<?php

class CtrlAuth {

    /** Muestra el formulario de login */
    public function read() {
        // El login vive fuera del MVC (login.php directo),
        // pero por compatibilidad se puede llamar desde aquí.
        redirect('../web/login.php');
    }

    /** Cierra la sesión del usuario */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header("Location: login.php");
        exit();
    }
}
