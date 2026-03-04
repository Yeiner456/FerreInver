<?php
// api/productos.php

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
        $resultado = mysqli_query($conn, "SELECT * FROM productos ORDER BY id_producto DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre'] ?? '');
        $precio      = $b['precio'] ?? '';
        $descripcion = trim($b['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '') {
            echo json_encode(["success" => false, "message" => "El nombre y el precio son obligatorios."]);
            exit;
        }
        if (strlen($nombre) > 30 || strlen($nombre) === 0) {
            echo json_encode(["success" => false, "message" => "El nombre debe tener entre 1 y 30 caracteres."]);
            exit;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número mayor a 0."]);
            exit;
        }
        if (floor($precio) != $precio) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número entero."]);
            exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]);
            exit;
        }

        $st = mysqli_prepare($conn, "INSERT INTO productos (nombre, Precio, Descripcion) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($st, 'sis', $nombre, $precio, $descripcion);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto registrado exitosamente."]);
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
        $b  = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre'] ?? '');
        $precio      = $b['precio'] ?? '';
        $descripcion = trim($b['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '') {
            echo json_encode(["success" => false, "message" => "El nombre y el precio son obligatorios."]);
            exit;
        }
        if (strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 30 caracteres."]);
            exit;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número mayor a 0."]);
            exit;
        }
        if (floor($precio) != $precio) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número entero."]);
            exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]);
            exit;
        }

        // Verificar que existe
        $st = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE productos SET nombre=?, Precio=?, Descripcion=? WHERE id_producto=?");
        mysqli_stmt_bind_param($st, 'sisi', $nombre, $precio, $descripcion, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto actualizado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ELIMINAR ──────────────────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        $st = mysqli_prepare($conn, "SELECT ID_producto FROM productos WHERE ID_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM productos WHERE ID_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto eliminado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);