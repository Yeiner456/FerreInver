<?php

require_once __DIR__ . '/../config/Database.php';

class ProductosModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODOS ───────────────────────────────────────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "SELECT * FROM productos ORDER BY id_producto DESC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── BUSCAR POR ID ──────────────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn, "SELECT * FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── CREAR ───────────────────────────────────────────────────────────────
    public function create(string $nombre, int $precio, string $descripcion, ?string $imagen): bool {
        $st = mysqli_prepare($this->conn,
            "INSERT INTO productos (nombre, precio, descripcion, imagen) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'siss', $nombre, $precio, $descripcion, $imagen);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function update(int $id, string $nombre, int $precio, string $descripcion, ?string $imagen): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE productos SET nombre = ?, precio = ?, descripcion = ?, imagen = ? WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'sissi', $nombre, $precio, $descripcion, $imagen, $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── DESACTIVAR (soft delete) ────────────────────────────────────────────
    public function deactivate(int $id): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE productos SET estado_producto = 'inactivo' WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}