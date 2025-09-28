<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('functionInvoice.php');
require '../middleware/verifyToken.php';

   /// Created By : Kavinda
   /// Date : 2025-09-15
   /// Description : Read the invoice Header  by ID and as a list

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {
    
    
    $userData = verifyToken();

    if (isset($_GET['IHID'])) {   

        $shopParam = $_GET;
        //var_dump($invoiceParam);exit;
        echo getInvoiceListById($shopParam); 
        
   
    } 
    else if(isset($_GET['ShopID']))
    {
        $shopParam = $_GET;

        echo getInvoiceListById($shopParam); 
    }
    
    else {

        echo getInvoiceList(); 
    }

} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ]);
}

?>
