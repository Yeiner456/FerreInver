<?php
// api/proveedores.php

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
        $resultado = mysqli_query($conn, "SELECT * FROM proveedores ORDER BY nit_proveedor DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ─────────────────────────────────────────────────────────────
    case 'POST':
        $b = json_decode(file_get_contents("php://input"), true);

        $nit       = trim($b['nit'] ?? '');
        $correo    = trim($b['correo'] ?? '');
        $direccion = trim($b['direccion'] ?? '');
        $telefono  = trim($b['telefono'] ?? '');
        $estado    = trim($b['estado'] ?? '');

        if (empty($nit) || empty($correo) || empty($direccion) || empty($telefono) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!is_numeric($nit) || $nit <= 0 || strlen($nit) > 11) {
            echo json_encode(["success" => false, "message" => "El NIT debe ser un número válido de máximo 11 dígitos."]);
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80) {
            echo json_encode(["success" => false, "message" => "El correo no es válido o excede 80 caracteres."]);
            exit;
        }
        if (strlen($direccion) == 0 || strlen($direccion) > 80) {
            echo json_encode(["success" => false, "message" => "La dirección debe tener entre 1 y 80 caracteres."]);
            exit;
        }
        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20) {
            echo json_encode(["success" => false, "message" => "Teléfono inválido. Solo números, espacios, guiones, paréntesis y +"]);
            exit;
        }
        $solo_numeros = preg_replace("/[^0-9]/", "", $telefono);
        if (strlen($solo_numeros) < 7) {
            echo json_encode(["success" => false, "message" => "El teléfono debe tener al menos 7 dígitos."]);
            exit;
        }
        if ($estado !== 'Activo' && $estado !== 'Inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // NIT duplicado
        $st = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
        mysqli_stmt_bind_param($st, 'i', $nit);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "El NIT ya está registrado."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Correo duplicado
        $st = mysqli_prepare($conn, "SELECT correo FROM proveedores WHERE correo = ?");
        mysqli_stmt_bind_param($st, 's', $correo);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "El correo ya está registrado."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "INSERT INTO proveedores (nit_proveedor, correo, direccion, telefono, estado) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'issss', $nit, $correo, $direccion, $telefono, $estado);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Proveedor registrado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ACTUALIZAR ────────────────────────────────────────────────────────
    case 'PUT':
        if (!isset($_GET['nit']) || !is_numeric($_GET['nit'])) {
            echo json_encode(["success" => false, "message" => "NIT inválido."]); exit;
        }
        $nit = $_GET['nit'];
        $b   = json_decode(file_get_contents("php://input"), true);

        $correo    = trim($b['correo'] ?? '');
        $direccion = trim($b['direccion'] ?? '');
        $telefono  = trim($b['telefono'] ?? '');
        $estado    = trim($b['estado'] ?? '');

        if (empty($correo) || empty($direccion) || empty($telefono) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80) {
            echo json_encode(["success" => false, "message" => "El correo no es válido o excede 80 caracteres."]);
            exit;
        }
        if (strlen($direccion) == 0 || strlen($direccion) > 80) {
            echo json_encode(["success" => false, "message" => "La dirección debe tener entre 1 y 80 caracteres."]);
            exit;
        }
        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20) {
            echo json_encode(["success" => false, "message" => "Teléfono inválido."]);
            exit;
        }
        $solo_numeros = preg_replace("/[^0-9]/", "", $telefono);
        if (strlen($solo_numeros) < 7) {
            echo json_encode(["success" => false, "message" => "El teléfono debe tener al menos 7 dígitos."]);
            exit;
        }
        if ($estado !== 'Activo' && $estado !== 'Inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Existe
        $st = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
        mysqli_stmt_bind_param($st, 'i', $nit);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El proveedor no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Correo duplicado en otro proveedor
        $st = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE correo = ? AND nit_proveedor != ?");
        mysqli_stmt_bind_param($st, 'si', $correo, $nit);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "El correo ya está registrado en otro proveedor."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "UPDATE proveedores SET correo=?, direccion=?, telefono=?, estado=? WHERE nit_proveedor=?");
        mysqli_stmt_bind_param($st, 'ssssi', $correo, $direccion, $telefono, $estado, $nit);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Proveedor actualizado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ELIMINAR ──────────────────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['nit']) || !is_numeric($_GET['nit'])) {
            echo json_encode(["success" => false, "message" => "NIT inválido."]); exit;
        }
        $nit = $_GET['nit'];

        $st = mysqli_prepare($conn, "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
        mysqli_stmt_bind_param($st, 'i', $nit);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) === 0) {
            echo json_encode(["success" => false, "message" => "El proveedor no existe."]);
            exit;
        }
        mysqli_stmt_close($st);

        // Integridad con compras
        $st = mysqli_prepare($conn, "SELECT ID_compra FROM Compras WHERE ID_proveedor = ? LIMIT 1");
        mysqli_stmt_bind_param($st, 'i', $nit);
        mysqli_stmt_execute($st);
        if (mysqli_num_rows(mysqli_stmt_get_result($st)) > 0) {
            echo json_encode(["success" => false, "message" => "No se puede eliminar: hay compras asociadas a este proveedor."]);
            exit;
        }
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM proveedores WHERE nit_proveedor = ?");
        mysqli_stmt_bind_param($st, 'i', $nit);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Proveedor eliminado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);