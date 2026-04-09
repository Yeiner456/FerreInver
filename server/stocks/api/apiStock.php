<?php
// api/stocks.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET ?selects=1 → productos para el select
if ($method === 'GET' && isset($_GET['selects'])) {
    $res = mysqli_query($conn, "SELECT id_producto, nombre FROM productos ORDER BY nombre ASC");
    $rows = [];
    while ($f = mysqli_fetch_assoc($res)) $rows[] = $f;
    echo json_encode(["success" => true, "productos" => $rows]);
    exit;
}

switch ($method) {

    // ─── LISTAR ────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "
            SELECT s.id_stock, s.cantidad, s.id_producto,
                   p.nombre AS nombre_producto, p.precio
            FROM stocks s
            INNER JOIN productos p ON s.id_producto = p.id_producto
            ORDER BY s.id_stock DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $id_producto = $b['id_producto'] ?? '';
        $cantidad    = $b['cantidad'] ?? '';

        if (empty($id_producto) || $cantidad === '') {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($id_producto) || $id_producto <= 0) {
            echo json_encode(["success" => false, "message" => "Producto inválido."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser un entero mayor o igual a 0."]);
            exit;
        }

        // Verificar producto
        $st = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar que no exista ya un stock para este producto
        $st = mysqli_prepare($conn, "SELECT id_stock FROM stocks WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe un registro de stock para este producto."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO stocks (id_producto, cantidad) VALUES (?, ?)");
        mysqli_stmt_bind_param($st, 'ii', $id_producto, $cantidad);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Stock registrado exitosamente."]);
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

        $id_producto = $b['id_producto'] ?? '';
        $cantidad    = $b['cantidad'] ?? '';

        if (empty($id_producto) || $cantidad === '') {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($id_producto) || $id_producto <= 0) {
            echo json_encode(["success" => false, "message" => "Producto inválido."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser un entero mayor o igual a 0."]);
            exit;
        }

        // Existe el stock
        $st = mysqli_prepare($conn, "SELECT id_stock FROM stocks WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El stock no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar producto
        $st = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Stock duplicado en otro registro
        $st = mysqli_prepare($conn, "SELECT id_stock FROM stocks WHERE id_producto = ? AND id_stock != ?");
        mysqli_stmt_bind_param($st, 'ii', $id_producto, $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe otro registro de stock para este producto."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE stocks SET id_producto=?, cantidad=? WHERE id_stock=?");
        mysqli_stmt_bind_param($st, 'iii', $id_producto, $cantidad, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Stock actualizado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ELIMINAR (eliminación física — tabla de inventario, no aplica desactivar) ──
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        $st = mysqli_prepare($conn, "SELECT id_stock FROM stocks WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El stock no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM stocks WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Stock eliminado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);