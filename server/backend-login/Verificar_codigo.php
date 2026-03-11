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

if (!isset($data["correo"]) || !isset($data["codigo"])) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit();
}

$correo = trim($data["correo"]);
$codigo = trim($data["codigo"]);
$ahora  = date("Y-m-d H:i:s");

$stmt = $conn->prepare("SELECT codigo_recuperacion, codigo_expiracion FROM clientes WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$resultado) {
    echo json_encode(["success" => false, "mensaje" => "Correo no encontrado"]);
    exit();
}

if ($resultado["codigo_recuperacion"] !== $codigo) {
    echo json_encode(["success" => false, "mensaje" => "Código incorrecto"]);
    exit();
}

if ($ahora > $resultado["codigo_expiracion"]) {
    echo json_encode(["success" => false, "mensaje" => "El código ha expirado. Solicita uno nuevo"]);
    exit();
}

echo json_encode(["success" => true, "mensaje" => "Código verificado correctamente"]);

$conn->close();
?>