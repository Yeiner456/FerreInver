<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../models/ClientesModel.php';

class ClientesController {

    private $model;

    public function __construct() {
        $this->model = new ClientesModel();
    }

    // GET /clientes  → lista todos
    // GET /clientes/tipos  → lista tipos de usuario
    public function index($subrecurso = null) {
        if ($subrecurso === 'tipos') {
            $tipos = $this->model->getTiposUsuario();
            Response::json(["success" => true, "data" => $tipos]);
        }
        $clientes = $this->model->getAll();
        Response::json(["success" => true, "data" => $clientes]);
    }

    // POST /clientes
    public function create() {
        $body = json_decode(file_get_contents("php://input"), true);

        $required = ['documento','id_tipo_de_usuario','nombre','correo','password','confirmar_password','estado'];
        foreach ($required as $field) {
            if (empty($body[$field])) {
                Response::error("El campo '$field' es obligatorio.");
            }
        }

        $documento          = trim($body['documento']);
        $id_tipo            = (int) trim($body['id_tipo_de_usuario']);
        $nombre             = trim($body['nombre']);
        $correo             = trim($body['correo']);
        $password           = trim($body['password']);
        $confirmar_password = trim($body['confirmar_password']);
        $estado             = trim($body['estado']);

        // Validaciones
        if (!is_numeric($documento) || $documento <= 0 || strlen($documento) > 11)
            Response::error("Documento inválido.");

        if ($id_tipo <= 0)
            Response::error("Tipo de usuario inválido.");

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            Response::error("Nombre inválido (solo letras, máx 30 caracteres).");

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50)
            Response::error("Correo inválido.");

        if ($password !== $confirmar_password)
            Response::error("Las contraseñas no coinciden.");

        if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password))
            Response::error("Contraseña inválida (mín 6 caracteres, letras y números).");

        if (!in_array($estado, ['activo', 'inactivo']))
            Response::error("Estado inválido.");

        // Verificar existencias en BD
        if (!$this->model->existeTipoUsuario($id_tipo))
            Response::error("El tipo de usuario no existe.");

        if ($this->model->existeDocumento($documento))
            Response::error("El documento ya está registrado.");

        if ($this->model->existeCorreo($correo))
            Response::error("El correo ya está registrado.");

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if ($this->model->create($documento, $id_tipo, $password_hash, $nombre, $correo, $estado)) {
            Response::success("Cliente registrado exitosamente.");
        } else {
            Response::error("Error al registrar el cliente.", 500);
        }
    }

    // PUT /clientes?documento=X
    public function update($documento) {
        if (!$documento || !is_numeric($documento))
            Response::error("Documento inválido.");

        $body = json_decode(file_get_contents("php://input"), true);

        $id_tipo            = (int) trim($body['id_tipo_de_usuario'] ?? '');
        $nombre             = trim($body['nombre'] ?? '');
        $correo             = trim($body['correo'] ?? '');
        $estado             = trim($body['estado'] ?? '');
        $password           = trim($body['password'] ?? '');
        $confirmar_password = trim($body['confirmar_password'] ?? '');

        if (!$id_tipo || !$nombre || !$correo || !$estado)
            Response::error("Todos los campos obligatorios deben estar llenos.");

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            Response::error("Nombre inválido.");

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 50)
            Response::error("Correo inválido.");

        if (!in_array($estado, ['activo', 'inactivo']))
            Response::error("Estado inválido.");

        if ($this->model->existeCorreo($correo, $documento))
            Response::error("El correo ya está registrado en otro cliente.");

        // Contraseña opcional
        $password_hash = null;
        if (!empty($password) || !empty($confirmar_password)) {
            if ($password !== $confirmar_password)
                Response::error("Las contraseñas no coinciden.");
            if (strlen($password) < 6 || !preg_match("/[a-zA-Z]/", $password) || !preg_match("/[0-9]/", $password))
                Response::error("Contraseña inválida.");
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($this->model->update($documento, $id_tipo, $nombre, $correo, $estado, $password_hash)) {
            Response::success("Cliente actualizado exitosamente.");
        } else {
            Response::error("Error al actualizar el cliente.", 500);
        }
    }

    // PATCH /clientes/nombre?documento=X  → actualiza solo el nombre (usado en MiPerfil)
    public function updateNombre($documento) {
        if (!$documento || !is_numeric($documento))
            Response::error("Documento inválido.");

        $body   = json_decode(file_get_contents("php://input"), true);
        $nombre = trim($body['nombre'] ?? '');

        if (!$nombre)
            Response::error("El nombre no puede estar vacío.");
        if (strlen($nombre) < 2)
            Response::error("El nombre debe tener al menos 2 caracteres.");
        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/", $nombre) || strlen($nombre) > 30)
            Response::error("Nombre inválido (solo letras, máx 30 caracteres).");

        if (!$this->model->existeDocumento($documento))
            Response::error("Cliente no encontrado.", 404);

        if ($this->model->updateNombre($documento, $nombre)) {
            Response::success("Nombre actualizado correctamente.", ["nombre" => $nombre]);
        } else {
            Response::error("Error al actualizar el nombre.", 500);
        }
    }

    // DELETE /clientes?documento=X  → soft delete (desactivar)
    public function deactivate($documento) {
        if (!$documento || !is_numeric($documento))
            Response::error("Documento inválido.");

        $estado = $this->model->getEstado($documento);

        if ($estado === null)
            Response::error("El cliente no existe.", 404);

        if ($estado === 'inactivo')
            Response::error("El cliente ya está desactivado.");

        if ($this->model->deactivate($documento)) {
            Response::success("Cliente desactivado exitosamente.");
        } else {
            Response::error("Error al desactivar el cliente.", 500);
        }
    }
}