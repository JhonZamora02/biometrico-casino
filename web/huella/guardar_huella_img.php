<?php
/**
 * guardar_huella_img.php
 * ─────────────────────────────────────────────────────────────────
 * Recibe la imagen de huella (base64) ya capturada por el navegador,
 * la guarda en disco como JPG y actualiza la ruta en la BD.
 *
 * POST params:
 *   cedula      → cédula del empleado
 *   imagen_b64  → imagen en base64 (PNG o JPG, sin prefijo data:...)
 *
 * Responde JSON:
 *   { "ok": true,  "ruta": "huellas_img/huella_10000001.jpg" }
 *   { "ok": false, "error": "Mensaje" }
 * ─────────────────────────────────────────────────────────────────
 */

header('Content-Type: application/json; charset=utf-8');

// Subir un nivel desde web/huella/ para llegar a Lib/
include_once '../../Lib/Config/conexionSqli.php';

$cedula   = trim($_POST['cedula']     ?? '');
$imagenB64 = trim($_POST['imagen_b64'] ?? '');

if (!$cedula || !$imagenB64) {
    echo json_encode(['ok' => false, 'error' => 'Parámetros incompletos']);
    exit;
}

// Limpiar cédula para nombre de archivo seguro
$cedulaSegura = preg_replace('/[^0-9a-zA-Z_]/', '', $cedula);

// Directorio destino: web/huellas_img/
$dir      = __DIR__ . '/../huellas_img/';
$filename = 'huella_' . $cedulaSegura . '.jpg';
$filepath = $dir . $filename;
$rutaDB   = 'huellas_img/' . $filename;

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

// Decodificar base64
$imageData = base64_decode($imagenB64);
if (!$imageData) {
    echo json_encode(['ok' => false, 'error' => 'Imagen base64 inválida']);
    exit;
}

// Intentar guardar como JPG usando GD (disponible en XAMPP/WAMP)
$guardado = false;
if (function_exists('imagecreatefromstring')) {
    $gd = @imagecreatefromstring($imageData);
    if ($gd !== false) {
        // Aplicar leve mejora de contraste para imagen de huella
        imagefilter($gd, IMG_FILTER_CONTRAST, -8);
        imagejpeg($gd, $filepath, 92);
        imagedestroy($gd);
        $guardado = true;
    }
}

// Fallback: guardar los bytes directamente (si GD no pudo)
if (!$guardado) {
    file_put_contents($filepath, $imageData);
}

// Actualizar columna `huella` en la BD
$con    = new Connection();
$link   = $con->getConnect();
$c      = mysqli_real_escape_string($link, $cedula);
$r      = mysqli_real_escape_string($link, $rutaDB);
$result = mysqli_query($link, "UPDATE empleados SET huella='$r' WHERE cedula='$c'");

if (!$result) {
    echo json_encode(['ok' => false, 'error' => 'BD: ' . mysqli_error($link)]);
    exit;
}

echo json_encode(['ok' => true, 'ruta' => $rutaDB]);
