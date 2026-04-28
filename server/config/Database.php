<?php

class Database {
    private static $conn = null;

    private static $host   = 'localhost';
    private static $user   = 'root';
    private static $pass   = '';
    private static $db     = 'ferreinver';
    private static $port   = '3306';

    public static function getConnection() {
        if (self::$conn === null) {
            self::$conn = mysqli_connect(
                self::$host,
                self::$user,
                self::$pass,
                self::$db,
                self::$port
            );
            if (!self::$conn) {
                http_response_code(500);
                echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
                exit;
            }
            mysqli_set_charset(self::$conn, 'utf8mb4');
        }
        return self::$conn;
    }
}