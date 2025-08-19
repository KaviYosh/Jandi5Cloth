<?php
require '../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "Jandi5Cloth"; // Same as in login.php

function verifyToken() {
    $headers = apache_request_headers();
    
    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Authorization header missing"]);
        exit;
    }

    $authHeader = $headers['Authorization'];
    $arr = explode(" ", $authHeader);
    $token = $arr[1] ?? "";

    try {
        $decoded = JWT::decode($token, new Key("Jandi5Cloth", 'HS256'));
        return (array) $decoded; // Return user payload
    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid or expired token"]);
        exit;
    }
}
