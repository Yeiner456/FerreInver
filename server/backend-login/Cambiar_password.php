<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once "config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["correo"]) || !isset($data["codigo"]) || !isset($data["nueva_password"])) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit();
}

$correo          = trim($data["correo"]);
$codigo          = trim($data["codigo"]);
$nueva_password  = $data["nueva_password"];
$ahora           = date("Y-m-d H:i:s");

if (strlen($nueva_password) < 8) {
    echo json_encode(["success" => false, "mensaje" => "La contraseña debe tener al menos 8 caracteres"]);
    exit();
}

// Verificar código una vez más por seguridad
$stmt = $conn->prepare("SELECT codigo_recuperacion, codigo_expiracion FROM clientes WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$resultado || $resultado["codigo_recuperacion"] !== $codigo) {
    echo json_encode(["success" => false, "mensaje" => "Código inválido"]);
    exit();
}

if ($ahora > $resultado["codigo_expiracion"]) {
    echo json_encode(["success" => false, "mensaje" => "El código ha expirado"]);
    exit();
}

// Actualizar contraseña y limpiar el código
$hash = password_hash($nueva_password, PASSWORD_DEFAULT);

$stmt2 = $conn->prepare("UPDATE clientes SET password_hash = ?, codigo_recuperacion = NULL, codigo_expiracion = NULL WHERE correo = ?");
$stmt2->bind_param("ss", $hash, $correo);

if ($stmt2->execute()) {
    echo json_encode(["success" => true, "mensaje" => "Contraseña actualizada correctamente"]);
} else {
    echo json_encode(["success" => false, "mensaje" => "Error al actualizar la contraseña"]);
}

$stmt2->close();
$conn->close();
?>