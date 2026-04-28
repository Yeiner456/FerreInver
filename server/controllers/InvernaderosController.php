<?php

require_once __DIR__ . '/../models/InvernaderosModel.php';
require_once __DIR__ . '/../core/Response.php';

class InvernaderosController {

    private InvernaderosModel $model;

    public function __construct() {
        $this->model = new InvernaderosModel();
    }

    // ─── GET /invernaderos ───────────────────────────────────────────────────
    public function index(): void {
        $data = $this->model->getAll();
        Response::success("OK", $data);
    }

    // ─── POST /invernaderos ──────────────────────────────────────────────────
    public function create(): void {
        $b = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre']      ?? '');
        $descripcion = trim($b['descripcion'] ?? '');
        $precio_m2   = $b['precio_m2']        ?? '';
        $estado      = trim($b['estado']      ?? '');

        if (empty($nombre) || $precio_m2 === '' || empty($estado)) {
            Response::error("Nombre, precio m² y estado son obligatorios.", 400); return;
        }
        if (strlen($nombre) > 50) {
            Response::error("El nombre no puede exceder 50 caracteres.", 400); return;
        }
        if (strlen($descripcion) > 150) {
            Response::error("La descripción no puede exceder 150 caracteres.", 400); return;
        }
        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99) {
            Response::error("El precio m² debe ser un número positivo válido.", 400); return;
        }
        if (!in_array($estado, ['activo', 'inactivo'])) {
            Response::error("Estado inválido.", 400); return;
        }
        if ($this->model->getByNombre($nombre)) {
            Response::error("Ya existe un invernadero con ese nombre.", 409); return;
        }

        if ($this->model->create($nombre, $descripcion, (float)$precio_m2, $estado))
            Response::success("Invernadero registrado exitosamente.", null, 201);
        else
            Response::error("Error al registrar el invernadero.", 500);
    }

    // ─── PUT /invernaderos?id=X ──────────────────────────────────────────────
    public function update(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;
        $b  = json_decode(file_get_contents("php://input"), true);

        $nombre      = trim($b['nombre']      ?? '');
        $descripcion = trim($b['descripcion'] ?? '');
        $precio_m2   = $b['precio_m2']        ?? '';
        $estado      = trim($b['estado']      ?? '');

        if (empty($nombre) || $precio_m2 === '' || empty($estado)) {
            Response::error("Nombre, precio m² y estado son obligatorios.", 400); return;
        }
        if (strlen($nombre) > 50) {
            Response::error("El nombre no puede exceder 50 caracteres.", 400); return;
        }
        if (strlen($descripcion) > 150) {
            Response::error("La descripción no puede exceder 150 caracteres.", 400); return;
        }
        if (!is_numeric($precio_m2) || $precio_m2 <= 0 || $precio_m2 >= 9999999999.99) {
            Response::error("El precio m² debe ser un número positivo válido.", 400); return;
        }
        if (!in_array($estado, ['activo', 'inactivo'])) {
            Response::error("Estado inválido.", 400); return;
        }
        if (!$this->model->getById($id)) {
            Response::error("El invernadero no existe.", 404); return;
        }
        if ($this->model->getByNombre($nombre, $id)) {
            Response::error("Ya existe otro invernadero con ese nombre.", 409); return;
        }

        if ($this->model->update($id, $nombre, $descripcion, (float)$precio_m2, $estado))
            Response::success("Invernadero actualizado exitosamente.");
        else
            Response::error("Error al actualizar el invernadero.", 500);
    }

    // ─── DELETE /invernaderos?id=X ───────────────────────────────────────────
    public function deactivate(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id  = (int)$id;
        $inv = $this->model->getById($id);

        if (!$inv) {
            Response::error("El invernadero no existe.", 404); return;
        }
        if ($inv['estado'] === 'inactivo') {
            Response::error("El invernadero ya está desactivado.", 409); return;
        }

        if ($this->model->deactivate($id))
            Response::success("Invernadero desactivado exitosamente.");
        else
            Response::error("Error al desactivar el invernadero.", 500);
    }
}