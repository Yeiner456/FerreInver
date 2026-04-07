<?php
// api/cotizaciones.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// Endpoint especial: GET ?selects=1 → clientes e invernaderos activos
if ($method === 'GET' && isset($_GET['selects'])) {
    $clientes     = mysqli_query($conn, "SELECT documento, nombre FROM clientes WHERE estado_inicio_sesion = 'activo' ORDER BY nombre");
    $invernaderos = mysqli_query($conn, "SELECT id_invernadero, nombre, precio_m2 FROM invernaderos WHERE estado = 'activo' ORDER BY nombre");
    $cl  = []; while ($r = mysqli_fetch_assoc($clientes))     $cl[]  = $r;
    $inv = []; while ($r = mysqli_fetch_assoc($invernaderos)) $inv[] = $r;
    echo json_encode(["success" => true, "clientes" => $cl, "invernaderos" => $inv]);
    exit;
}

switch ($method) {

    // ─── LISTAR ────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "
            SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
                   co.valor_m2, co.total, co.fecha, co.estado,
                   cl.nombre  AS cliente_nombre,
                   inv.nombre AS invernadero_nombre,
                   co.cliente_id, co.invernadero_id
            FROM cotizaciones co
            INNER JOIN clientes     cl  ON co.cliente_id     = cl.documento
            INNER JOIN invernaderos inv ON co.invernadero_id  = inv.id_invernadero
            ORDER BY co.fecha DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $cliente_id       = $b['cliente_id']       ?? '';
        $invernadero_id   = $b['invernadero_id']   ?? '';
        $largo            = $b['largo']            ?? '';
        $ancho            = $b['ancho']            ?? '';
        $metros_cuadrados = $b['metros_cuadrados'] ?? '';
        $valor_m2         = $b['valor_m2']         ?? '';
        $total            = $b['total']            ?? '';
        $estado           = trim($b['estado']      ?? '');

        if (empty($cliente_id) || empty($invernadero_id) || empty($largo) || empty($ancho) ||
            empty($metros_cuadrados) || empty($valor_m2) || empty($total) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        foreach (['largo' => $largo, 'ancho' => $ancho, 'metros_cuadrados' => $metros_cuadrados,
                  'valor_m2' => $valor_m2, 'total' => $total] as $campo => $val) {
            if (!is_numeric($val) || $val <= 0) {
                echo json_encode(["success" => false, "message" => "El campo $campo debe ser un número mayor a 0."]);
                exit;
            }
        }

        if (abs(round($largo * $ancho, 2) - round($metros_cuadrados, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "Los metros cuadrados no coinciden con largo × ancho."]);
            exit;
        }

        if (!in_array($estado, ['pendiente', 'aprobada', 'rechazada'])) {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Verificar cliente
        $st = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($st, 'i', $cliente_id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El cliente no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar invernadero y precio
        $st = mysqli_prepare($conn, "SELECT precio_m2 FROM invernaderos WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $invernadero_id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($res) === 0) {
            echo json_encode(["success" => false, "message" => "El invernadero no existe."]);
            exit;
        }
        $inv_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);

        if (abs(round($inv_data['precio_m2'], 2) - round($valor_m2, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "El valor m² no coincide con el precio del invernadero."]);
            exit;
        }

        if (abs(round($metros_cuadrados * $valor_m2, 2) - round($total, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "El total no coincide con metros cuadrados × valor m²."]);
            exit;
        }

        $st = mysqli_prepare($conn, "INSERT INTO cotizaciones (cliente_id, invernadero_id, largo, ancho, metros_cuadrados, valor_m2, total, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iiddddds', $cliente_id, $invernadero_id, $largo, $ancho, $metros_cuadrados, $valor_m2, $total, $estado);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Cotización registrada exitosamente."]);
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

        $cliente_id       = $b['cliente_id']       ?? '';
        $invernadero_id   = $b['invernadero_id']   ?? '';
        $largo            = $b['largo']            ?? '';
        $ancho            = $b['ancho']            ?? '';
        $metros_cuadrados = $b['metros_cuadrados'] ?? '';
        $valor_m2         = $b['valor_m2']         ?? '';
        $total            = $b['total']            ?? '';
        $estado           = trim($b['estado']      ?? '');

        if (empty($cliente_id) || empty($invernadero_id) || empty($largo) || empty($ancho) ||
            empty($metros_cuadrados) || empty($valor_m2) || empty($total) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        foreach (['largo' => $largo, 'ancho' => $ancho, 'metros_cuadrados' => $metros_cuadrados,
                  'valor_m2' => $valor_m2, 'total' => $total] as $campo => $val) {
            if (!is_numeric($val) || $val <= 0) {
                echo json_encode(["success" => false, "message" => "El campo $campo debe ser un número mayor a 0."]);
                exit;
            }
        }

        if (abs(round($largo * $ancho, 2) - round($metros_cuadrados, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "Los metros cuadrados no coinciden con largo × ancho."]);
            exit;
        }

        if (!in_array($estado, ['pendiente', 'aprobada', 'rechazada'])) {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Existe cotización
        $st = mysqli_prepare($conn, "SELECT id_cotizacion FROM cotizaciones WHERE id_cotizacion = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "La cotización no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar cliente
        $st = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($st, 'i', $cliente_id);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El cliente no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Verificar invernadero y precio
        $st = mysqli_prepare($conn, "SELECT precio_m2 FROM invernaderos WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $invernadero_id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($res) === 0) {
            echo json_encode(["success" => false, "message" => "El invernadero no existe."]);
            exit;
        }
        $inv_data = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);

        if (abs(round($inv_data['precio_m2'], 2) - round($valor_m2, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "El valor m² no coincide con el precio del invernadero."]);
            exit;
        }

        if (abs(round($metros_cuadrados * $valor_m2, 2) - round($total, 2)) > 0.01) {
            echo json_encode(["success" => false, "message" => "El total no coincide con metros cuadrados × valor m²."]);
            exit;
        }

        $st = mysqli_prepare($conn, "UPDATE cotizaciones SET cliente_id=?, invernadero_id=?, largo=?, ancho=?, metros_cuadrados=?, valor_m2=?, total=?, estado=? WHERE id_cotizacion=?");
        mysqli_stmt_bind_param($st, 'iidddddsi', $cliente_id, $invernadero_id, $largo, $ancho, $metros_cuadrados, $valor_m2, $total, $estado, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Cotización actualizada exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── RECHAZAR COTIZACIÓN ───────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        // Verificar que existe
        $st = mysqli_prepare($conn, "SELECT id_cotizacion, estado FROM cotizaciones WHERE id_cotizacion = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($res) === 0) {
            echo json_encode(["success" => false, "message" => "La cotización no existe."]);
            exit;
        }
        $cot_actual = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);

        // Verificar que no esté ya rechazada
        if ($cot_actual['estado'] === 'rechazada') {
            echo json_encode(["success" => false, "message" => "La cotización ya está rechazada."]);
            exit;
        }

        $st = mysqli_prepare($conn, "UPDATE cotizaciones SET estado = 'rechazada' WHERE id_cotizacion = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Cotización rechazada exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);