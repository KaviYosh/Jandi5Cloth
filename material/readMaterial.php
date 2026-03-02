<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('functionMaterial.php');
require '../middleware/verifyToken.php';

   /// Created By : Kavinda
   /// Date : 2026-02-27
   /// Description : Read the Material details
$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {
    
    
    $userData = verifyToken();

    if (isset($_GET['MID'])) {   

        $shopParam = $_GET;
        //var_dump($invoiceParam);exit;
        echo getMaterialInfoByID($shopParam); 
        
   
    } 
    else if(isset($_GET['ShopID']))
    {
        $shopParam = $_GET;

        echo getMaterialInfo($shopParam); 
    }
    
    else {

        echo getMaterialInfo(); 
    }

} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ]);
}

?>
