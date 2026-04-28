<?php

require_once __DIR__ . '/../models/StocksModel.php';
require_once __DIR__ . '/../core/Response.php';

class StocksController {

    private StocksModel $model;

    public function __construct() {
        $this->model = new StocksModel();
    }

    // ─── GET /stocks            → lista completa con JOIN ────────────────────
    // ─── GET /stocks?selects=1  → productos para el <select> del modal ───────
    public function index(): void {
        if (isset($_GET['selects'])) {
            $productos = $this->model->getProductosSelect();
            Response::success("OK", ['productos' => $productos]);
            return;
        }
        $data = $this->model->getAll();
        Response::success("OK", $data);
    }

    // ─── POST /stocks ─────────────────────────────────────────────────────────
    public function create(): void {
        $b = json_decode(file_get_contents("php://input"), true);

        $id_producto = $b['id_producto'] ?? '';
        $cantidad    = $b['cantidad']    ?? '';

        if ($id_producto === '' || $cantidad === '') {
            Response::error("Todos los campos son obligatorios.", 400); return;
        }
        if (!is_numeric($id_producto) || $id_producto <= 0) {
            Response::error("Producto inválido.", 400); return;
        }
        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad) {
            Response::error("La cantidad debe ser un entero mayor o igual a 0.", 400); return;
        }
        if (!$this->model->productoExiste((int)$id_producto)) {
            Response::error("El producto no existe.", 404); return;
        }
        if ($this->model->stockDuplicado((int)$id_producto)) {
            Response::error("Ya existe un registro de stock para este producto.", 409); return;
        }

        if ($this->model->create((int)$id_producto, (int)$cantidad))
            Response::success("Stock registrado exitosamente.", null, 201);
        else
            Response::error("Error al registrar el stock.", 500);
    }

    // ─── PUT /stocks?id=X ─────────────────────────────────────────────────────
    public function update(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;
        $b  = json_decode(file_get_contents("php://input"), true);

        $id_producto = $b['id_producto'] ?? '';
        $cantidad    = $b['cantidad']    ?? '';

        if ($id_producto === '' || $cantidad === '') {
            Response::error("Todos los campos son obligatorios.", 400); return;
        }
        if (!is_numeric($id_producto) || $id_producto <= 0) {
            Response::error("Producto inválido.", 400); return;
        }
        if (!is_numeric($cantidad) || $cantidad < 0 || floor($cantidad) != $cantidad) {
            Response::error("La cantidad debe ser un entero mayor o igual a 0.", 400); return;
        }
        if (!$this->model->getById($id)) {
            Response::error("El stock no existe.", 404); return;
        }
        if (!$this->model->productoExiste((int)$id_producto)) {
            Response::error("El producto no existe.", 404); return;
        }
        if ($this->model->stockDuplicado((int)$id_producto, $id)) {
            Response::error("Ya existe otro registro de stock para este producto.", 409); return;
        }

        if ($this->model->update($id, (int)$id_producto, (int)$cantidad))
            Response::success("Stock actualizado exitosamente.");
        else
            Response::error("Error al actualizar el stock.", 500);
    }

    // ─── DELETE /stocks?id=X  (eliminación física) ────────────────────────────
    public function delete(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;

        if (!$this->model->getById($id)) {
            Response::error("El stock no existe.", 404); return;
        }

        if ($this->model->delete($id))
            Response::success("Stock eliminado exitosamente.");
        else
            Response::error("Error al eliminar el stock.", 500);
    }
}