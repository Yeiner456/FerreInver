<?php

require_once __DIR__ . '/../config/Database.php';

class ComprasModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ── SELECTS ─────────────────────────────────────────────────────────────

    public function getProductosActivos() {
        $result = mysqli_query($this->conn,
            "SELECT id_producto, nombre FROM productos WHERE estado_producto = 'activo' ORDER BY nombre"
        );
        $rows = [];
        while ($f = mysqli_fetch_assoc($result)) $rows[] = $f;
        return $rows;
    }

    public function getProveedoresActivos() {
        $result = mysqli_query($this->conn,
            "SELECT nit_proveedor, correo FROM proveedores WHERE estado = 'activo' ORDER BY correo"
        );
        $rows = [];
        while ($f = mysqli_fetch_assoc($result)) $rows[] = $f;
        return $rows;
    }

    // ── COMPRAS ─────────────────────────────────────────────────────────────

    public function getAll() {
        $result = mysqli_query($this->conn, "
            SELECT c.id_compra, c.cantidad, c.descripcion,
                   p.id_producto, p.nombre AS nombre_producto,
                   pr.nit_proveedor, pr.correo AS correo_proveedor
            FROM compras c
            INNER JOIN productos p    ON c.id_producto  = p.id_producto
            INNER JOIN proveedores pr ON c.id_proveedor = pr.nit_proveedor
            ORDER BY c.id_compra DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($result)) $rows[] = $f;
        return $rows;
    }

    public function existe($id) {
        $stmt = mysqli_prepare($this->conn,
            "SELECT id_compra FROM compras WHERE id_compra = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function existeProducto($id_producto) {
        $stmt = mysqli_prepare($this->conn,
            "SELECT id_producto FROM productos WHERE id_producto = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_producto);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function existeProveedor($id_proveedor) {
        $stmt = mysqli_prepare($this->conn,
            "SELECT nit_proveedor FROM proveedores WHERE nit_proveedor = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_proveedor);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function create($cantidad, $descripcion, $id_proveedor, $id_producto) {
        $stmt = mysqli_prepare($this->conn,
            "INSERT INTO compras (cantidad, descripcion, id_proveedor, id_producto) VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'isii', $cantidad, $descripcion, $id_proveedor, $id_producto);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function update($id, $cantidad, $descripcion) {
        $stmt = mysqli_prepare($this->conn,
            "UPDATE compras SET cantidad=?, descripcion=? WHERE id_compra=?"
        );
        mysqli_stmt_bind_param($stmt, 'isi', $cantidad, $descripcion, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn,
            "DELETE FROM compras WHERE id_compra = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}