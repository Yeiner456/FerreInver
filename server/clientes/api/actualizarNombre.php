<?php
// clientes/api/actualizarNombre.php — Endpoint para actualizar nombre del cliente

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Solo aceptar PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(["success" => false, "mensaje" => "Método no permitido"]);
    exit;
}

require_once '../conexion.php';

// Leer body JSON
$body = json_decode(file_get_contents("php://input"), true);

$documento = isset($body['documento']) ? intval($body['documento']) : null;
$nombre    = isset($body['nombre'])    ? trim($body['nombre'])       : null;

// Validaciones
if (!$documento || !$nombre) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

if (strlen($nombre) < 2 || strlen($nombre) > 30) {
    echo json_encode(["success" => false, "mensaje" => "El nombre debe tener entre 2 y 30 caracteres"]);
    exit;
}

// Verificar que el cliente existe
$check = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
mysqli_stmt_bind_param($check, "i", $documento);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) === 0) {
    echo json_encode(["success" => false, "mensaje" => "Cliente no encontrado"]);
    mysqli_stmt_close($check);
    mysqli_close($conn);
    exit;
}
mysqli_stmt_close($check);

// Actualizar nombre
$stmt = mysqli_prepare($conn, "UPDATE clientes SET nombre = ? WHERE documento = ?");
mysqli_stmt_bind_param($stmt, "si", $nombre, $documento);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode([
        "success"  => true,
        "mensaje"  => "Nombre actualizado correctamente",
        "nombre"   => $nombre
    ]);
} else {
    echo json_encode([
        "success" => false,
        "mensaje" => "Error al actualizar: " . mysqli_error($conn)
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>