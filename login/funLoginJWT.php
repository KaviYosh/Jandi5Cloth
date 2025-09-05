<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../inc/dbconcls.php'; // Your DB connection file
require '../vendor/autoload.php'; // Composer autoload for JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Secret key for JWT (⚠️ store in env/config file in production)
$secret_key = "Jandi5Cloth";

// Connect to DB
$database = new Database();
$db = $database->getConnection();

// Get POST data
//$data = json_decode(file_get_contents("php://input"));

// 🔑 Read from form-data
$username = isset($_POST['UserName']) ? trim($_POST['UserName']) : '';
$password = isset($_POST['Password']) ? trim($_POST['Password']) : '';

// Validate request
if (!empty($username) && !empty($password)) {
    // $username = trim($data->UserName);
    // $password = trim($data->Password);

    try {
        // Check user exists
        $query = "SELECT UId, UserName, Password FROM UserInfo WHERE UserName = :username LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(":username", $username);
        $stmt->execute();

        

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verify password (⚠️ plain text comparison, not secure!)
            if ($password === $user['Password']) {

                $issuedAt   = time();
                $expire     = $issuedAt + 3600; // valid for 1 hour

                $payload = [
                    "iat"      => $issuedAt,
                    "exp"      => $expire,
                    "uid"      => $user['UId'],
                    "username" => $user['UserName']
                ];

                // Generate JWT
                $jwt = JWT::encode($payload, $secret_key, 'HS256');

                echo json_encode([
                    "status"  => "success",
                    "message" => "Login successful.",
                    "token"   => $jwt
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    "status"  => "error",
                    "message" => "Invalid username or password."
                ]);
            }
        } else {
            http_response_code(401);
            echo json_encode([
                "status"  => "error",
                "message" => "Invalid username or password."
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            "status"  => "error",
            "message" => "Server error.",
            "error"   => $e->getMessage()
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        "status"  => "error",
        "message" => "Username and password are required."
    ]);
}
?>