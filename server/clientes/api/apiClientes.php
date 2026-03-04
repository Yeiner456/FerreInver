<?php
// api/clientes.php — Endpoint REST para el CRUD de clientes

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Responder preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // ─── LISTAR CLIENTES ───────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "
            SELECT c.documento, c.nombre, c.correo, c.fecha_registro,
                c.estado_inicio_sesion,
                t.nombre AS tipo_usuario
            FROM clientes c
            LEFT JOIN tipos_usuarios t ON c.id_tipo_de_usuario = t.id_tipo_de_usuario
            ORDER BY c.fecha_registro DESC
        ");

        $clientes = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $clientes[] = $fila;
        }

        echo json_encode(["success" => true, "data" => $clientes]);
        break;

    // ─── CREAR CLIENTE ─────────────────────────────────────────────────────
    case 'POST':
        $body = json_decode(file_get_contents("php://input"), true);

        // Validar campos obligatorios
        $required = ['documento','id_tipo_de_usuario','nombre','correo','password','confirmar_password','estado'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                echo json_encode(["success" => false, "message" => "El campo '$field' es obligatorio."]);
                exit;
            }
        }

        $documento          = trim($body['documento']);
        $id_tipo_de_usuario = trim($body['id_tipo_de_usuario']);
        $nombre             = trim($body['nombre']);
        $correo             = trim($body['correo']);
        $password           = trim($body['password']);
        $confirmar_password = trim($body['confirmar_password']);
        $estado             = trim($body['estado']);

        // Validaciones
        if (!is_numeric($documento) || $documento <= 0 || strlen($documento) > 11) {
            echo json_encode(["success" => false, "message" => "Documento inválido."]);
            exit;
        }
        if (!is_numeric($id_tipo_de_usuario) || $id_tipo_de_usuario <= 0) {
            echo json_encode(["success" => false, "message" => "Tipo de usuario inválido."]);
            exit;
        }
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "Nombre inválido (solo letras, máx 30 chars)."]);
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50) {
            echo json_encode(["success" => false, "message" => "Correo inválido."]);
            exit;
        }
        if ($password !== $confirmar_password) {
            echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden."]);
            exit;
        }
        if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
            echo json_encode(["success" => false, "message" => "Contraseña inválida (mín 6 chars, letras y números)."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Verificar tipo de usuario existe
        $stmt_tipo = mysqli_prepare($conn, "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?");
        mysqli_stmt_bind_param($stmt_tipo, 'i', $id_tipo_de_usuario);
        mysqli_stmt_execute($stmt_tipo);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_tipo)) === 0) {
            echo json_encode(["success" => false, "message" => "Tipo de usuario no existe."]);
            exit;
        }
        mysqli_stmt_close($stmt_tipo);

        // Verificar duplicado documento
        $stmt_ck = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($stmt_ck, 'i', $documento);
        mysqli_stmt_execute($stmt_ck);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ck)) > 0) {
            echo json_encode(["success" => false, "message" => "El documento ya está registrado."]);
            exit;
        }
        mysqli_stmt_close($stmt_ck);

        // Verificar duplicado correo
        $stmt_cm = mysqli_prepare($conn, "SELECT correo FROM clientes WHERE correo = ?");
        mysqli_stmt_bind_param($stmt_cm, 's', $correo);
        mysqli_stmt_execute($stmt_cm);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_cm)) > 0) {
            echo json_encode(["success" => false, "message" => "El correo ya está registrado."]);
            exit;
        }
        mysqli_stmt_close($stmt_cm);

        // Insertar
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO clientes (documento, id_tipo_de_usuario, password_hash, nombre, correo, estado_inicio_sesion) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'iissss', $documento, $id_tipo_de_usuario, $password_hash, $nombre, $correo, $estado);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Cliente registrado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ─── ACTUALIZAR CLIENTE ────────────────────────────────────────────────
    case 'PUT':
        if (!isset($_GET['documento']) || !is_numeric($_GET['documento'])) {
            echo json_encode(["success" => false, "message" => "Documento inválido."]);
            exit;
        }

        $documento = $_GET['documento'];
        $body = json_decode(file_get_contents("php://input"), true);

        $id_tipo_de_usuario = trim($body['id_tipo_de_usuario'] ?? '');
        $nombre             = trim($body['nombre'] ?? '');
        $correo             = trim($body['correo'] ?? '');
        $estado             = trim($body['estado'] ?? '');
        $password           = trim($body['password'] ?? '');
        $confirmar_password = trim($body['confirmar_password'] ?? '');

        // Validaciones básicas
        if (empty($id_tipo_de_usuario) || empty($nombre) || empty($correo) || empty($estado)) {
            echo json_encode(["success" => false, "message" => "Todos los campos obligatorios deben estar llenos."]);
            exit;
        }
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "Nombre inválido."]);
            exit;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50) {
            echo json_encode(["success" => false, "message" => "Correo inválido."]);
            exit;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            echo json_encode(["success" => false, "message" => "Estado inválido."]);
            exit;
        }

        // Verificar correo en otro cliente
        $stmt_cm = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE correo = ? AND documento != ?");
        mysqli_stmt_bind_param($stmt_cm, 'si', $correo, $documento);
        mysqli_stmt_execute($stmt_cm);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_cm)) > 0) {
            echo json_encode(["success" => false, "message" => "El correo ya está registrado en otro cliente."]);
            exit;
        }
        mysqli_stmt_close($stmt_cm);

        // Contraseña opcional
        $actualizar_password = false;
        $password_hash = '';

        if (!empty($password) || !empty($confirmar_password)) {
            if ($password !== $confirmar_password) {
                echo json_encode(["success" => false, "message" => "Las contraseñas no coinciden."]);
                exit;
            }
            if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
                echo json_encode(["success" => false, "message" => "Contraseña inválida."]);
                exit;
            }
            $actualizar_password = true;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($actualizar_password) {
            $stmt = mysqli_prepare($conn, "UPDATE clientes SET id_tipo_de_usuario=?, password_hash=?, nombre=?, correo=?, estado_inicio_sesion=? WHERE documento=?");
            mysqli_stmt_bind_param($stmt, 'issssi', $id_tipo_de_usuario, $password_hash, $nombre, $correo, $estado, $documento);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE clientes SET id_tipo_de_usuario=?, nombre=?, correo=?, estado_inicio_sesion=? WHERE documento=?");
            mysqli_stmt_bind_param($stmt, 'isssi', $id_tipo_de_usuario, $nombre, $correo, $estado, $documento);
        }

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Cliente actualizado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al actualizar: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($stmt);
        break;

    // ─── ELIMINAR CLIENTE ──────────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['documento']) || !is_numeric($_GET['documento'])) {
            echo json_encode(["success" => false, "message" => "Documento inválido."]);
            exit;
        }

        $documento = $_GET['documento'];

        $stmt_ck = mysqli_prepare($conn, "SELECT documento FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($stmt_ck, 'i', $documento);
        mysqli_stmt_execute($stmt_ck);
        if (mysqli_num_rows(mysqli_stmt_get_result($stmt_ck)) === 0) {
            echo json_encode(["success" => false, "message" => "El cliente no existe."]);
            exit;
        }
        mysqli_stmt_close($stmt_ck);

        $stmt = mysqli_prepare($conn, "DELETE FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($stmt, 'i', $documento);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(["success" => true, "message" => "Cliente eliminado exitosamente."]);
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