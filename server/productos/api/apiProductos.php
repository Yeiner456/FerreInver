<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once '../conexion.php';

$method = $_SERVER['REQUEST_METHOD'];

// ─── FUNCIÓN PARA SUBIR IMAGEN ──────────────────────────────────────────────
function subirImagen($file) {
    $uploadDir = __DIR__ . '/uploads/productos/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowedTypes))
        return ['ok' => false, 'msg' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
    if ($file['size'] > $maxSize)
        return ['ok' => false, 'msg' => 'La imagen no puede superar 2MB.'];

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('prod_', true) . '.' . $ext;
    $destPath = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath))
        return ['ok' => false, 'msg' => 'No se pudo guardar la imagen.'];

    // URL relativa que se guardará en BD
    $url = 'server/productos/api/uploads/productos/' . $filename;
    return ['ok' => true, 'url' => $url];
}

switch ($method) {

    // ─── LISTAR ─────────────────────────────────────────────────────────────
    case 'GET':
        $resultado = mysqli_query($conn, "SELECT * FROM productos ORDER BY id_producto DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        echo json_encode(["success" => true, "data" => $rows]);
        break;

    // ─── CREAR ──────────────────────────────────────────────────────────────
    case 'POST':
        // Ahora se recibe como multipart/form-data
        $nombre      = trim($_POST['nombre'] ?? '');
        $precio      = $_POST['precio'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '') {
            echo json_encode(["success" => false, "message" => "El nombre y el precio son obligatorios."]); exit;
        }
        if (strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "El nombre debe tener entre 1 y 30 caracteres."]); exit;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número mayor a 0."]); exit;
        }
        if (floor($precio) != $precio) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número entero."]); exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]); exit;
        }

        // Subir imagen si viene
        $imagenUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $res = subirImagen($_FILES['imagen']);
            if (!$res['ok']) { echo json_encode(["success" => false, "message" => $res['msg']]); exit; }
            $imagenUrl = $res['url'];
        }

        $st = mysqli_prepare($conn, "INSERT INTO productos (nombre, Precio, Descripcion, imagen) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'siss', $nombre, $precio, $descripcion, $imagenUrl);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto registrado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ACTUALIZAR ─────────────────────────────────────────────────────────
    case 'PUT':
        // PUT no soporta multipart nativamente; parseamos con php://input o usamos POST override
        // Usamos el truco: el frontend envía POST con ?_method=PUT  ← ver nota en el JSX
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        $nombre      = trim($_POST['nombre'] ?? '');
        $precio      = $_POST['precio'] ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        if (empty($nombre) || $precio === '') {
            echo json_encode(["success" => false, "message" => "El nombre y el precio son obligatorios."]); exit;
        }
        if (strlen($nombre) > 30) {
            echo json_encode(["success" => false, "message" => "El nombre no puede exceder 30 caracteres."]); exit;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número mayor a 0."]); exit;
        }
        if (floor($precio) != $precio) {
            echo json_encode(["success" => false, "message" => "El precio debe ser un número entero."]); exit;
        }
        if (strlen($descripcion) > 100) {
            echo json_encode(["success" => false, "message" => "La descripción no puede exceder 100 caracteres."]); exit;
        }

        // Verificar que existe y obtener imagen actual
        $st = mysqli_prepare($conn, "SELECT imagen FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $result = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]); exit;
        }
        $productoActual = mysqli_fetch_assoc($result);
        $imagenUrl = $productoActual['imagen']; // Conservar imagen anterior por defecto
        mysqli_stmt_close($st);

        // Si viene nueva imagen, reemplazar
        if (!empty($_FILES['imagen']['name'])) {
            $res = subirImagen($_FILES['imagen']);
            if (!$res['ok']) { echo json_encode(["success" => false, "message" => $res['msg']]); exit; }
            // Borrar imagen anterior del servidor si existía
            if ($imagenUrl) {
                $oldPath = __DIR__ . '/../../../' . $imagenUrl;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $imagenUrl = $res['url'];
        }

        $st = mysqli_prepare($conn, "UPDATE productos SET nombre=?, Precio=?, Descripcion=?, imagen=? WHERE id_producto=?");
        mysqli_stmt_bind_param($st, 'sissi', $nombre, $precio, $descripcion, $imagenUrl, $id);
        if (mysqli_stmt_execute($st))
            echo json_encode(["success" => true, "message" => "Producto actualizado exitosamente."]);
        else
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        mysqli_stmt_close($st);
        break;

    // ─── ELIMINAR ───────────────────────────────────────────────────────────
    case 'DELETE':
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(["success" => false, "message" => "ID inválido."]); exit;
        }
        $id = $_GET['id'];

        $st = mysqli_prepare($conn, "SELECT imagen FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $result = mysqli_stmt_get_result($st);
        if (mysqli_num_rows($result) === 0) {
            echo json_encode(["success" => false, "message" => "El producto no existe."]); exit;
        }
        $prod = mysqli_fetch_assoc($result);
        mysqli_stmt_close($st);

        $st = mysqli_prepare($conn, "DELETE FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        if (mysqli_stmt_execute($st)) {
            // Borrar imagen del servidor también
            if ($prod['imagen']) {
                $oldPath = __DIR__ . '/../../../' . $prod['imagen'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            echo json_encode(["success" => true, "message" => "Producto eliminado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error: " . mysqli_error($conn)]);
        }
        mysqli_stmt_close($st);
        break;

    default:
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Método no permitido."]);
}

mysqli_close($conn);    