<?php
include_once '../DAO/AsistenciaDAO.php';

class CtrlRegistros extends AsistenciaDAO {

    /** Muestra la vista de registros de asistencia */
    public function read() {
        include_once '../View/Registros/FrmRegistros.php';
    }

    /**
     * Devuelve JSON con los registros de asistencia para DataTables.
     * Acepta POST: fecha, area
     */
    public function data() {
        $fecha = $_POST['fecha'] ?? '';
        $area  = $_POST['area']  ?? '';

        $lista = AsistenciaDAO::getInstance()->getRegistros($fecha, $area);

        $array = [];
        $key   = 0;
        while ($row = mysqli_fetch_assoc($lista)) {
            $array['data'][$key]['fecha']    = $row['fecha'];
            $array['data'][$key]['hora']     = $row['hora'];
            $array['data'][$key]['cedula']   = $row['cedula'];
            $array['data'][$key]['nombre']   = $row['nombre'];
            $array['data'][$key]['apellido'] = $row['apellido'];
            $array['data'][$key]['area']     = $row['area'];
            $key++;
        }

        echo json_encode($array);
    }
}
