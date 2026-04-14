<?php
// server/cotizaciones/api/cotizacionesCliente.php
// Trae las cotizaciones de un cliente específico por su documento

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
    SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
           co.valor_m2, co.total, co.fecha, co.estado,
           inv.nombre AS invernadero_nombre
    FROM cotizaciones co
    INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
    WHERE co.cliente_id = $documento
    ORDER BY co.fecha DESC
");

$rows = [];
while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;

echo json_encode(["success" => true, "data" => $rows]);
mysqli_close($conn);
?>