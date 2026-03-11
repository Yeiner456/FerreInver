<?php


mysqli_report(MYSQLI_REPORT_OFF);

$host    = "localhost";
$usuario = "root";
$password = "";
$bd      = "ferreinver";

$conn = new mysqli($host, $usuario, $password, $bd);

if ($conn->connect_error) {
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "mensaje" => "Error de conexión a la base de datos"
    ]);
    exit();
}

$conn->set_charset("utf8");
?>