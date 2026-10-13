<?php
include_once '../Lib/Config/conexionSqli.php';

class AsistenciaDAO extends Connection {

    private static $instance = NULL;

    public static function getInstance(): AsistenciaDAO {
        if (self::$instance == NULL)
            self::$instance = new AsistenciaDAO();
        return self::$instance;
    }

    public function registrarAsistencia($cedula) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        $check  = $this->execute("SELECT cedula FROM empleados WHERE cedula = '$cedula'");
        if (mysqli_num_rows($check) === 0) return 'no_existe';

        date_default_timezone_set("America/Bogota");
        $fecha = date("Y-m-d");
        $hora  = date("H:i:s");

        $existe = $this->execute(
            "SELECT cedula_id_cedula FROM asistencias
             WHERE cedula_id_cedula = '$cedula' AND fecha = '$fecha'"
        );

        if (mysqli_num_rows($existe) > 0) {
            $this->execute("UPDATE asistencias SET hora = '$hora'
                            WHERE cedula_id_cedula = '$cedula' AND fecha = '$fecha'");
            return 'actualizado';
        } else {
            $this->execute("INSERT INTO asistencias (cedula_id_cedula, fecha, hora)
                            VALUES ('$cedula', '$fecha', '$hora')");
            return 'insertado';
        }
    }

    public function getRegistros($fecha = '', $area = '') {
        $sql = "SELECT a.fecha, a.hora, e.cedula, e.nombre, e.apellido, e.area
                FROM asistencias a
                INNER JOIN empleados e ON a.cedula_id_cedula = e.cedula
                WHERE 1=1";
        if ($fecha != '') {
            $fecha = mysqli_real_escape_string($this->getConnect(), $fecha);
            $sql  .= " AND a.fecha = '$fecha'";
        }
        if ($area != '') {
            $area = mysqli_real_escape_string($this->getConnect(), $area);
            $sql .= " AND e.area LIKE '%$area%'";
        }
        $sql .= " ORDER BY a.fecha DESC, a.hora DESC";
        return $this->execute($sql);
    }

    /** Verifica si el empleado tiene reserva HOY. Retorna array | null */
    public function verificarReservaHoy($cedula) {
        $cedula = mysqli_real_escape_string($this->getConnect(), $cedula);
        date_default_timezone_set("America/Bogota");
        $fecha = date("Y-m-d");
        $sql   = "SELECT a.fecha, a.hora, e.cedula, e.nombre, e.apellido, e.area
                  FROM asistencias a
                  INNER JOIN empleados e ON a.cedula_id_cedula = e.cedula
                  WHERE a.cedula_id_cedula = '$cedula' AND a.fecha = '$fecha'";
        return mysqli_fetch_assoc($this->execute($sql));
    }

    /**
     * Busca el empleado cuya huella coincide con la imagen capturada.
     * Usa GD si está disponible, sino usa comparación de bytes directa.
     * Retorna: cedula | null
     */
    public function buscarPorHuella($rutaCapturada) {
        $result = $this->execute(
            "SELECT cedula, huella FROM empleados WHERE huella != '' AND huella IS NOT NULL"
        );
        if (!$rutaCapturada || !file_exists($rutaCapturada)) return null;

        $gdDisponible = function_exists('imagecreatefromjpeg');

        if ($gdDisponible) {
            return $this->buscarConGD($rutaCapturada, $result);
        } else {
            return $this->buscarSinGD($rutaCapturada, $result);
        }
    }

    /**
     * Comparación usando GD (requiere extension=gd en php.ini)
     */
    private function buscarConGD($rutaCapturada, $result) {
        $imgCap = @imagecreatefromjpeg($rutaCapturada);
        if (!$imgCap) return null;

        $mejorCedula = null;
        $mejorScore  = 0;
        $UMBRAL      = 0.72;

        while ($row = mysqli_fetch_assoc($result)) {
            $ruta = '../web/' . $row['huella'];
            if (!file_exists($ruta)) continue;
            $imgDB = @imagecreatefromjpeg($ruta);
            if (!$imgDB) continue;
            $score = $this->compararImagenesGD($imgCap, $imgDB);
            imagedestroy($imgDB);
            if ($score > $mejorScore) {
                $mejorScore  = $score;
                $mejorCedula = $row['cedula'];
            }
        }
        imagedestroy($imgCap);
        return ($mejorScore >= $UMBRAL) ? $mejorCedula : null;
    }

    /**
     * Comparación SIN GD: usa bytes crudos del JPEG
     * Compara los primeros N bytes de la imagen (firma visual aproximada)
     */
    private function buscarSinGD($rutaCapturada, $result) {
        $bytesCapturada = file_get_contents($rutaCapturada);
        if (!$bytesCapturada) return null;

        // Usar el contenido completo para comparación
        $tamCap  = strlen($bytesCapturada);
        $UMBRAL  = 0.85; // Más estricto sin GD

        $mejorCedula = null;
        $mejorScore  = 0;

        while ($row = mysqli_fetch_assoc($result)) {
            $ruta = '../web/' . $row['huella'];
            if (!file_exists($ruta)) continue;

            $bytesDB = file_get_contents($ruta);
            if (!$bytesDB) continue;

            $score = $this->compararBytesSimilitud($bytesCapturada, $bytesDB);
            if ($score > $mejorScore) {
                $mejorScore  = $score;
                $mejorCedula = $row['cedula'];
            }
        }

        return ($mejorScore >= $UMBRAL) ? $mejorCedula : null;
    }

    /**
     * Similitud de bytes entre dos archivos JPEG.
     * Compara muestras del contenido comprimido como aproximación.
     */
    private function compararBytesSimilitud($bytes1, $bytes2): float {
        // Saltar el header JPEG (primeros ~300 bytes de metadatos)
        $SKIP   = 300;
        $SAMPLE = 5000; // bytes a comparar

        $len1 = strlen($bytes1);
        $len2 = strlen($bytes2);

        if ($len1 < $SKIP + 100 || $len2 < $SKIP + 100) return 0.0;

        // Extraer muestra del centro del archivo
        $centro1 = (int)($len1 / 2);
        $centro2 = (int)($len2 / 2);
        $muestra1 = substr($bytes1, $centro1, $SAMPLE);
        $muestra2 = substr($bytes2, $centro2, $SAMPLE);

        $len = min(strlen($muestra1), strlen($muestra2));
        if ($len < 100) return 0.0;

        // Contar bytes iguales en posiciones equivalentes
        $iguales = 0;
        $TOLERANCIA = 8; // tolerancia por compresión JPEG
        for ($i = 0; $i < $len; $i++) {
            $diff = abs(ord($muestra1[$i]) - ord($muestra2[$i]));
            if ($diff <= $TOLERANCIA) $iguales++;
        }

        return $iguales / $len;
    }

    /** Comparación GD con histograma Bhattacharyya */
    private function compararImagenesGD($img1, $img2): float {
        $W = 60; $H = 80;
        $r1 = imagecreatetruecolor($W, $H);
        $r2 = imagecreatetruecolor($W, $H);
        imagecopyresampled($r1, $img1, 0,0,0,0, $W,$H, imagesx($img1), imagesy($img1));
        imagecopyresampled($r2, $img2, 0,0,0,0, $W,$H, imagesx($img2), imagesy($img2));

        $h1 = array_fill(0, 32, 0);
        $h2 = array_fill(0, 32, 0);
        $total = $W * $H;

        for ($y = 0; $y < $H; $y++) {
            for ($x = 0; $x < $W; $x++) {
                $c1 = imagecolorat($r1,$x,$y);
                $c2 = imagecolorat($r2,$x,$y);
                $g1 = (($c1>>16)&0xFF)*0.299+(($c1>>8)&0xFF)*0.587+($c1&0xFF)*0.114;
                $g2 = (($c2>>16)&0xFF)*0.299+(($c2>>8)&0xFF)*0.587+($c2&0xFF)*0.114;
                $h1[(int)($g1/8)]++;
                $h2[(int)($g2/8)]++;
            }
        }
        imagedestroy($r1); imagedestroy($r2);

        $suma = 0;
        for ($i = 0; $i < 32; $i++)
            $suma += sqrt(($h1[$i]/$total)*($h2[$i]/$total));
        return $suma;
    }
}
