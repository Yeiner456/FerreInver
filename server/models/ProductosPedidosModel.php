<?php

require_once __DIR__ . '/../config/Database.php';

class ProductosPedidosModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODOS (con JOIN a productos y pedidos) ───────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "
            SELECT pp.id, pp.descripcion, pp.cantidad,
                   p.nombre  AS nombre_producto,
                   pe.id_pedido
            FROM productos_pedidos pp
            INNER JOIN productos p  ON pp.id_producto = p.id_producto
            INNER JOIN pedidos   pe ON pp.id_pedido   = pe.id_pedido
            ORDER BY pp.id DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── PRODUCTOS Y PEDIDOS PARA SELECTS ────────────────────────────────────
    public function getSelects(): array {
        $resProds = mysqli_query($this->conn,
            "SELECT id_producto, nombre FROM productos ORDER BY nombre ASC");
        $resPeds  = mysqli_query($this->conn,
            "SELECT id_pedido FROM pedidos ORDER BY id_pedido ASC");

        $productos = [];
        while ($r = mysqli_fetch_assoc($resProds)) $productos[] = $r;

        $pedidos = [];
        while ($r = mysqli_fetch_assoc($resPeds)) $pedidos[] = $r;

        return ['productos' => $productos, 'pedidos' => $pedidos];
    }

    // ─── BUSCAR REGISTRO POR ID ──────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn,
            "SELECT id FROM productos_pedidos WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── VERIFICAR QUE EL PRODUCTO EXISTE ────────────────────────────────────
    public function productoExiste(int $id_producto): bool {
        $st = mysqli_prepare($this->conn,
            "SELECT id_producto FROM productos WHERE id_producto = ?");
        mysqli_stmt_bind_param($st, 'i', $id_producto);
        mysqli_stmt_execute($st);
        $res    = mysqli_stmt_get_result($st);
        $existe = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $existe;
    }

    // ─── VERIFICAR QUE EL PEDIDO EXISTE ──────────────────────────────────────
    public function pedidoExiste(int $id_pedido): bool {
        $st = mysqli_prepare($this->conn,
            "SELECT id_pedido FROM pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id_pedido);
        mysqli_stmt_execute($st);
        $res    = mysqli_stmt_get_result($st);
        $existe = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $existe;
    }

    // ─── CREAR ───────────────────────────────────────────────────────────────
    public function create(int $id_producto, int $id_pedido, string $descripcion, int $cantidad): bool {
        $st = mysqli_prepare($this->conn,
            "INSERT INTO productos_pedidos (id_producto, id_pedido, descripcion, cantidad)
             VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iisi', $id_producto, $id_pedido, $descripcion, $cantidad);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ACTUALIZAR (solo descripcion y cantidad) ─────────────────────────────
    public function update(int $id, string $descripcion, int $cantidad): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE productos_pedidos SET descripcion = ?, cantidad = ? WHERE id = ?");
        mysqli_stmt_bind_param($st, 'sii', $descripcion, $cantidad, $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ELIMINAR (físico — tabla intermedia) ─────────────────────────────────
    public function delete(int $id): bool {
        $st = mysqli_prepare($this->conn,
            "DELETE FROM productos_pedidos WHERE id = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}