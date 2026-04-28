<?php

require_once __DIR__ . '/../config/Database.php';

class InvernaderosModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODOS ───────────────────────────────────────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "SELECT * FROM invernaderos ORDER BY id_invernadero DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── BUSCAR POR ID ──────────────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn, "SELECT * FROM invernaderos WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── BUSCAR POR NOMBRE (duplicados) ─────────────────────────────────────
    // $excludeId: si se pasa, excluye ese registro (usado en UPDATE)
    public function getByNombre(string $nombre, ?int $excludeId = null) {
        if ($excludeId !== null) {
            $st = mysqli_prepare($this->conn,
                "SELECT id_invernadero FROM invernaderos WHERE nombre = ? AND id_invernadero != ?");
            mysqli_stmt_bind_param($st, 'si', $nombre, $excludeId);
        } else {
            $st = mysqli_prepare($this->conn,
                "SELECT id_invernadero FROM invernaderos WHERE nombre = ?");
            mysqli_stmt_bind_param($st, 's', $nombre);
        }
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── CREAR ───────────────────────────────────────────────────────────────
    public function create(string $nombre, string $descripcion, float $precio_m2, string $estado): bool {
        $st = mysqli_prepare($this->conn,
            "INSERT INTO invernaderos (nombre, descripcion, precio_m2, estado) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'ssds', $nombre, $descripcion, $precio_m2, $estado);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function update(int $id, string $nombre, string $descripcion, float $precio_m2, string $estado): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE invernaderos SET nombre = ?, descripcion = ?, precio_m2 = ?, estado = ? WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'ssdsi', $nombre, $descripcion, $precio_m2, $estado, $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── DESACTIVAR (soft delete) ────────────────────────────────────────────
    public function deactivate(int $id): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE invernaderos SET estado = 'inactivo' WHERE id_invernadero = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}