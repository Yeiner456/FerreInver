<?php
// models/ProveedoresModel.php

class ProveedoresModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // ─── LISTAR TODOS ──────────────────────────────────────────────────────
    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM proveedores ORDER BY nit_proveedor DESC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ─── VERIFICAR NIT DUPLICADO ───────────────────────────────────────────
    public function nitExiste(int $nit): bool
    {
        $stmt = $this->db->prepare("SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?");
        $stmt->bind_param("i", $nit);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // ─── VERIFICAR CORREO DUPLICADO ────────────────────────────────────────
    public function correoExiste(string $correo, ?int $excluirNit = null): bool
    {
        if ($excluirNit !== null) {
            $stmt = $this->db->prepare(
                "SELECT nit_proveedor FROM proveedores WHERE correo = ? AND nit_proveedor != ?"
            );
            $stmt->bind_param("si", $correo, $excluirNit);
        } else {
            $stmt = $this->db->prepare(
                "SELECT nit_proveedor FROM proveedores WHERE correo = ?"
            );
            $stmt->bind_param("s", $correo);
        }
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // ─── OBTENER POR NIT ───────────────────────────────────────────────────
    public function getByNit(int $nit): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT nit_proveedor, estado FROM proveedores WHERE nit_proveedor = ?"
        );
        $stmt->bind_param("i", $nit);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ─── CREAR ─────────────────────────────────────────────────────────────
    public function create(array $d): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO proveedores (nit_proveedor, correo, direccion, telefono, estado)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("issss",
            $d['nit'],
            $d['correo'],
            $d['direccion'],
            $d['telefono'],
            $d['estado']
        );
        return $stmt->execute();
    }

    // ─── ACTUALIZAR ────────────────────────────────────────────────────────
    public function update(int $nit, array $d): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE proveedores SET correo=?, direccion=?, telefono=?, estado=?
             WHERE nit_proveedor=?"
        );
        $stmt->bind_param("ssssi",
            $d['correo'],
            $d['direccion'],
            $d['telefono'],
            $d['estado'],
            $nit
        );
        return $stmt->execute();
    }

    // ─── DESACTIVAR (soft-delete) ──────────────────────────────────────────
    public function deactivate(int $nit): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE proveedores SET estado = 'inactivo' WHERE nit_proveedor = ?"
        );
        $stmt->bind_param("i", $nit);
        return $stmt->execute();
    }
}