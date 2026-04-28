<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../models/ComprasModel.php';

class ComprasController {

    private $model;

    public function __construct() {
        $this->model = new ComprasModel();
    }

    // GET /compras          → lista todas las compras
    // GET /compras/selects  → productos y proveedores activos para los selects
    public function index($subrecurso = null) {
        if ($subrecurso === 'selects') {
            Response::json([
                "success"     => true,
                "productos"   => $this->model->getProductosActivos(),
                "proveedores" => $this->model->getProveedoresActivos(),
            ]);
        }
        $compras = $this->model->getAll();
        Response::json(["success" => true, "data" => $compras]);
    }

    // POST /compras
    public function create() {
        $b = json_decode(file_get_contents("php://input"), true);

        $cantidad     = $b['cantidad']     ?? '';
        $descripcion  = trim($b['descripcion']  ?? '');
        $id_producto  = $b['id_producto']  ?? '';
        $id_proveedor = $b['id_proveedor'] ?? '';

        if (empty($cantidad) || empty($descripcion) || empty($id_producto) || empty($id_proveedor))
            Response::error("Todos los campos son obligatorios.");

        if (!is_numeric($cantidad) || $cantidad <= 0)
            Response::error("La cantidad debe ser un número mayor a 0.");

        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion))
            Response::error("La descripción solo puede contener letras, números y espacios.");

        if (strlen($descripcion) > 150)
            Response::error("La descripción no puede exceder 150 caracteres.");

        if (!$this->model->existeProducto($id_producto))
            Response::error("El producto seleccionado no existe.");

        if (!$this->model->existeProveedor($id_proveedor))
            Response::error("El proveedor seleccionado no existe.");

        if ($this->model->create($cantidad, $descripcion, $id_proveedor, $id_producto)) {
            Response::success("Compra registrada exitosamente.");
        } else {
            Response::error("Error al registrar la compra.", 500);
        }
    }

    // PUT /compras?id=X
    public function update($id) {
        if (!$id || !is_numeric($id))
            Response::error("ID inválido.");

        $b = json_decode(file_get_contents("php://input"), true);

        $cantidad    = $b['cantidad']    ?? '';
        $descripcion = trim($b['descripcion'] ?? '');

        if (empty($cantidad) || empty($descripcion))
            Response::error("Cantidad y descripción son obligatorios.");

        if (!is_numeric($cantidad) || $cantidad <= 0)
            Response::error("La cantidad debe ser un número mayor a 0.");

        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $descripcion))
            Response::error("La descripción solo puede contener letras, números y espacios.");

        if (strlen($descripcion) > 150)
            Response::error("La descripción no puede exceder 150 caracteres.");

        if (!$this->model->existe($id))
            Response::error("La compra no existe.", 404);

        if ($this->model->update($id, $cantidad, $descripcion)) {
            Response::success("Compra actualizada exitosamente.");
        } else {
            Response::error("Error al actualizar la compra.", 500);
        }
    }

    // DELETE /compras?id=X
    public function delete($id) {
        if (!$id || !is_numeric($id))
            Response::error("ID inválido.");

        if (!$this->model->existe($id))
            Response::error("La compra no existe.", 404);

        if ($this->model->delete($id)) {
            Response::success("Compra eliminada exitosamente.");
        } else {
            Response::error("Error al eliminar la compra.", 500);
        }
    }
}