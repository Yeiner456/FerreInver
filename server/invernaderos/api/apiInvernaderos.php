<?php
// api/invernaderos.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ─── LISTAR ────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "SELECT * FROM invernaderos ORDER BY id_invernadero DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre'] ?? '');
        $descripcion = trim($b['descripcion'] ?? '');
        $precio_m2   = $b['precio_m2'] ?? '';
        $estado      = trim($b['estado'] ?? '');

        if (empty($nombre) || empty($precio_m2) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Nombre, precio m² y estado son obligatorios."]);
            exit;
        }
        if (strlen($nombre) > 50) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 50 caracteres."]);
            exit;
        }
        if (strlen($descripcion) > 150) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 150 caracteres."]);
            exit;
        }
        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99) {
            echo json_encode(["success" => false, "message" => "El precio m² debe ser un número positivo válido."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Nombre duplicado
        $st = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE nombre = ?");
        mysqli_stmt_bind_param($st, 's', $nombre);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe un invernadero con ese nombre."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO invernaderos (nombre, descripcion, precio_m2, estado) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'ssds', $nombre, $descripcion, $precio_m2, $estado);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Invernadero registrado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ACTUALIZAR ────────────────────────────────────────────────────────
    case 'PUT':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];
        $b = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre'] ?? '');
        $descripcion = trim($b['descripcion'] ?? '');
        $precio_m2   = $b['precio_m2'] ?? '';
        $estado      = trim($b['estado'] ?? '');

        if (empty($nombre) || empty($precio_m2) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Nombre, precio m² y estado son obligatorios."]);
            exit;
        }
        if (strlen($nombre) > 50) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 50 caracteres."]);
            exit;
        }
        if (strlen($descripcion) > 150) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 150 caracteres."]);
            exit;
        }
        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99) {
            echo json_encode(["success" => false, "message" => "El precio m² debe ser un número positivo válido."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Existe
        $st = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El invernadero no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Nombre duplicado en otro registro
        $st = mysqli_prepare($conn, "SELECT id_invernadero FROM invernaderos WHERE nombre = ? AND id_invernadero != ?");
        mysqli_stmt_bind_param($st, 'si', $nombre, $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe otro invernadero con ese nombre."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE invernaderos SET nombre=?, descripcion=?, precio_m2=?, estado=? WHERE id_invernadero=?");
        mysqli_stmt_bind_param($st, 'ssdsi', $nombre, $descripcion, $precio_m2, $estado, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Invernadero actualizado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── DESACTIVAR INVERNADERO ────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        // Verificar que existe y obtener estado actual
        $st = mysqli_prepare($conn, "SELECT id_invernadero, estado FROM invernaderos WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($res) === 0) {
            echo json_encode(["success" => false, "message" => "El invernadero no existe."]);
            exit;
        }
        $inv_actual = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);

        // Verificar que no esté ya desactivado
        if ($inv_actual['estado'] === 'inactivo') {
            echo json_encode(["success" => false, "message" => "El invernadero ya está desactivado."]);
            exit;
        }

        $st = mysqli_prepare($conn, "UPDATE invernaderos SET estado = 'inactivo' WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Invernadero desactivado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);