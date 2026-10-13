<?php
include_once '../DAO/AsistenciaDAO.php';

class CtrlAsistencia extends AsistenciaDAO {

    /** Muestra el formulario público de registro de asistencia (index) */
    public function read() {
        include_once '../View/Asistencia/FrmAsistencia.php';
    }

    /**
     * Registra la asistencia vía AJAX.
     * Recibe POST: cedula
     * Devuelve JSON: { status, mensaje, tipo }
     */
    public function registrar() {
        $cedula    = $_POST['cedula'] ?? '';
        $resultado = AsistenciaDAO::getInstance()->registrarAsistencia($cedula);

        date_default_timezone_set("America/Bogota");
        $hora  = date("g:i A");
        $fecha = date("d/m/Y");

        switch ($resultado) {
            case 'insertado':
                $respuesta = [
                    'status'  => 'success',
                    'mensaje' => "✅ Su asistencia fue registrada a las $hora del $fecha.",
                    'tipo'    => 'success'
                ];
                break;
            case 'actualizado':
                $respuesta = [
                    'status'  => 'success',
                    'mensaje' => "✅ Su asistencia fue actualizada a las $hora del $fecha.",
                    'tipo'    => 'success'
                ];
                break;
            default:
                $respuesta = [
                    'status'  => 'error',
                    'mensaje' => "❌ Su cédula no está registrada. Contacte al administrador.",
                    'tipo'    => 'error'
                ];
        }

        echo json_encode($respuesta);
    }
}
