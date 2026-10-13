<?php
/**
 * capturar_huella.php
 * Se conecta al agente local (puerto 7842) que corre en la sesión
 * del usuario y tiene acceso al lector biométrico.
 *
 * REQUISITO: Iniciar el agente antes de usar el sistema:
 *   web\huella\agente\iniciar_agente.bat
 */
header('Content-Type: application/json; charset=utf-8');
set_time_limit(30);

$HOST = "127.0.0.1";
$PORT = 7842;
$TIMEOUT = 25; // segundos esperando la captura

// Usar fsockopen (más compatible que socket_*)
$fp = @fsockopen($HOST, $PORT, $errno, $errstr, 5);

if (!$fp) {
    echo json_encode([
        'ok'    => false,
        'error' => 'El agente de huella no está corriendo. '
                 . 'Abre iniciar_agente.bat en la carpeta web/huella/agente/ '
                 . 'y déjalo abierto mientras usas el sistema.'
    ]);
    exit;
}

// Configurar timeout de lectura
stream_set_timeout($fp, $TIMEOUT);

// Enviar comando
fwrite($fp, "CAPTURAR\n");

// Leer respuesta completa (puede ser ~20 KB por el base64)
$respuesta = '';
while (!feof($fp)) {
    $chunk = fread($fp, 8192);
    if ($chunk === false) break;
    $respuesta .= $chunk;
    // El agente termina la respuesta con \n
    if (str_contains($respuesta, "\n")) break;
    // Verificar timeout
    $info = stream_get_meta_data($fp);
    if ($info['timed_out']) break;
}
fclose($fp);

$respuesta = trim($respuesta);
$resultado = json_decode($respuesta, true);

if (json_last_error() !== JSON_ERROR_NONE || $resultado === null) {
    echo json_encode([
        'ok'    => false,
        'error' => 'Respuesta inválida del agente. Detalle: ' . substr($respuesta, 0, 150)
    ]);
    exit;
}

echo json_encode($resultado);
