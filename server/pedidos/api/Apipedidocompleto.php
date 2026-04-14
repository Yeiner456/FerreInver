<?php
// server/pedidos/api/apiPedidoCompleto.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

require_once '../conexion.php';

$b = json_decode(file_get_contents("php://input"), true);

$id_cliente = $b['id_cliente'] ?? '';
$medio_pago = trim($b['medio_pago'] ?? '');
$items      = $b['items'] ?? [];

// ── Validaciones básicas ──────────────────────────────────────────────────
if (empty($id_cliente) || empty($medio_pago) || empty($items)) {
    echo json_encode(["success" => false, "message" => "Faltan datos obligatorios."]);
    exit;
}

$medios_validos = ['Efectivo', 'Tarjeta Débito', 'Tarjeta Crédito', 'Transferencia', 'PSE', 'Nequi', 'Daviplata'];
if (!in_array($medio_pago, $medios_validos)) {
    echo json_encode(["success" => false, "message" => "Medio de pago inválido."]);
    exit;
}

if (!is_array($items) || count($items) === 0) {
    echo json_encode(["success" => false, "message" => "El carrito está vacío."]);
    exit;
}

// ── Verificar que el cliente existe ──────────────────────────────────────
$st = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
mysqli_stmt_bind_param($st, 'i', $id_cliente);
mysqli_stmt_execute($st);
if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
    echo json_encode(["success" => false, "message" => "El cliente no existe."]);
    exit;
}
mysqli_stmt_close($st);

// ── Transacción ───────────────────────────────────────────────────────────
mysqli_begin_transaction($conn);

try {
    // 1. Insertar pedido
    $st = mysqli_prepare($conn, "INSERT INTO pedidos (id_cliente, medio_pago, estado_pedido) VALUES (?, ?, 'pendiente')");
    mysqli_stmt_bind_param($st, 'is', $id_cliente, $medio_pago);
    mysqli_stmt_execute($st);
    $id_pedido = mysqli_insert_id($conn);
    mysqli_stmt_close($st);

    // 2. Insertar cada item en productos_pedidos
    $st = mysqli_prepare($conn, "INSERT INTO productos_pedidos (id_producto, id_pedido, descripcion, cantidad) VALUES (?, ?, ?, ?)");

    foreach ($items as $item) {
        $id_producto = (int)($item['id_producto'] ?? 0);
        $cantidad    = (int)($item['cantidad']    ?? 1);
        $descripcion = substr(trim($item['nombre'] ?? 'Producto'), 0, 100);

        if ($id_producto <= 0 || $cantidad <= 0) continue;

        mysqli_stmt_bind_param($st, 'iisi', $id_producto, $id_pedido, $descripcion, $cantidad);
        mysqli_stmt_execute($st);
    }
    mysqli_stmt_close($st);

    mysqli_commit($conn);

    echo json_encode([
        "success"   => true,
        "message"   => "Pedido registrado exitosamente.",
        "id_pedido" => $id_pedido
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => "Error al registrar el pedido: " . $e->getMessage()]);
}

mysqli_close($conn);