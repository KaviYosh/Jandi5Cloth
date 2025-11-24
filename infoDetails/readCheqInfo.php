<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Pragma: no-cache");

include('functionInfo.php');
require '../middleware/verifyToken.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {
    $userData = verifyToken(); // Verify the token before proceeding

    if (isset($_GET['PHID'])) {        
        $shopParam = $_GET;
        //var_dump($shopParam);exit;  
        
        echo getChquePaymentInfo($shopParam);  // safer: tie shop to user
        //echo getShopById($shopParam, $userData['uid']);
   
    }
    else 
    {
        echo "payment Header can not be empty."; // return shops for this user only
        //echo getShopList($userData['uid']); // return shops for this user only
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
