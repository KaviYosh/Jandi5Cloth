<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require '../inc/dbconcls.php'; // Your DB connection file


 
// Connect to DB
$database = new Database();
$db = $database->getConnection();



// Get POST data
$data = json_decode(file_get_contents("php://input"));

if (!empty($data->UserName) && !empty($data->Password)) {
    $username = trim($data->UserName);
    $password = trim($data->Password);


    // Check credentials
    $query = "SELECT UId, UserName FROM UserInfo WHERE UserName = :username AND Password = :password LIMIT 1";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":password", $password); // ❗ For real projects, hash passwords!
    
    $stmt->execute();

    

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //var_dump($user);

        // Generate token
        $token = bin2hex(random_bytes(16));

        //var_dump($token); exit();

        // Save token in DB
        $update = "UPDATE UserInfo SET TokenID = :tokenID WHERE UId = :Id";
        $updateStmt = $db->prepare($update);
        $updateStmt->bindParam(":tokenID", $token);
        $updateStmt->bindParam(":Id", $user['UId']);
        $updateStmt->execute();

        // Send response
        echo json_encode([
            "status" => "success",
            "message" => "Login successful.",
            "token" => $token
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password."
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Username and password are required."
    ]);
}
?>
