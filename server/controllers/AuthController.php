<?php

require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../config/Database.php';

class AuthController {

    private $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // POST /auth/login
    public function login(): void {
        $body = json_decode(file_get_contents("php://input"), true);

        $documento = trim($body['documento'] ?? '');
        $password  = trim($body['password']  ?? '');

        if (empty($documento) || empty($password))
            Response::error("Documento y contraseña son obligatorios.");

        if (!is_numeric($documento) || $documento <= 0)
            Response::error("Documento inválido.");

        $st = mysqli_prepare($this->conn,
            "SELECT documento, nombre, correo, password_hash, estado_inicio_sesion, id_tipo_de_usuario
             FROM clientes WHERE documento = ?"
        );
        mysqli_stmt_bind_param($st, 'i', $documento);
        mysqli_stmt_execute($st);
        $cliente = mysqli_fetch_assoc(mysqli_stmt_get_result($st));
        mysqli_stmt_close($st);

        if (!$cliente)
            Response::error("Documento o contraseña incorrectos.", 401);

        if ($cliente['estado_inicio_sesion'] === 'inactivo')
            Response::error("Tu cuenta está inactiva. Contacta con Ferreinver.", 403);

        if (!password_verify($password, $cliente['password_hash']))
            Response::error("Documento o contraseña incorrectos.", 401);

        Response::json([
            "success" => true,
            "message" => "Sesión iniciada correctamente.",
            "cliente" => [
                "documento"          => $cliente['documento'],
                "nombre"             => $cliente['nombre'],
                "correo"             => $cliente['correo'],
                "id_tipo_de_usuario" => $cliente['id_tipo_de_usuario'],
                "estado_inicio_sesion" => $cliente['estado_inicio_sesion'],
            ]
        ]);
    }
}