<?php

require_once __DIR__ . '/../config/Database.php';

class ClientesModel
{

    private $conn;

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    // ── TIPOS DE USUARIO ────────────────────────────────────────────────────

    public function getTiposUsuario()
    {
        $result = mysqli_query(
            $this->conn,
            "SELECT id_tipo_de_usuario, nombre FROM tipos_usuarios ORDER BY nombre"
        );
        $tipos = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $tipos[] = $fila;
        }
        return $tipos;
    }

    public function existeTipoUsuario($id_tipo)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id_tipo);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    // ── CLIENTES ────────────────────────────────────────────────────────────

    public function getAll()
    {
        $result = mysqli_query($this->conn, "
            SELECT c.documento, c.id_tipo_de_usuario, c.nombre, c.correo,
                   c.fecha_registro, c.estado_inicio_sesion,
                   t.nombre AS tipo_usuario
            FROM clientes c
            LEFT JOIN tipos_usuarios t ON c.id_tipo_de_usuario = t.id_tipo_de_usuario
            ORDER BY c.fecha_registro DESC
        ");
        $clientes = [];
        while ($fila = mysqli_fetch_assoc($result)) {
            $clientes[] = $fila;
        }
        return $clientes;
    }

    public function existeDocumento($documento)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT documento FROM clientes WHERE documento = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $documento);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function existeCorreo($correo, $excluir_documento = null)
    {
        if ($excluir_documento !== null) {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT documento FROM clientes WHERE correo = ? AND documento != ?"
            );
            mysqli_stmt_bind_param($stmt, 'si', $correo, $excluir_documento);
        } else {
            $stmt = mysqli_prepare(
                $this->conn,
                "SELECT documento FROM clientes WHERE correo = ?"
            );
            mysqli_stmt_bind_param($stmt, 's', $correo);
        }
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function create($documento, $id_tipo, $password_hash, $nombre, $correo, $estado)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "INSERT INTO clientes (documento, id_tipo_de_usuario, password_hash, nombre, correo, estado_inicio_sesion)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            'iissss',
            $documento,
            $id_tipo,
            $password_hash,
            $nombre,
            $correo,
            $estado
        );
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function update($documento, $id_tipo, $nombre, $correo, $estado, $password_hash = null)
    {
        if ($password_hash !== null) {
            $stmt = mysqli_prepare(
                $this->conn,
                "UPDATE clientes SET id_tipo_de_usuario=?, password_hash=?, nombre=?, correo=?, estado_inicio_sesion=?
                 WHERE documento=?"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'issssi',
                $id_tipo,
                $password_hash,
                $nombre,
                $correo,
                $estado,
                $documento
            );
        } else {
            $stmt = mysqli_prepare(
                $this->conn,
                "UPDATE clientes SET id_tipo_de_usuario=?, nombre=?, correo=?, estado_inicio_sesion=?
                 WHERE documento=?"
            );
            mysqli_stmt_bind_param(
                $stmt,
                'isssi',
                $id_tipo,
                $nombre,
                $correo,
                $estado,
                $documento
            );
        }
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function getEstado($documento)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "SELECT estado_inicio_sesion FROM clientes WHERE documento = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $documento);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $fila = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        return $fila ? $fila['estado_inicio_sesion'] : null;
    }

    public function updateNombre($documento, $nombre)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE clientes SET nombre = ? WHERE documento = ?"
        );
        mysqli_stmt_bind_param($stmt, 'si', $nombre, $documento);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function deactivate($documento)
    {
        $stmt = mysqli_prepare(
            $this->conn,
            "UPDATE clientes SET estado_inicio_sesion = 'inactivo' WHERE documento = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $documento);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}