<?php
include_once '../Lib/Config/conexionSqli.php';

class EmpleadoDAO extends Connection {

    private static $instance = NULL;

    public static function getInstance(): EmpleadoDAO {
        if (self::$instance == NULL)
            self::$instance = new EmpleadoDAO();
        return self::$instance;
    }

    public function getAll() {
        $sql = "SELECT cedula, nombre, apellido, correo, area,
                       IF(huella != '' AND huella IS NOT NULL, 1, 0) AS tiene_huella
                FROM empleados ORDER BY nombre";
        return $this->execute($sql);
    }

    public function getByCedula($cedula) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        $sql    = "SELECT cedula, nombre, apellido, correo, area,
                          IF(huella != '' AND huella IS NOT NULL, 1, 0) AS tiene_huella
                   FROM empleados WHERE cedula = '$cedula'";
        return mysqli_fetch_assoc($this->execute($sql));
    }

    public function getHuella($cedula) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        $row    = mysqli_fetch_assoc($this->execute(
            "SELECT huella FROM empleados WHERE cedula = '$cedula'"
        ));
        return $row ? $row['huella'] : null;
    }

    public function addEmpleado($cedula, $nombre, $apellido, $correo, $area, $huella = '') {
        $cedula   = mysqli_real_escape_string($this->getConnect(), $cedula);
        $nombre   = mysqli_real_escape_string($this->getConnect(), $nombre);
        $apellido = mysqli_real_escape_string($this->getConnect(), $apellido);
        $correo   = mysqli_real_escape_string($this->getConnect(), $correo);
        $area     = mysqli_real_escape_string($this->getConnect(), $area);
        $huella   = mysqli_real_escape_string($this->getConnect(), $huella);
        $this->execute(
            "INSERT INTO empleados (cedula, nombre, apellido, correo, area, huella)
             VALUES ('$cedula','$nombre','$apellido','$correo','$area','$huella')"
        );
        return 1;
    }

    public function updateEmpleado($cedula, $nombre, $apellido, $correo, $area) {
        $cedula   = mysqli_real_escape_string($this->getConnect(), $cedula);
        $nombre   = mysqli_real_escape_string($this->getConnect(), $nombre);
        $apellido = mysqli_real_escape_string($this->getConnect(), $apellido);
        $correo   = mysqli_real_escape_string($this->getConnect(), $correo);
        $area     = mysqli_real_escape_string($this->getConnect(), $area);
        $this->execute(
            "UPDATE empleados SET nombre='$nombre', apellido='$apellido',
             correo='$correo', area='$area' WHERE cedula='$cedula'"
        );
        return 1;
    }

    public function updateHuella($cedula, $huella) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        $huella = mysqli_real_escape_string($this->getConnect(), $huella);
        $this->execute("UPDATE empleados SET huella='$huella' WHERE cedula='$cedula'");
        return 1;
    }

    public function deleteEmpleado($cedula) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        $this->execute("DELETE FROM asistencias WHERE cedula_id_cedula = '$cedula'");
        $this->execute("DELETE FROM empleados WHERE cedula = '$cedula'");
        return 1;
    }
}
