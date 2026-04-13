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

if (!isset($data["correo"]) || !isset($data["password"])) {
    echo json_encode(["success" => false, "mensaje" => "Datos incompletos"]);
    exit();
}

$correo   = trim($data["correo"]);
$password = $data["password"];

//  JOIN para traer el nombre del rol (admin o cliente)
$sql = "SELECT c.documento, c.nombre, c.correo, c.estado_inicio_sesion,
               t.nombre AS tipo_usuario
        FROM clientes c
        LEFT JOIN tipos_usuarios t ON c.id_tipo_de_usuario = t.id_tipo_de_usuario
        WHERE c.correo = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();

    // Verificar que la cuenta esté activa
    if ($usuario["estado_inicio_sesion"] === "inactivo") {
        echo json_encode(["success" => false, "mensaje" => "Tu cuenta está inactiva. Contacta al administrador"]);
        exit();
    }

    // Verificar contraseña — buscar password_hash por separado
    $sqlPass = "SELECT password_hash FROM clientes WHERE correo = ?";
    $stmtPass = $conn->prepare($sqlPass);
    $stmtPass->bind_param("s", $correo);
    $stmtPass->execute();
    $resPass = $stmtPass->get_result()->fetch_assoc();

    if (password_verify($password, $resPass["password_hash"])) {
        echo json_encode([
            "success" => true,
            "mensaje" => "Login correcto",
            "usuario" => [
                "documento"    => $usuario["documento"],
                "nombre"       => $usuario["nombre"],
                "tipo_usuario" => $usuario["tipo_usuario"],
                "correo"              => $usuario["correo"],              // ← agrega
                "estado_inicio_sesion"=> $usuario["estado_inicio_sesion"] // "admin" o "cliente"
                
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "mensaje" => "Contraseña incorrecta"]);
    }
} else {
    echo json_encode(["success" => false, "mensaje" => "Usuario no encontrado"]);
}

$stmt->close();
$conn->close();
?>