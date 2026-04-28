<?php

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    public static function success($message, $data = null, $status = 200) {
        $payload = ["success" => true, "message" => $message];
        if ($data !== null) $payload["data"] = $data;
        self::json($payload, $status);
    }

    public static function error($message, $status = 400) {
        self::json(["success" => false, "message" => $message], $status);
    }
}