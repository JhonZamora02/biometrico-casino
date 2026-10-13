<?php
include_once '../DAO/EmpleadoDAO.php';

class CtrlEmpleado extends EmpleadoDAO {

    public function read() {
        include_once '../View/Empleado/FrmEmpleado.php';
    }

    /** JSON para DataTables */
    public function data() {
        $array = [];
        $lista = $this->getAll();
        $key   = 0;
        while ($row = mysqli_fetch_assoc($lista)) {
            $huellaBadge = $row['tiene_huella']
                ? '<span class="badge badge-success">✅ Registrada</span>'
                : '<span class="badge badge-secondary">Sin huella</span>';

            $array['data'][$key] = [
                'cedula'       => $row['cedula'],
                'nombre'       => $row['nombre'],
                'apellido'     => $row['apellido'],
                'correo'       => $row['correo'],
                'area'         => $row['area'],
                'tiene_huella' => $row['tiene_huella'],
                'huella_badge' => $huellaBadge,
                'acciones'     => ''
            ];
            $key++;
        }
        echo json_encode($array);
    }

    /** Busca empleado por cédula → JSON */
    public function buscar() {
        $cedula   = $_POST['cedula'] ?? '';
        $empleado = EmpleadoDAO::getInstance()->getByCedula($cedula);
        echo json_encode($empleado ?: []);
    }

    /** Registrar nuevo empleado (con template de huella opcional) */
    public function add() {
        $cedula   = $_POST['cedula']   ?? '';
        $nombre   = $_POST['nombre']   ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $correo   = $_POST['correo']   ?? '';
        $area     = $_POST['area']     ?? '';
        $huella   = $_POST['huella']   ?? '';   // base64 del template ISO

        $rs = EmpleadoDAO::getInstance()->addEmpleado(
            $cedula, $nombre, $apellido, $correo, $area, $huella
        );

        if ($rs == 1) {
            $msgHuella = $huella ? ' con huella registrada' : ' (sin huella, puede agregarla luego)';
            messageSweetAlert(
                'Registro Exitoso',
                "Empleado $nombre registrado correctamente$msgHuella.",
                'success', '#28a745',
                getUrl('Empleado', 'Empleado', 'read')
            );
        }
    }

    /** Modificar datos del empleado (no toca la huella) */
    public function update() {
        $cedula   = $_POST['cedula']   ?? '';
        $nombre   = $_POST['nombre']   ?? '';
        $apellido = $_POST['apellido'] ?? '';
        $correo   = $_POST['correo']   ?? '';
        $area     = $_POST['area']     ?? '';

        $rs = EmpleadoDAO::getInstance()->updateEmpleado(
            $cedula, $nombre, $apellido, $correo, $area
        );
        if ($rs == 1) {
            messageSweetAlert(
                'Modificación Exitosa',
                'Los datos del empleado han sido actualizados.',
                'success', '#ffc107',
                getUrl('Empleado', 'Empleado', 'read')
            );
        }
    }

    /**
     * Actualizar SOLO la huella de un empleado existente.
     * Recibe POST: cedula, huella (base64 ISO template)
     * Devuelve JSON para respuesta AJAX.
     */
    public function updateHuella($cedula = null, $huella = null) {
        $cedula = $cedula ?? ($_POST['cedula'] ?? '');
        $huella = $huella ?? ($_POST['huella'] ?? '');

        if (!$cedula || !$huella) {
            echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $empleado = EmpleadoDAO::getInstance()->getByCedula($cedula);
        if (!$empleado) {
            echo json_encode(['ok' => false, 'error' => 'Empleado no encontrado']);
            return;
        }

        EmpleadoDAO::getInstance()->updateHuella($cedula, $huella);
        echo json_encode(['ok' => true, 'mensaje' => 'Huella actualizada correctamente']);
    }

    /** Eliminar empleado y sus asistencias */
    public function delete() {
        $cedula = $_POST['cedula'] ?? '';
        $rs     = EmpleadoDAO::getInstance()->deleteEmpleado($cedula);
        if ($rs == 1) {
            messageSweetAlert(
                'Eliminado',
                'El empleado y sus registros han sido eliminados.',
                'success', '#dc3545',
                getUrl('Empleado', 'Empleado', 'read')
            );
        }
    }
}
