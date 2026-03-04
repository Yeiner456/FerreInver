<?php
// api/tipos_usuarios.php — Endpoint REST para el CRUD de tipos de usuario

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ─── LISTAR TIPOS ──────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "SELECT * FROM tipos_usuarios ORDER BY id_tipo_de_usuario DESC");
        $tipos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $tipos[] = $fila;
        }
        echo json_encode(["success" => true, "data" => $tipos]);
        break;

    // ─── CREAR TIPO ────────────────────────────────────────────────────────
    case 'POST':
        $body = json_decode(file_get_contents("php://input"), true);

        if (empty($body['nombre']) || empty($body['estado'])) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        $nombre = trim($body['nombre']);
        $estado = trim($body['estado']);

        if (strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 30 caracteres."]);
            exit;
        }
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            echo json_encode(["success" => false, "message" => "El nombre solo puede contener letras y espacios."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "El estado no es válido."]);
            exit;
        }

        $stmt_ck = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ?");
        mysqli_stmt_bind_param($stmt_ck, 's', $nombre);
        mysqli_stmt_execute($stmt_ck);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ck)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe un tipo de usuario con ese nombre."]);
            exit;
        }
        mysqli_stmt_close($stmt_ck);

        $stmt = mysqli_prepare($conn, "INSERT INTO tipos_usuarios (nombre, estado) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $estado);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Tipo de usuario registrado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ─── ACTUALIZAR TIPO ───────────────────────────────────────────────────
    case 'PUT':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]);
            exit;
        }

        $id   = $_GET['id'];
        $body = json_decode(file_get_contents("php://input"), true);

        if (empty($body['nombre']) || empty($body['estado'])) {
            echo json_encode(["success" => false, "message" => "Todos los campos son obligatorios."]);
            exit;
        }

        $nombre = trim($body['nombre']);
        $estado = trim($body['estado']);

        if (strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 30 caracteres."]);
            exit;
        }
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre)) {
            echo json_encode(["success" => false, "message" => "El nombre solo puede contener letras y espacios."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "El estado no es válido."]);
            exit;
        }

        $stmt_ex = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
        mysqli_stmt_bind_param($stmt_ex, 'i', $id);
        mysqli_stmt_execute($stmt_ex);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ex)) === 0) {
            echo json_encode(["success" => false, "message" => "El tipo de usuario no existe."]);
            exit;
        }
        mysqli_stmt_close($stmt_ex);

        $stmt_ck = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ? AND id_tipo_de_usuario != ?");
        mysqli_stmt_bind_param($stmt_ck, 'si', $nombre, $id);
        mysqli_stmt_execute($stmt_ck);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ck)) > 0) {
            echo json_encode(["success" => false, "message" => "Ya existe otro tipo de usuario con ese nombre."]);
            exit;
        }
        mysqli_stmt_close($stmt_ck);

        $stmt = mysqli_prepare($conn, "UPDATE tipos_usuarios SET nombre = ?, estado = ? WHERE id_tipo_de_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $estado, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Tipo de usuario actualizado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ─── ELIMINAR TIPO ─────────────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]);
            exit;
        }

        $id = $_GET['id'];

        $stmt_ex = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
        mysqli_stmt_bind_param($stmt_ex, 'i', $id);
        mysqli_stmt_execute($stmt_ex);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ex)) === 0) {
            echo json_encode(["success" => false, "message" => "El tipo de usuario no existe."]);
            exit;
        }
        mysqli_stmt_close($stmt_ex);

        $stmt_ref = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE id_tipo_de_usuario = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt_ref, 'i', $id);
        mysqli_stmt_execute($stmt_ref);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ref)) > 0) {
            echo json_encode(["success" => false, "message" => "No se puede eliminar: hay clientes asociados a este tipo de usuario."]);
            exit;
        }
        mysqli_stmt_close($stmt_ref);

        $stmt = mysqli_prepare($conn, "DELETE FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Tipo de usuario eliminado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al eliminar: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);