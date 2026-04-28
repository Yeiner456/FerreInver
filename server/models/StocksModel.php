<?php

require_once __DIR__ . '/../config/Database.php';

class StocksModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODOS (con JOIN a productos) ─────────────────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "
            SELECT s.id_stock, s.cantidad, s.id_producto,
                   p.nombre AS nombre_producto, p.precio
            FROM stocks s
            INNER JOIN productos p ON s.id_producto = p.id_producto
            ORDER BY s.id_stock DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── LISTAR PRODUCTOS PARA SELECT ────────────────────────────────────────
    public function getProductosSelect(): array {
        $resultado = mysqli_query($this->conn,
            "SELECT id_producto, nombre FROM productos ORDER BY nombre ASC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── BUSCAR STOCK POR ID ─────────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn, "SELECT * FROM stocks WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── VERIFICAR QUE EL PRODUCTO EXISTE ────────────────────────────────────
    public function productoExiste(int $id_producto): bool {
        $st = mysqli_prepare($this->conn, "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $existe = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $existe;
    }

    // ─── VERIFICAR STOCK DUPLICADO POR PRODUCTO ───────────────────────────────
    // $excludeId: excluye ese stock (para UPDATE)
    public function stockDuplicado(int $id_producto, ?int $excludeId = null): bool {
        if ($excludeId !== null) {
            $st = mysqli_prepare($this->conn,
                "SELECT id_stock FROM stocks WHERE id_producto = ? AND id_stock != ?");
            mysqli_stmt_bind_param($st, 'ii', $id_producto, $excludeId);
        } else {
            $st = mysqli_prepare($this->conn,
                "SELECT id_stock FROM stocks WHERE id_producto = ?");
            mysqli_stmt_bind_param($st, 'i', $id_producto);
        }
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $duplicado = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $duplicado;
    }

    // ─── CREAR ───────────────────────────────────────────────────────────────
    public function create(int $id_producto, int $cantidad): bool {
        $st = mysqli_prepare($this->conn,
            "INSERT INTO stocks (id_producto, cantidad) VALUES (?, ?)");
        mysqli_stmt_bind_param($st, 'ii', $id_producto, $cantidad);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function update(int $id, int $id_producto, int $cantidad): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE stocks SET id_producto = ?, cantidad = ? WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'iii', $id_producto, $cantidad, $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ELIMINAR (físico) ───────────────────────────────────────────────────
    public function delete(int $id): bool {
        $st = mysqli_prepare($this->conn, "DELETE FROM stocks WHERE id_stock = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}