<?php
require '../vendor/autoload.php'; // Composer autoload
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function verifyToken() {
    $secret_key = "Jandi5Cloth"; // keep safe

    // Get Authorization header safely across Apache/Nginx/FastCGI
    $headers = null;

    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);
    } 
    elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or FastCGI
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
    } 
    elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    // Debugging: check what you actually get
    // var_dump($headers); exit();

    if (!$headers) {
        http_response_code(401);
        echo json_encode([
            "status"  => "error",
            "message" => "Authorization header missing"
        ]);
        exit;
    }

    $authHeader = $headers;
    $arr = explode(" ", $authHeader);
    $token = $arr[1] ?? "";

    try {
        $decoded = JWT::decode($token, new Key("Jandi5Cloth", 'HS256'));
        return (array) $decoded; // Return user payload
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode([
            "status"  => "error",
            "message" => "Invalid or expired token"
        ]);
        exit;
    }

}
