<?php
/**
 * test_agente.php - Diagnóstico directo del agente TCP
 * Abrir en el navegador: http://localhost/Biometrico_Casino/web/huella/test_agente.php
 */
echo "<h2>Test Agente Huella</h2>";
echo "<pre>";

$HOST = "127.0.0.1"; $PORT = 7842;

// Test PING
echo "1. Conectando al agente en $HOST:$PORT...\n";
$fp = @fsockopen($HOST, $PORT, $errno, $errstr, 3);
if (!$fp) {
    echo "ERROR: No se pudo conectar ($errno: $errstr)\n";
    echo "Solución: Ejecuta iniciar_agente.bat\n";
    die("</pre>");
}
echo "OK: Conexión establecida\n";
stream_set_timeout($fp, 5);
fwrite($fp, "PING\n");
$resp = fgets($fp, 256);
fclose($fp);
echo "Respuesta PING: $resp\n\n";

// Test CAPTURAR
echo "2. Probando captura (pon el dedo en el lector)...\n";
$fp2 = @fsockopen($HOST, $PORT, $e2, $es2, 3);
if (!$fp2) { echo "ERROR segunda conexión\n"; die("</pre>"); }
stream_set_timeout($fp2, 20);
fwrite($fp2, "CAPTURAR\n");
$respuesta = '';
$inicio = time();
while (!feof($fp2)) {
    $chunk = fread($fp2, 16384);
    if ($chunk === false || $chunk === '') break;
    $respuesta .= $chunk;
    if (str_contains($respuesta, "\n")) break;
    if (time() - $inicio > 18) { echo "TIMEOUT\n"; break; }
}
fclose($fp2);

$data = json_decode(trim($respuesta), true);
if (!$data) {
    echo "ERROR parseando JSON: " . substr($respuesta, 0, 200) . "\n";
} elseif ($data['ok']) {
    echo "OK: Captura exitosa!\n";
    echo "  Formato: " . $data['formato'] . "\n";
    echo "  Tamaño base64: " . strlen($data['imagen_b64']) . " chars\n";
    // Mostrar imagen
    echo "</pre><img src='data:image/jpeg;base64," . $data['imagen_b64'] . "' style='border:1px solid #ccc;'>\n";
} else {
    echo "ERROR en captura: " . $data['error'] . "\n";
}
echo "</pre>";
