<?php

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../conexion.php';

$resultado = mysqli_query($conn, "SELECT id_tipo_de_usuario, nombre FROM tipos_usuarios ORDER BY nombre");

$tipos = [];
while ($fila = mysqli_fetch_assoc($resultado)) {
    $tipos[] = $fila;
}

echo json_encode(["success" => true, "data" => $tipos]);
mysqli_close($conn);