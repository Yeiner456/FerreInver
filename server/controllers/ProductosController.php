<?php

require_once __DIR__ . '/../models/ProductosModel.php';
require_once __DIR__ . '/../core/Response.php';

class ProductosController {

    private ProductosModel $model;

    // Ruta física donde se guardan las imágenes
    // server/uploads/productos/
    private string $uploadDir;

    // Prefijo de URL que se guarda en la BD y usa el JSX
    // server/uploads/productos/nombre.jpg
    private string $uploadUrl = 'server/uploads/productos/';

    public function __construct() {
        $this->model     = new ProductosModel();
        $this->uploadDir = __DIR__ . '/../uploads/productos/';
    }

    // ─── GET /productos ──────────────────────────────────────────────────────
    public function index(): void {
        $data = $this->model->getAll();
        Response::success("OK", $data);
    }

    // ─── POST /productos ─────────────────────────────────────────────────────
    // Recibe FormData (multipart) porque puede traer imagen
    public function create(): void {
        $nombre      = trim($_POST['nombre']      ?? '');
        $precio      = $_POST['precio']           ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        // Validaciones
        if (empty($nombre) || $precio === '') {
            Response::error("El nombre y el precio son obligatorios.", 400); return;
        }
        if (strlen($nombre) > 30) {
            Response::error("El nombre debe tener entre 1 y 30 caracteres.", 400); return;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            Response::error("El precio debe ser un número mayor a 0.", 400); return;
        }
        if (floor($precio) != $precio) {
            Response::error("El precio debe ser un número entero.", 400); return;
        }
        if (strlen($descripcion) > 100) {
            Response::error("La descripción no puede exceder 100 caracteres.", 400); return;
        }

        // Imagen (opcional)
        $imagenUrl = null;
        if (!empty($_FILES['imagen']['name'])) {
            $res = $this->subirImagen($_FILES['imagen']);
            if (!$res['ok']) { Response::error($res['msg'], 400); return; }
            $imagenUrl = $res['url'];
        }

        if ($this->model->create($nombre, (int)$precio, $descripcion, $imagenUrl))
            Response::success("Producto registrado exitosamente.", null, 201);
        else
            Response::error("Error al registrar el producto.", 500);
    }

    // ─── PUT /productos?id=X ─────────────────────────────────────────────────
    // El JSX envía FormData con POST + ?_method=PUT
    // En index.php lo capturamos como método PUT directamente
    // Para soportar FormData en PUT usamos php://input no disponible,
    // por eso el JSX usa POST con _method=PUT y el router lo trata como update()
    public function update(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }
        $id = (int)$id;

        $nombre      = trim($_POST['nombre']      ?? '');
        $precio      = $_POST['precio']           ?? '';
        $descripcion = trim($_POST['descripcion'] ?? '') ?: 'Producto de ferreinver disponible';

        // Validaciones
        if (empty($nombre) || $precio === '') {
            Response::error("El nombre y el precio son obligatorios.", 400); return;
        }
        if (strlen($nombre) > 30) {
            Response::error("El nombre no puede exceder 30 caracteres.", 400); return;
        }
        if (!is_numeric($precio) || $precio <= 0) {
            Response::error("El precio debe ser un número mayor a 0.", 400); return;
        }
        if (floor($precio) != $precio) {
            Response::error("El precio debe ser un número entero.", 400); return;
        }
        if (strlen($descripcion) > 100) {
            Response::error("La descripción no puede exceder 100 caracteres.", 400); return;
        }

        // Verificar que existe y obtener imagen actual
        $producto = $this->model->getById($id);
        if (!$producto) {
            Response::error("El producto no existe.", 404); return;
        }

        // Conservar imagen actual por defecto
        $imagenUrl = $producto['imagen'];

        // Solo reemplaza si el usuario subió una imagen nueva
        if (!empty($_FILES['imagen']['name'])) {
            $res = $this->subirImagen($_FILES['imagen']);
            if (!$res['ok']) { Response::error($res['msg'], 400); return; }

            // Borrar imagen vieja del disco
            if ($imagenUrl) {
                $oldPath = __DIR__ . '/../../' . $imagenUrl;
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $imagenUrl = $res['url'];
        }

        if ($this->model->update($id, $nombre, (int)$precio, $descripcion, $imagenUrl))
            Response::success("Producto actualizado exitosamente.");
        else
            Response::error("Error al actualizar el producto.", 500);
    }

    // ─── DELETE /productos?id=X  (soft delete → estado_producto = inactivo) ──
    public function deactivate(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }
        $id = (int)$id;

        $producto = $this->model->getById($id);
        if (!$producto) {
            Response::error("El producto no existe.", 404); return;
        }
        if ($producto['estado_producto'] === 'inactivo') {
            Response::error("El producto ya está desactivado.", 409); return;
        }

        if ($this->model->deactivate($id))
            Response::success("Producto desactivado exitosamente.");
        else
            Response::error("Error al desactivar el producto.", 500);
    }

    // ─── HELPER: subir imagen ────────────────────────────────────────────────
    private function subirImagen(array $file): array {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $maxSize      = 2 * 1024 * 1024; // 2 MB

        if (!in_array($file['type'], $allowedTypes))
            return ['ok' => false, 'msg' => 'Solo se permiten imágenes JPG, PNG, WEBP o GIF.'];
        if ($file['size'] > $maxSize)
            return ['ok' => false, 'msg' => 'La imagen no puede superar 2MB.'];

        if (!is_dir($this->uploadDir))
            mkdir($this->uploadDir, 0755, true);

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('prod_', true) . '.' . $ext;
        $destPath = $this->uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath))
            return ['ok' => false, 'msg' => 'No se pudo guardar la imagen.'];

        return ['ok' => true, 'url' => $this->uploadUrl . $filename];
    }
}