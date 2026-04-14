<?php
// server/pedidos/api/pedidosCliente.php
// Trae los pedidos de un cliente específico por su documento

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

if (!isset($_GET['documento']) || !is_numeric($_GET['documento'])) {
    echo json_encode(["success" => false, "mensaje" => "Documento inválido"]);
    exit;
}

$documento = intval($_GET['documento']);

$resultado = mysqli_query($conn, "
    SELECT p.id_pedido, p.fecha_hora, p.medio_pago, p.estado_pedido,
           GROUP_CONCAT(pp.descripcion, ' x', pp.cantidad SEPARATOR ' | ') AS productos
    FROM pedidos p
    LEFT JOIN productos_pedidos pp ON p.id_pedido = pp.id_pedido
    WHERE p.id_cliente = $documento
    GROUP BY p.id_pedido
    ORDER BY p.fecha_hora DESC
");

$rows = [];
while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;

echo json_encode(["success" => true, "data" => $rows]);
mysqli_close($conn);
?>