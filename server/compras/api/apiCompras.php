<?php
// api/compras.php

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
        $resultado = mysqli_query($conn, "
            SELECT c.id_compra, c.cantidad, c.descripcion,
                p.id_producto, p.nombre AS nombre_producto,
                pr.nit_proveedor, pr.correo AS correo_proveedor
            FROM compras c
            INNER JOIN productos p  ON c.id_producto  = p.id_producto
            INNER JOIN proveedores pr ON c.id_proveedor = pr.nit_proveedor
            ORDER BY c.id_compra DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        // ¿piden los datos de selects?
        if (isset($_GET['selects'])) {
            $productos   = mysqli_query($conn, "SELECT id_producto, nombre FROM productos WHERE estado_producto = 'activo' ORDER BY nombre");
            $proveedores = mysqli_query($conn, "SELECT nit_proveedor, correo FROM proveedores WHERE estado = 'activo' ORDER BY correo");
            $ps = []; while($r = mysqli_fetch_assoc($productos))   $ps[] = $r;
            $pv = []; while($r = mysqli_fetch_assoc($proveedores)) $pv[] = $r;
            echo json_encode(["success" => true, "productos" => $ps, "proveedores" => $pv]);
            exit;
        }

        $b = json_decode(file_get_contents("php://input"), true);

        $cantidad     = $b['cantidad'] ?? '';
        $descripcion  = trim($b['descripcion'] ?? '');
        $id_producto  = $b['id_producto'] ?? '';
        $id_proveedor = $b['id_proveedor'] ?? '';

        if (empty($cantidad) || empty($descripcion) || empty($id_producto) || empty($id_proveedor)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser un número mayor a 0."]);
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion)) {
            echo json_encode(["success" => false, "message" => "La descripción solo puede contener letras, números y espacios."]);
            exit;
        }
        if (strlen($descripcion) > 150) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 150 caracteres."]);
            exit;
        }

        // Verificar producto
        $st = mysqli_prepare($conn, "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto seleccionado no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar proveedor
        $st = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
        mysqli_stmt_bind_param($st, 'i', $id_proveedor);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El proveedor seleccionado no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO compras (cantidad, descripcion, id_proveedor, id_producto) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'isii', $cantidad, $descripcion, $id_proveedor, $id_producto);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Compra registrada exitosamente."]);
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

        $cantidad    = $b['cantidad'] ?? '';
        $descripcion = trim($b['descripcion'] ?? '');

        if (empty($cantidad) || empty($descripcion)) {
            echo json_encode(["success" => false, "message" => "Cantidad y descripción son obligatorios."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser un número mayor a 0."]);
            exit;
        }
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion)) {
            echo json_encode(["success" => false, "message" => "La descripción solo puede contener letras, números y espacios."]);
            exit;
        }
        if (strlen($descripcion) > 150) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 150 caracteres."]);
            exit;
        }

        // Existe
        $st = mysqli_prepare($conn, "SELECT id_compra FROM compras WHERE id_compra = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "La compra no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE compras SET cantidad=?, descripcion=? WHERE id_compra=?");
        mysqli_stmt_bind_param($st, 'isi', $cantidad, $descripcion, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Compra actualizada exitosamente."]);
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

        $st = mysqli_prepare($conn, "SELECT id_compra FROM compras WHERE id_compra = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "La compra no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM compras WHERE id_compra = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Compra eliminada exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);