<?php

require_once __DIR__ . '/../config/Database.php';

class CotizacionesModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // ─── LISTAR TODAS ──────────────────────────────────────────────────────
    public function getAll(): array {
        $resultado = mysqli_query($this->conn, "
            SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
                   co.valor_m2, co.total, co.fecha, co.estado,
                   cl.nombre AS cliente_nombre,
                   inv.nombre AS invernadero_nombre,
                   co.cliente_id, co.invernadero_id
            FROM cotizaciones co
            INNER JOIN clientes cl ON co.cliente_id = cl.documento
            INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
            ORDER BY co.fecha DESC
        ");

        $rows = [];
        while ($f = mysqli_fetch_assoc($resultado)) $rows[] = $f;
        return $rows;
    }

    // ─── LISTAR POR CLIENTE ────────────────────────────────────────────────
    public function getByCliente(int $documento): array {
    $st = mysqli_prepare($this->conn, "
        SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
               co.valor_m2, co.total, co.fecha, co.estado,
               inv.nombre AS invernadero_nombre
        FROM cotizaciones co
        INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
        WHERE co.cliente_id = ?
        ORDER BY co.fecha DESC
    ");

    mysqli_stmt_bind_param($st, 'i', $documento);
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);

    $rows = [];
    while ($f = mysqli_fetch_assoc($res)) $rows[] = $f;

    mysqli_stmt_close($st);
    return $rows;
}

    // ─── SELECTS: clientes activos + invernaderos activos ─────────────────
    public function getSelects(): array {

        // Clientes
        $resClientes = mysqli_query($this->conn, "
            SELECT documento, nombre FROM clientes
            WHERE estado_inicio_sesion = 'activo'
            ORDER BY nombre
        ");

        $clientes = [];
        while ($f = mysqli_fetch_assoc($resClientes)) $clientes[] = $f;

        // Invernaderos
        $resInv = mysqli_query($this->conn, "
            SELECT id_invernadero, nombre, precio_m2 FROM invernaderos
            WHERE estado = 'activo'
            ORDER BY nombre
        ");

        $invernaderos = [];
        while ($f = mysqli_fetch_assoc($resInv)) $invernaderos[] = $f;

        return [
            'clientes' => $clientes,
            'invernaderos' => $invernaderos
        ];
    }

    // ─── VERIFICAR CLIENTE ─────────────────────────────────────────────────
    public function clienteExiste(int $cliente_id): bool {
        $st = mysqli_prepare($this->conn,
            "SELECT documento FROM clientes WHERE documento = ?"
        );
        mysqli_stmt_bind_param($st, 'i', $cliente_id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $existe = mysqli_num_rows($res) > 0;
        mysqli_stmt_close($st);
        return $existe;
    }

    // ─── VERIFICAR INVERNADERO Y OBTENER PRECIO ────────────────────────────
    public function getInvernadero(int $invernadero_id) {
        $st = mysqli_prepare($this->conn,
            "SELECT id_invernadero, precio_m2 FROM invernaderos WHERE id_invernadero = ?"
        );
        mysqli_stmt_bind_param($st, 'i', $invernadero_id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row ?: null;
    }

    // ─── VERIFICAR COTIZACIÓN ──────────────────────────────────────────────
    public function getById(int $id) {
        $st = mysqli_prepare($this->conn,
            "SELECT id_cotizacion, estado FROM cotizaciones WHERE id_cotizacion = ?"
        );
        mysqli_stmt_bind_param($st, 'i', $id);
        mysqli_stmt_execute($st);
        $res = mysqli_stmt_get_result($st);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($st);
        return $row ?: null;
    }

    // ─── CREAR ─────────────────────────────────────────────────────────────
    public function create(array $d): bool {
        $st = mysqli_prepare($this->conn, "
            INSERT INTO cotizaciones
            (cliente_id, invernadero_id, largo, ancho, metros_cuadrados, valor_m2, total, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param(
            $st,
            'iiddddss',
            $d['cliente_id'],
            $d['invernadero_id'],
            $d['largo'],
            $d['ancho'],
            $d['metros_cuadrados'],
            $d['valor_m2'],
            $d['total'],
            $d['estado']
        );

        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── ACTUALIZAR ────────────────────────────────────────────────────────
    public function update(int $id, array $d): bool {
        $st = mysqli_prepare($this->conn, "
            UPDATE cotizaciones
            SET cliente_id=?, invernadero_id=?, largo=?, ancho=?,
                metros_cuadrados=?, valor_m2=?, total=?, estado=?
            WHERE id_cotizacion=?
        ");

        mysqli_stmt_bind_param(
            $st,
            'iiddddsi',
            $d['cliente_id'],
            $d['invernadero_id'],
            $d['largo'],
            $d['ancho'],
            $d['metros_cuadrados'],
            $d['valor_m2'],
            $d['total'],
            $d['estado'],
            $id
        );

        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }

    // ─── RECHAZAR ──────────────────────────────────────────────────────────
    public function rechazar(int $id): bool {
        $st = mysqli_prepare($this->conn,
            "UPDATE cotizaciones SET estado = 'rechazada' WHERE id_cotizacion = ?"
        );
        mysqli_stmt_bind_param($st, 'i', $id);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        return $ok;
    }
}