<?php
// api/apiLogin.php — Endpoint de autenticación de clientes

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

require_once '../conexion.php';

$body = json_decode(file_get_contents("php://input"), true);

$documento = trim($body['documento'] ?? '');
$password  = trim($body['password']  ?? '');

// Validaciones básicas
if (empty($documento) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Documento y contraseña son obligatorios."]);
    exit;
}
if (!is_numeric($documento) || $documento <= 0) {
    echo json_encode(["success" => false, "message" => "Documento inválido."]);
    exit;
}

// Buscar cliente
$st = mysqli_prepare($conn,
    "SELECT documento, nombre, correo, password_hash, estado_inicio_sesion, id_tipo_de_usuario
     FROM clientes
     WHERE documento = ?"
);
mysqli_stmt_bind_param($st, 'i', $documento);
mysqli_stmt_execute($st);
$result = mysqli_stmt_get_result($st);
$cliente = mysqli_fetch_assoc($result);
mysqli_stmt_close($st);

if (!$cliente) {
    echo json_encode(["success" => false, "message" => "Documento o contraseña incorrectos."]);
    exit;
}

if ($cliente['estado_inicio_sesion'] === 'inactivo') {
    echo json_encode(["success" => false, "message" => "Tu cuenta está inactiva. Contacta con Ferreinver."]);
    exit;
}

if (!password_verify($password, $cliente['password_hash'])) {
    echo json_encode(["success" => false, "message" => "Documento o contraseña incorrectos."]);
    exit;
}

// Login exitoso — devolver datos sin el hash
echo json_encode([
    "success" => true,
    "message" => "Sesión iniciada correctamente.",
    "cliente" => [
        "documento"         => $cliente['documento'],
        "nombre"            => $cliente['nombre'],
        "correo"            => $cliente['correo'],
        "id_tipo_de_usuario"=> $cliente['id_tipo_de_usuario'],
    ]
]);

mysqli_close($conn);