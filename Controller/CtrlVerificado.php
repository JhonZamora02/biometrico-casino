<?php
include_once '../DAO/AsistenciaDAO.php';
include_once '../DAO/EmpleadoDAO.php';

class CtrlVerificado {

    public function read() {
        include_once '../View/Verificado/FrmVerificado.php';
    }

    public function confirmacion() {
        include_once '../View/Verificado/FrmConfirmacion.php';
    }

    public function verificarHuella() {
        // NO usar ob_clean() — borra el buffer del caller
        // Simplemente suprimir errores y emitir JSON limpio
        error_reporting(0);
        ini_set('display_errors', 0);
        set_time_limit(35);

        // Emitir header JSON
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        date_default_timezone_set("America/Bogota");
        $hoy       = date("Y-m-d");
        $diaSemana = (int)date("w");
        $hora      = date("H:i:s");

        /* ── REGLAS DE NEGOCIO (descomenta en producción) ────────
        if ($diaSemana === 0) {
            echo json_encode(['ok'=>false,'tipo'=>'domingo',
                'error'=>'El casino no opera los días domingos.']); return;
        }
        if ($hora >= '10:00:00') {
            echo json_encode(['ok'=>false,'tipo'=>'fuera_horario',
                'error'=>'La plataforma cerró a las 10:00 AM.']); return;
        }
        ─────────────────────────────────────────────────────── */

        // Capturar huella desde el agente TCP
        $captura = $this->capturarDesdeAgente();

        if (!$captura['ok']) {
            echo json_encode(['ok' => false, 'error' => $captura['error']]);
            return;
        }

        // Guardar imagen temporal
        $tmpDir  = dirname(__DIR__) . '/web/huellas_img/';
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0755, true);
        $tmpFile = $tmpDir . 'verif_tmp_' . time() . '.jpg';
        $imgData = base64_decode($captura['imagen_b64']);

        if (!$imgData || !file_put_contents($tmpFile, $imgData)) {
            echo json_encode(['ok' => false, 'error' => 'Error guardando imagen temporal.']);
            return;
        }

        // Buscar empleado por huella
        $cedula = AsistenciaDAO::getInstance()->buscarPorHuella($tmpFile);
        @unlink($tmpFile);

        if (!$cedula) {
            echo json_encode(['ok' => false, 'tipo' => 'no_reconocido',
                'error' => 'Huella no reconocida. No coincide con ningún empleado registrado.']);
            return;
        }

        // Verificar reserva hoy
        $reserva = AsistenciaDAO::getInstance()->verificarReservaHoy($cedula);
        if (!$reserva) {
            $emp    = EmpleadoDAO::getInstance()->getByCedula($cedula);
            $nombre = $emp
                ? htmlspecialchars($emp['nombre'] . ' ' . $emp['apellido'])
                : 'El empleado';
            echo json_encode(['ok' => false, 'tipo' => 'sin_reserva', 'nombre' => $nombre,
                'error' => "$nombre no tiene reserva de almuerzo para hoy ($hoy)."]);
            return;
        }

        echo json_encode([
            'ok'           => true,
            'cedula'       => $reserva['cedula'],
            'nombre'       => htmlspecialchars($reserva['nombre']),
            'apellido'     => htmlspecialchars($reserva['apellido']),
            'hora_reserva' => substr($reserva['hora'], 0, 5),
            'fecha'        => $reserva['fecha'],
        ]);
    }

    /** Conecta al agente TCP en puerto 7842 */
    private function capturarDesdeAgente(): array {
        $HOST    = "127.0.0.1";
        $PORT    = 7842;
        $TIMEOUT = 25;

        $fp = @fsockopen($HOST, $PORT, $errno, $errstr, 5);
        if (!$fp) {
            return ['ok' => false,
                'error' => 'El agente de huella no está corriendo. '
                         . 'Abre iniciar_agente.bat y déjalo abierto.'];
        }

        stream_set_timeout($fp, $TIMEOUT);
        fwrite($fp, "CAPTURAR\n");

        $respuesta = '';
        $inicio    = time();
        while (!feof($fp)) {
            $chunk = fread($fp, 32768);
            if ($chunk === false || $chunk === '') break;
            $respuesta .= $chunk;
            if (str_contains($respuesta, "\n")) break;
            if (time() - $inicio > $TIMEOUT) break;
            $meta = stream_get_meta_data($fp);
            if ($meta['timed_out']) break;
        }
        fclose($fp);

        $data = json_decode(trim($respuesta), true);
        if (json_last_error() !== JSON_ERROR_NONE || $data === null) {
            return ['ok' => false,
                'error' => 'Respuesta inválida del agente (' . strlen($respuesta) . ' bytes): '
                         . substr($respuesta, 0, 200)];
        }
        return $data;
    }
}
