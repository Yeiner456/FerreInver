<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../models/TiposUsuariosModel.php';

class TiposUsuariosController {

    private $model;

    public function __construct() {
        $this->model = new TiposUsuariosModel();
    }

    // GET /tipos-usuarios
    public function index() {
        Response::json(["success" => true, "data" => $this->model->getAll()]);
    }

    // POST /tipos-usuarios
    public function create() {
        $body   = json_decode(file_get_contents("php://input"), true);
        $nombre = trim($body['nombre'] ?? '');
        $estado = trim($body['estado'] ?? '');

        if (!$nombre || !$estado)
            Response::error("Todos los campos son obligatorios.");

        if (strlen($nombre) > 30)
            Response::error("El nombre no puede exceder 30 caracteres.");

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre))
            Response::error("El nombre solo puede contener letras y espacios.");

        if (!in_array($estado, ['activo', 'inactivo']))
            Response::error("El estado no es válido.");

        if ($this->model->existeNombre($nombre))
            Response::error("Ya existe un tipo de usuario con ese nombre.");

        if ($this->model->create($nombre, $estado)) {
            Response::success("Tipo de usuario registrado exitosamente.");
        } else {
            Response::error("Error al registrar el tipo de usuario.", 500);
        }
    }

    // PUT /tipos-usuarios?id=X
    public function update($id) {
        if (!$id || !is_numeric($id))
            Response::error("ID inválido.");

        $body   = json_decode(file_get_contents("php://input"), true);
        $nombre = trim($body['nombre'] ?? '');
        $estado = trim($body['estado'] ?? '');

        if (!$nombre || !$estado)
            Response::error("Todos los campos son obligatorios.");

        if (strlen($nombre) > 30)
            Response::error("El nombre no puede exceder 30 caracteres.");

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre))
            Response::error("El nombre solo puede contener letras y espacios.");

        if (!in_array($estado, ['activo', 'inactivo']))
            Response::error("El estado no es válido.");

        if (!$this->model->existe($id))
            Response::error("El tipo de usuario no existe.", 404);

        if ($this->model->existeNombre($nombre, $id))
            Response::error("Ya existe otro tipo de usuario con ese nombre.");

        if ($this->model->update($id, $nombre, $estado)) {
            Response::success("Tipo de usuario actualizado exitosamente.");
        } else {
            Response::error("Error al actualizar el tipo de usuario.", 500);
        }
    }

    // DELETE /tipos-usuarios?id=X
    public function delete($id) {
        if (!$id || !is_numeric($id))
            Response::error("ID inválido.");

        if (!$this->model->existe($id))
            Response::error("El tipo de usuario no existe.", 404);

        if ($this->model->tieneClientes($id))
            Response::error("No se puede eliminar: hay clientes asociados a este tipo de usuario.");

        if ($this->model->delete($id)) {
            Response::success("Tipo de usuario eliminado exitosamente.");
        } else {
            Response::error("Error al eliminar el tipo de usuario.", 500);
        }
    }
}