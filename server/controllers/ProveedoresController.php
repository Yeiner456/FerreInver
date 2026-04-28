<?php
// controllers/ProveedoresController.php

require_once __DIR__ . '/../models/ProveedoresModel.php';

class ProveedoresController
{
    private ProveedoresModel $model;

    public function __construct($db)
    {
        $this->model = new ProveedoresModel($db);
    }

    // ─── GET /proveedores ──────────────────────────────────────────────────
    public function index(): void
    {
        $data = $this->model->getAll();
        Response::json(["success" => true, "data" => $data]);
    }

    // ─── POST /proveedores ─────────────────────────────────────────────────
    public function create(): void
    {
        $b = json_decode(file_get_contents("php://input"), true) ?? [];

        $nit       = trim($b['nit']       ?? '');
        $correo    = trim($b['correo']    ?? '');
        $direccion = trim($b['direccion'] ?? '');
        $telefono  = trim($b['telefono']  ?? '');
        $estado    = trim($b['estado']    ?? '');

        // ── Validaciones ───────────────────────────────────────────────────
        if (empty($nit) || empty($correo) || empty($direccion) || empty($telefono) || empty($estado)) {
            Response::json(["success" => false, "message" => "Todos los campos son obligatorios."]);
            return;
        }
        if (!is_numeric($nit) || $nit <= 0 || strlen($nit) > 11) {
            Response::json(["success" => false, "message" => "El NIT debe ser un número válido de máximo 11 dígitos."]);
            return;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80) {
            Response::json(["success" => false, "message" => "El correo no es válido o excede 80 caracteres."]);
            return;
        }
        if (strlen($direccion) > 80) {
            Response::json(["success" => false, "message" => "La dirección debe tener entre 1 y 80 caracteres."]);
            return;
        }
        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20) {
            Response::json(["success" => false, "message" => "Teléfono inválido. Solo números, espacios, guiones, paréntesis y +"]);
            return;
        }
        if (strlen(preg_replace("/[^0-9]/", "", $telefono)) < 7) {
            Response::json(["success" => false, "message" => "El teléfono debe tener al menos 7 dígitos."]);
            return;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            Response::json(["success" => false, "message" => "Estado inválido."]);
            return;
        }

        // ── Duplicados ─────────────────────────────────────────────────────
        if ($this->model->nitExiste((int)$nit)) {
            Response::json(["success" => false, "message" => "El NIT ya está registrado."]);
            return;
        }
        if ($this->model->correoExiste($correo)) {
            Response::json(["success" => false, "message" => "El correo ya está registrado."]);
            return;
        }

        // ── Insertar ───────────────────────────────────────────────────────
        if ($this->model->create(['nit' => (int)$nit, 'correo' => $correo, 'direccion' => $direccion, 'telefono' => $telefono, 'estado' => $estado])) {
            Response::json(["success" => true, "message" => "Proveedor registrado exitosamente."]);
        } else {
            Response::json(["success" => false, "message" => "Error al registrar el proveedor."], 500);
        }
    }

    // ─── PUT /proveedores?nit=X ────────────────────────────────────────────
    public function update(?string $nit): void
    {
        if (!$nit) $nit = $_GET['nit'] ?? null;

        if (!$nit || !is_numeric($nit)) {
            Response::json(["success" => false, "message" => "NIT inválido."], 400);
            return;
        }
        $nit = (int)$nit;

        $b = json_decode(file_get_contents("php://input"), true) ?? [];

        $correo    = trim($b['correo']    ?? '');
        $direccion = trim($b['direccion'] ?? '');
        $telefono  = trim($b['telefono']  ?? '');
        $estado    = trim($b['estado']    ?? '');

        // ── Validaciones ───────────────────────────────────────────────────
        if (empty($correo) || empty($direccion) || empty($telefono) || empty($estado)) {
            Response::json(["success" => false, "message" => "Todos los campos son obligatorios."]);
            return;
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || strlen($correo) > 80) {
            Response::json(["success" => false, "message" => "El correo no es válido o excede 80 caracteres."]);
            return;
        }
        if (strlen($direccion) > 80) {
            Response::json(["success" => false, "message" => "La dirección debe tener entre 1 y 80 caracteres."]);
            return;
        }
        if (!preg_match("/^[0-9\s\-\(\)\+]+$/", $telefono) || strlen($telefono) > 20) {
            Response::json(["success" => false, "message" => "Teléfono inválido."]);
            return;
        }
        if (strlen(preg_replace("/[^0-9]/", "", $telefono)) < 7) {
            Response::json(["success" => false, "message" => "El teléfono debe tener al menos 7 dígitos."]);
            return;
        }
        if ($estado !== 'activo' && $estado !== 'inactivo') {
            Response::json(["success" => false, "message" => "Estado inválido."]);
            return;
        }

        // ── Existe ─────────────────────────────────────────────────────────
        if (!$this->model->getByNit($nit)) {
            Response::json(["success" => false, "message" => "El proveedor no existe."], 404);
            return;
        }

        // ── Correo duplicado en otro proveedor ─────────────────────────────
        if ($this->model->correoExiste($correo, $nit)) {
            Response::json(["success" => false, "message" => "El correo ya está registrado en otro proveedor."]);
            return;
        }

        // ── Actualizar ─────────────────────────────────────────────────────
        if ($this->model->update($nit, ['correo' => $correo, 'direccion' => $direccion, 'telefono' => $telefono, 'estado' => $estado])) {
            Response::json(["success" => true, "message" => "Proveedor actualizado exitosamente."]);
        } else {
            Response::json(["success" => false, "message" => "Error al actualizar el proveedor."], 500);
        }
    }

    // ─── DELETE /proveedores?nit=X ─────────────────────────────────────────
    public function deactivate(?string $nit): void
    {
        if (!$nit) $nit = $_GET['nit'] ?? null;

        if (!$nit || !is_numeric($nit)) {
            Response::json(["success" => false, "message" => "NIT inválido."], 400);
            return;
        }
        $nit = (int)$nit;

        $proveedor = $this->model->getByNit($nit);
        if (!$proveedor) {
            Response::json(["success" => false, "message" => "El proveedor no existe."], 404);
            return;
        }
        if ($proveedor['estado'] === 'inactivo') {
            Response::json(["success" => false, "message" => "El proveedor ya está desactivado."]);
            return;
        }

        if ($this->model->deactivate($nit)) {
            Response::json(["success" => true, "message" => "Proveedor desactivado exitosamente."]);
        } else {
            Response::json(["success" => false, "message" => "Error al desactivar el proveedor."], 500);
        }
    }
}