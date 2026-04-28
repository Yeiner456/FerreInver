<?php

require_once __DIR__ . '/../models/ProductosPedidosModel.php';
require_once __DIR__ . '/../core/Response.php';

class ProductosPedidosController {

    private ProductosPedidosModel $model;

    public function __construct() {
        $this->model = new ProductosPedidosModel();
    }

    // ─── GET /productos-pedidos            → lista completa ──────────────────
    // ─── GET /productos-pedidos?selects=1  → productos y pedidos para selects ─
    public function index(): void {
        if (isset($_GET['selects'])) {
            $data = $this->model->getSelects();
            Response::success("OK", $data);
            return;
        }
        $data = $this->model->getAll();
        Response::success("OK", $data);
    }

    // ─── POST /productos-pedidos ──────────────────────────────────────────────
    public function create(): void {
        $b = json_decode(file_get_contents("php://input"), true);

        $id_producto = $b['id_producto'] ?? '';
        $id_pedido   = $b['id_pedido']   ?? '';
        $descripcion = trim($b['descripcion'] ?? '');
        $cantidad    = $b['cantidad']    ?? '';

        if (empty($id_producto) || empty($id_pedido) || empty($descripcion) || $cantidad === '') {
            Response::error("Todos los campos son obligatorios.", 400); return;
        }
        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
            Response::error("La descripción contiene caracteres no permitidos.", 400); return;
        }
        if (strlen($descripcion) > 100) {
            Response::error("La descripción no puede exceder 100 caracteres.", 400); return;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000) {
            Response::error("La cantidad debe ser entre 1 y 1000.", 400); return;
        }
        if (!$this->model->productoExiste((int)$id_producto)) {
            Response::error("El producto no existe.", 404); return;
        }
        if (!$this->model->pedidoExiste((int)$id_pedido)) {
            Response::error("El pedido no existe.", 404); return;
        }

        if ($this->model->create((int)$id_producto, (int)$id_pedido, $descripcion, (int)$cantidad))
            Response::success("Producto-Pedido registrado exitosamente.", null, 201);
        else
            Response::error("Error al registrar el producto en el pedido.", 500);
    }

    // ─── PUT /productos-pedidos?id=X  (solo descripcion y cantidad) ───────────
    public function update(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;
        $b  = json_decode(file_get_contents("php://input"), true);

        $descripcion = trim($b['descripcion'] ?? '');
        $cantidad    = $b['cantidad']    ?? '';

        if (empty($descripcion) || $cantidad === '') {
            Response::error("Descripción y cantidad son obligatorios.", 400); return;
        }
        if (!preg_match("/^[A-Za-z0-9\s,.\-]+$/", $descripcion)) {
            Response::error("La descripción contiene caracteres no permitidos.", 400); return;
        }
        if (strlen($descripcion) > 100) {
            Response::error("La descripción no puede exceder 100 caracteres.", 400); return;
        }
        if (!is_numeric($cantidad) || $cantidad <= 0 || $cantidad > 1000) {
            Response::error("La cantidad debe ser entre 1 y 1000.", 400); return;
        }
        if (!$this->model->getById($id)) {
            Response::error("El registro no existe.", 404); return;
        }

        if ($this->model->update($id, $descripcion, (int)$cantidad))
            Response::success("Registro actualizado exitosamente.");
        else
            Response::error("Error al actualizar el registro.", 500);
    }

    // ─── DELETE /productos-pedidos?id=X  (eliminación física) ─────────────────
    public function delete(?string $id): void {
        if (!$id || !is_numeric($id)) {
            Response::error("ID inválido.", 400); return;
        }

        $id = (int)$id;

        if (!$this->model->getById($id)) {
            Response::error("El registro no existe.", 404); return;
        }

        if ($this->model->delete($id))
            Response::success("Producto eliminado del pedido exitosamente.");
        else
            Response::error("Error al eliminar el registro.", 500);
    }
}