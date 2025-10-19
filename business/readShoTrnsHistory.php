<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");

include('functionBuss.php');
require '../middleware/verifyToken.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {
    $userData = verifyToken(); // Verify the token before proceeding

    if (isset($_GET['ShopID']) && !empty($_GET['ShopID'])) {
        
        // If an ID is provided, fetch the specific design
        $designParam = $_GET; // use query parameters
        echo getShopTrnsHistory($designParam);
    } 
    else {
        
        echo "Can not find shop ID";
    }

} 
else 
{
    // Invalid method handling
    $data = [
        'status' => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ];
    header('HTTP/1.0 405 Method Not Allowed');
    echo json_encode($data);
}

?>
