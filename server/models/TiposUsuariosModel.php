<?php

require_once __DIR__ . '/../config/Database.php';

class TiposUsuariosModel {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $result = mysqli_query($this->conn,
            "SELECT * FROM tipos_usuarios ORDER BY id_tipo_de_usuario DESC"
        );
        $rows = [];
        while ($f = mysqli_fetch_assoc($result)) $rows[] = $f;
        return $rows;
    }

    public function existe($id) {
        $stmt = mysqli_prepare($this->conn,
            "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE id_tipo_de_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function existeNombre($nombre, $excluir_id = null) {
        if ($excluir_id !== null) {
            $stmt = mysqli_prepare($this->conn,
                "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ? AND id_tipo_de_usuario != ?"
            );
            mysqli_stmt_bind_param($stmt, 'si', $nombre, $excluir_id);
        } else {
            $stmt = mysqli_prepare($this->conn,
                "SELECT id_tipo_de_usuario FROM tipos_usuarios WHERE nombre = ?"
            );
            mysqli_stmt_bind_param($stmt, 's', $nombre);
        }
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function tieneClientes($id) {
        $stmt = mysqli_prepare($this->conn,
            "SELECT documento FROM clientes WHERE id_tipo_de_usuario = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $count = mysqli_num_rows(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);
        return $count > 0;
    }

    public function create($nombre, $estado) {
        $stmt = mysqli_prepare($this->conn,
            "INSERT INTO tipos_usuarios (nombre, estado) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, 'ss', $nombre, $estado);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function update($id, $nombre, $estado) {
        $stmt = mysqli_prepare($this->conn,
            "UPDATE tipos_usuarios SET nombre = ?, estado = ? WHERE id_tipo_de_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'ssi', $nombre, $estado, $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }

    public function delete($id) {
        $stmt = mysqli_prepare($this->conn,
            "DELETE FROM tipos_usuarios WHERE id_tipo_de_usuario = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}