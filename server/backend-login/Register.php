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

if (!isset($data["nombre"]) || !isset($data["correo"]) || !isset($data["documento"]) || !isset($data["password"])) {
    echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios"]);
    exit();
}

$nombre    = trim($data["nombre"]);
$correo    = trim($data["correo"]);
$documento = (int)trim($data["documento"]);
$password  = $data["password"];

// Validaciones
if (empty($nombre) || empty($correo) || !$documento || empty($password)) {
    echo json_encode(["success" => false, "mensaje" => "Todos los campos son obligatorios"]);
    exit();
}

if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,}$/u", $nombre)) {
    echo json_encode(["success" => false, "mensaje" => "El nombre solo puede contener letras y debe tener al menos 3 caracteres"]);
    exit();
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "mensaje" => "El correo electrónico no es válido"]);
    exit();
}

if ($documento < 100000 || $documento > 999999999999999) {
    echo json_encode(["success" => false, "mensaje" => "El documento debe tener entre 6 y 15 dígitos"]);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(["success" => false, "mensaje" => "La contraseña debe tener al menos 8 caracteres"]);
    exit();
}

// Verificar correo duplicado
$stmtCorreo = $conn->prepare("SELECT documento FROM clientes WHERE correo = ?");
$stmtCorreo->bind_param("s", $correo);
$stmtCorreo->execute();
$stmtCorreo->store_result();
if ($stmtCorreo->num_rows > 0) {
    echo json_encode(["success" => false, "mensaje" => "Este correo ya está registrado"]);
    $stmtCorreo->close();
    exit();
}
$stmtCorreo->close();

// Verificar documento duplicado
$stmtDoc = $conn->prepare("SELECT documento FROM clientes WHERE documento = ?");
$stmtDoc->bind_param("i", $documento);
$stmtDoc->execute();
$stmtDoc->store_result();
if ($stmtDoc->num_rows > 0) {
    echo json_encode(["success" => false, "mensaje" => "Este documento ya está registrado"]);
    $stmtDoc->close();
    exit();
}
$stmtDoc->close();

//  No necesitamos pasar id_tipo_de_usuario, la BD tiene DEFAULT 2 (cliente)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO clientes (documento, nombre, correo, password_hash) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isss", $documento, $nombre, $correo, $passwordHash);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "mensaje" => "Cuenta creada correctamente"]);
} else {
    echo json_encode(["success" => false, "mensaje" => "Error al crear la cuenta: " . $conn->error]);
}

$stmt->close();
$conn->close();
?>