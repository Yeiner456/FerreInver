<?php
// api/pedidos.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET ?selects=1 → clientes activos para el select
if ($method === 'GET' && isset($_GET['selects'])) {
    $res = mysqli_query($conn, "SELECT Documento, nombre, correo FROM clientes WHERE estado_inicio_sesion = 'Activo' ORDER BY Nombre ASC");
    $rows = [];
    while ($f = mysqli_fetch_assoc($res)) $rows[] = $f;
    echo json_encode(["success" => true, "clientes" => $rows]);
    exit;
}

$medios_validos  = ['Efectivo', 'Tarjeta Débito', 'Tarjeta Crédito', 'Transferencia', 'PSE', 'Nequi', 'Daviplata'];
$estados_validos = ['Pendiente', 'Recibido', 'Listo para recibir', 'Cancelado'];

switch ($method) {

    // ─── LISTAR ────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "
            SELECT p.*, c.nombre AS nombre_cliente, c.correo
            FROM pedidos p
            INNER JOIN clientes c ON p.id_cliente = c.Documento
            ORDER BY p.fecha_hora DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $id_cliente    = $b['id_cliente'] ?? '';
        $medio_pago    = trim($b['medio_pago'] ?? '');
        $estado_pedido = trim($b['estado_pedido'] ?? '');

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($id_cliente) || $id_cliente <= 0) {
            echo json_encode(["success" => false, "message" => "ID de cliente inválido."]);
            exit;
        }
        if (!in_array($medio_pago, $medios_validos)) {
            echo json_encode(["success" => false, "message" => "Medio de pago inválido."]);
            exit;
        }
        if (!in_array($estado_pedido, $estados_validos)) {
            echo json_encode(["success" => false, "message" => "Estado del pedido inválido."]);
            exit;
        }

        // Verificar cliente
        $st = mysqli_prepare($conn, "SELECT Documento FROM clientes WHERE Documento = ?");
        mysqli_stmt_bind_param($st, 'i', $id_cliente);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El cliente no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO pedidos (id_cliente, Medio_pago, Estado_pedido) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iss', $id_cliente, $medio_pago, $estado_pedido);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Pedido registrado exitosamente."]);
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

        $id_cliente    = $b['id_cliente'] ?? '';
        $medio_pago    = trim($b['medio_pago'] ?? '');
        $estado_pedido = trim($b['estado_pedido'] ?? '');

        if (empty($id_cliente) || empty($medio_pago) || empty($estado_pedido)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($id_cliente) || $id_cliente <= 0) {
            echo json_encode(["success" => false, "message" => "ID de cliente inválido."]);
            exit;
        }
        if (!in_array($medio_pago, $medios_validos)) {
            echo json_encode(["success" => false, "message" => "Medio de pago inválido."]);
            exit;
        }
        if (!in_array($estado_pedido, $estados_validos)) {
            echo json_encode(["success" => false, "message" => "Estado del pedido inválido."]);
            exit;
        }

        // Verificar pedido existe
        $st = mysqli_prepare($conn, "SELECT id_pedido FROM pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El pedido no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar cliente
        $st = mysqli_prepare($conn, "SELECT Documento FROM clientes WHERE Documento = ?");
        mysqli_stmt_bind_param($st, 'i', $id_cliente);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El cliente no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE pedidos SET id_cliente=?, Medio_pago=?, Estado_pedido=? WHERE id_pedido=?");
        mysqli_stmt_bind_param($st, 'issi', $id_cliente, $medio_pago, $estado_pedido, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Pedido actualizado exitosamente."]);
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

        $st = mysqli_prepare($conn, "SELECT id_pedido FROM pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El pedido no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Pedido eliminado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);