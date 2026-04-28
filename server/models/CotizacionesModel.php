<?php
// models/CotizacionesModel.php

class CotizacionesModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // ─── LISTAR TODAS ──────────────────────────────────────────────────────
    public function getAll()
    {
        $stmt = $this->db->prepare("
            SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
                   co.valor_m2, co.total, co.fecha, co.estado,
                   cl.nombre  AS cliente_nombre,
                   inv.nombre AS invernadero_nombre,
                   co.cliente_id, co.invernadero_id
            FROM cotizaciones co
            INNER JOIN clientes     cl  ON co.cliente_id    = cl.documento
            INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
            ORDER BY co.fecha DESC
        ");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ─── LISTAR POR CLIENTE ────────────────────────────────────────────────
    public function getByCliente(int $documento)
    {
        $stmt = $this->db->prepare("
            SELECT co.id_cotizacion, co.largo, co.ancho, co.metros_cuadrados,
                   co.valor_m2, co.total, co.fecha, co.estado,
                   inv.nombre AS invernadero_nombre
            FROM cotizaciones co
            INNER JOIN invernaderos inv ON co.invernadero_id = inv.id_invernadero
            WHERE co.cliente_id = ?
            ORDER BY co.fecha DESC
        ");
        $stmt->bind_param("i", $documento);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // ─── SELECTS: clientes activos + invernaderos activos ─────────────────
    public function getSelects()
    {
        $stClientes = $this->db->prepare(
            "SELECT documento, nombre FROM clientes
             WHERE estado_inicio_sesion = 'activo'
             ORDER BY nombre"
        );
        $stClientes->execute();
        $clientes = $stClientes->get_result()->fetch_all(MYSQLI_ASSOC);

        $stInv = $this->db->prepare(
            "SELECT id_invernadero, nombre, precio_m2 FROM invernaderos
             WHERE estado = 'activo'
             ORDER BY nombre"
        );
        $stInv->execute();
        $invernaderos = $stInv->get_result()->fetch_all(MYSQLI_ASSOC);

        return ['clientes' => $clientes, 'invernaderos' => $invernaderos];
    }

    // ─── VERIFICAR CLIENTE ─────────────────────────────────────────────────
    public function clienteExiste(int $cliente_id): bool
    {
        $stmt = $this->db->prepare(
            "SELECT documento FROM clientes WHERE documento = ?"
        );
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    // ─── VERIFICAR INVERNADERO Y OBTENER PRECIO ────────────────────────────
    public function getInvernadero(int $invernadero_id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id_invernadero, precio_m2 FROM invernaderos WHERE id_invernadero = ?"
        );
        $stmt->bind_param("i", $invernadero_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ─── VERIFICAR COTIZACIÓN ──────────────────────────────────────────────
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id_cotizacion, estado FROM cotizaciones WHERE id_cotizacion = ?"
        );
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    }

    // ─── CREAR ─────────────────────────────────────────────────────────────
    public function create(array $d): bool
    {
        $stmt = $this->db->prepare("
            INSERT INTO cotizaciones
                (cliente_id, invernadero_id, largo, ancho, metros_cuadrados, valor_m2, total, estado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iidddds",
            $d['cliente_id'],
            $d['invernadero_id'],
            $d['largo'],
            $d['ancho'],
            $d['metros_cuadrados'],
            $d['valor_m2'],
            $d['total'],
            $d['estado']
        );
        return $stmt->execute();
    }

    // ─── ACTUALIZAR ────────────────────────────────────────────────────────
    public function update(int $id, array $d): bool
    {
        $stmt = $this->db->prepare("
            UPDATE cotizaciones
            SET cliente_id=?, invernadero_id=?, largo=?, ancho=?,
                metros_cuadrados=?, valor_m2=?, total=?, estado=?
            WHERE id_cotizacion=?
        ");
        $stmt->bind_param(
            "iiddddsi",
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
        return $stmt->execute();
    }

    // ─── RECHAZAR ──────────────────────────────────────────────────────────
    public function rechazar(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE cotizaciones SET estado = 'rechazada' WHERE id_cotizacion = ?"
        );
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}