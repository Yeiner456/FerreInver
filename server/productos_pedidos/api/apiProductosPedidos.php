<?php
// api/productos_pedidos.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET ?selects=1 → productos y pedidos para los selects
if ($method === 'GET' && isset($_GET['selects'])) {
    $prods = mysqli_query($conn, "SELECT ID_producto, nombre FROM productos ORDER BY nombre ASC");
    $peds  = mysqli_query($conn, "SELECT id_pedido FROM pedidos ORDER BY id_pedido ASC");
    $ps = []; while ($r = mysqli_fetch_assoc($prods)) $ps[] = $r;
    $pe = []; while ($r = mysqli_fetch_assoc($peds))  $pe[] = $r;
    echo json_encode(["success" => true, "productos" => $ps, "pedidos" => $pe]);
    exit;
}

switch ($method) {

    // ─── LISTAR ────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "
            SELECT pp.id, pp.descripcion, pp.cantidad,
                    p.nombre  AS nombre_producto,
                    pe.id_pedido
            FROM Productos_Pedidos pp
            INNER JOIN Productos p  ON pp.id_producto = p.ID_producto
            INNER JOIN Pedidos   pe ON pp.id_pedido   = pe.id_pedido
            ORDER BY pp.id DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $id_producto = $b['id_producto'] ?? '';
        $id_pedido   = $b['id_pedido']   ?? '';
        $descripcion = trim($b['descripcion'] ?? '');
        $cantidad    = $b['cantidad'] ?? '';

        if (empty($id_producto) || empty($id_pedido) || empty($descripcion) || $cantidad === '') {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
            echo json_encode(["success" => false, "message" => "La descripción contiene caracteres no permitidos."]);
            exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser entre 1 y 1000."]);
            exit;
        }

        // Verificar producto
        $st = mysqli_prepare($conn, "SELECT ID_producto FROM Productos WHERE ID_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar pedido
        $st = mysqli_prepare($conn, "SELECT id_pedido FROM Pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id_pedido);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El pedido no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO Productos_Pedidos (id_producto, id_pedido, descripcion, cantidad) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iisi', $id_producto, $id_pedido, $descripcion, $cantidad);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto-Pedido registrado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ACTUALIZAR (solo descripcion y cantidad) ──────────────────────────
    case 'PUT':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];
        $b  = json_decode(file_get_contents("php://input"), true);

        $descripcion = trim($b['descripcion'] ?? '');
        $cantidad    = $b['cantidad'] ?? '';

        if (empty($descripcion) || $cantidad === '') {
            echo json_encode(["success" => false, "message" => "Descripción y cantidad son obligatorios."]);
            exit;
        }
        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
            echo json_encode(["success" => false, "message" => "La descripción contiene caracteres no permitidos."]);
            exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]);
            exit;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000) {
            echo json_encode(["success" => false, "message" => "La cantidad debe ser entre 1 y 1000."]);
            exit;
        }

        // Verificar que existe
        $st = mysqli_prepare($conn, "SELECT id FROM Productos_Pedidos WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El registro no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE Productos_Pedidos SET descripcion=?, cantidad=? WHERE id=?");
        mysqli_stmt_bind_param($st, 'sii', $descripcion, $cantidad, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Registro actualizado exitosamente."]);
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

        $st = mysqli_prepare($conn, "SELECT id FROM Productos_Pedidos WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El registro no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM Productos_Pedidos WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Registro eliminado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);