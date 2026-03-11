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
require_once "vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["correo"])) {
    echo json_encode(["success" => false, "mensaje" => "Correo requerido"]);
    exit();
}

$correo = trim($data["correo"]);

// Verificar que el correo existe en la BD
$stmt = $conn->prepare("SELECT documento FROM clientes WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(["success" => false, "mensaje" => "No existe una cuenta con ese correo"]);
    $stmt->close();
    exit();
}
$stmt->close();

// Generar código de 6 dígitos y su expiración (15 minutos)
$codigo     = strval(rand(100000, 999999));
$expiracion = date("Y-m-d H:i:s", strtotime("+15 minutes"));

// Guardar código en la BD
$stmt2 = $conn->prepare("UPDATE clientes SET codigo_recuperacion = ?, codigo_expiracion = ? WHERE correo = ?");
$stmt2->bind_param("sss", $codigo, $expiracion, $correo);
$stmt2->execute();
$stmt2->close();

// Enviar correo con PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "pablomontoya322501@gmail.com";    
    $mail->Password   = "atkgahquyuppelkz";   
    $mail->SMTPSecure = "tls";
    $mail->Port       = 587;
    $mail->CharSet    = "UTF-8";

    $mail->setFrom("pablomontoya322501@gmail.com", "Ferreinver");
    $mail->addAddress($correo);

    $mail->isHTML(true);
    $mail->Subject = "Código de recuperación - Ferreinver";
    $mail->Body    = "
        <div style='font-family: DM Sans, sans-serif; max-width: 400px; margin: auto; padding: 30px; border-radius: 12px; border: 1px solid #e0e0e0;'>
            <h2 style='color: #00185a;'>Recuperar contraseña</h2>
            <p>Tu código de verificación es:</p>
            <h1 style='letter-spacing: 8px; color: #00185a; font-size: 40px;'>$codigo</h1>
            <p style='color: #999; font-size: 13px;'>Este código expira en 15 minutos.</p>
        </div>
    ";

    $mail->send();

    echo json_encode(["success" => true, "mensaje" => "Código enviado a tu correo"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "mensaje" => "Error al enviar el correo: " . $mail->ErrorInfo]);
}

$conn->close();
?>