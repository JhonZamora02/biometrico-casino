<?php
/**
 * test_verificador.php - Muestra el error exacto del servidor
 * http://localhost/Biometrico_Casino/web/test_verificador.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== Test Verificador (con errores visibles) ===\n\n";

// Verificar que las clases necesarias existen
echo "1. Verificando archivos...\n";
$files = [
    '../Controller/CtrlVerificado.php',
    '../DAO/AsistenciaDAO.php',
    '../DAO/EmpleadoDAO.php',
    '../Lib/helpers.php',
    '../Lib/Config/conexionSqli.php',
];
foreach ($files as $f) {
    echo "   " . ($f) . ": " . (file_exists($f) ? "OK" : "NO EXISTE") . "\n";
}

echo "\n2. Cargando helpers...\n";
include_once '../Lib/helpers.php';
echo "   OK\n";

echo "\n3. Cargando CtrlVerificado...\n";
include_once '../Controller/CtrlVerificado.php';
echo "   OK\n";

echo "\n4. Probando conexión al agente TCP (127.0.0.1:7842)...\n";
$fp = @fsockopen("127.0.0.1", 7842, $e, $es, 3);
if (!$fp) {
    echo "   ERROR: Agente no disponible ($e: $es)\n";
    echo "   Solución: Ejecuta iniciar_agente.bat\n";
} else {
    stream_set_timeout($fp, 3);
    fwrite($fp, "PING\n");
    $r = fgets($fp, 256);
    fclose($fp);
    echo "   OK: " . trim($r) . "\n";
}

echo "\n5. Ejecutando verificarHuella() - pon el dedo ahora...\n";
flush();

$ctrl = new CtrlVerificado();

// Capturar tanto stdout como stderr
ob_start();
try {
    $ctrl->verificarHuella();
} catch (Throwable $ex) {
    echo "EXCEPCION: " . $ex->getMessage() . "\n";
    echo "En: " . $ex->getFile() . " línea " . $ex->getLine() . "\n";
    echo $ex->getTraceAsString();
}
$output = ob_get_clean();

echo "\n6. Respuesta del servidor:\n";
echo "---\n";
echo $output;
echo "\n---\n";

// Intentar parsear
$data = json_decode(trim($output), true);
if ($data) {
    echo "\n7. JSON parseado correctamente:\n";
    print_r($data);
} else {
    echo "\n7. ERROR: No se pudo parsear como JSON\n";
    echo "   json_last_error: " . json_last_error_msg() . "\n";
    echo "   Contenido hex: " . bin2hex(substr($output, 0, 50)) . "\n";
}

echo "\n=== FIN ===\n";
