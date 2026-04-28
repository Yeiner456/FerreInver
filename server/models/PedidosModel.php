<?php

require_once __DIR__ . '/../config/Database.php';

class PedidosModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODOS (con JOIN a clientes) ──────────────────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "
            SELECT p.id_pedido, p.fecha_hora, p.medio_pago, p.estado_pedido,
                   p.id_cliente, c.nombre AS nombre_cliente, c.correo
            FROM pedidos p
            INNER JOIN clientes c ON p.id_cliente = c.documento
            ORDER BY p.fecha_hora DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── PEDIDOS DE UN CLIENTE ESPECÍFICO ────────────────────────────────────
    public function getByCliente(int $documento): array {
        $resultado = mysqli_query($this->conn, "
            SELECT p.id_pedido, p.fecha_hora, p.medio_pago, p.estado_pedido,
                   GROUP_CONCAT(pp.descripcion, ' x', pp.cantidad SEPARATOR ' | ') AS productos
            FROM pedidos p
            LEFT JOIN productos_pedidos pp ON p.id_pedido = pp.id_pedido
            WHERE p.id_cliente = $documento
            GROUP BY p.id_pedido
            ORDER BY p.fecha_hora DESC
        ");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── CLIENTES ACTIVOS PARA SELECT ────────────────────────────────────────
    public function getClientesSelect(): array {
        $resultado = mysqli_query($this->conn,
            "SELECT documento, nombre, correo FROM clientes
             WHERE estado_inicio_sesion = 'activo' ORDER BY nombre ASC");
        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── BUSCAR PEDIDO POR ID ─────────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn, "SELECT * FROM pedidos WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row;
    }

    // ─── VERIFICAR QUE EL CLIENTE EXISTE ─────────────────────────────────────
    public function clienteExiste(int $documento): bool {
        $st = mysqli_prepare($this->conn, "SELECT documento FROM clientes WHERE documento = ?");
        mysqli_stmt_bind_param($st, 'i', $documento);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $existe = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $existe;
    }

    // ─── CREAR PEDIDO SIMPLE (CRUD admin) ────────────────────────────────────
    public function create(int $id_cliente, string $medio_pago, string $estado_pedido): bool {
        $st = mysqli_prepare($this->conn,
            "INSERT INTO pedidos (id_cliente, medio_pago, estado_pedido) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iss', $id_cliente, $medio_pago, $estado_pedido);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── CREAR PEDIDO COMPLETO con items en transacción ──────────────────────
    // Devuelve el id_pedido creado, o false si falla
    public function createCompleto(int $id_cliente, string $medio_pago, array $items) {
        mysqli_begin_transaction($this->conn);
        try {
            // 1. Insertar pedido
            $st = mysqli_prepare($this->conn,
                "INSERT INTO pedidos (id_cliente, medio_pago, estado_pedido) VALUES (?, ?, 'pendiente')");
            mysqli_stmt_bind_param($st, 'is', $id_cliente, $medio_pago);
            mysqli_stmt_execute($st);
            $id_pedido = mysqli_insert_id($this->conn);
            mysqli_stmt_close($st);

            // 2. Insertar cada item en productos_pedidos
            $st = mysqli_prepare($this->conn,
                "INSERT INTO productos_pedidos (id_producto, id_pedido, descripcion, cantidad) VALUES (?, ?, ?, ?)");

            foreach ($items as $item) {
                $id_producto = (int)($item['id_producto'] ?? 0);
                $cantidad    = (int)($item['cantidad']    ?? 1);
                $descripcion = substr(trim($item['nombre'] ?? 'Producto'), 0, 100);
                if ($id_producto <= 0 || $cantidad <= 0) continue;
                mysqli_stmt_bind_param($st, 'iisi', $id_producto, $id_pedido, $descripcion, $cantidad);
                mysqli_stmt_execute($st);
            }
            mysqli_stmt_close($st);

            mysqli_commit($this->conn);
            return $id_pedido;

        } catch (Exception $e) {
            mysqli_rollback($this->conn);
            return false;
        }
    }

    // ─── ACTUALIZAR ──────────────────────────────────────────────────────────
    public function update(int $id, int $id_cliente, string $medio_pago, string $estado_pedido): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE pedidos SET id_cliente = ?, medio_pago = ?, estado_pedido = ? WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'issi', $id_cliente, $medio_pago, $estado_pedido, $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── CANCELAR (soft → estado = 'cancelado') ───────────────────────────────
    public function cancel(int $id): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE pedidos SET estado_pedido = 'cancelado' WHERE id_pedido = ?");
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}