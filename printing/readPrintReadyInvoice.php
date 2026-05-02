<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('functionPrinting.php');
require '../middleware/verifyToken.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

var_dump(1234);exit;

if ($requestMethod === 'GET') {
    
    // ✅ Capture user data from token
    
    // exit;

    $userData = verifyToken();

    if (isset($_GET['PSID'])) {        
        $shopParam = $_GET;
        echo getPrintSndInvoiceById($shopParam); // safer: tie shop to user
        //echo getShopById($shopParam, $userData['uid']);
   
    }
    else 
    {
        echo getPrintSndInvoice(); // return shops for this user only
        //echo getShopList($userData['uid']); // return shops for this user only
    }

} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ]);
}
?>
