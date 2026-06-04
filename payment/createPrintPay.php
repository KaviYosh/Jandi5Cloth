<?php

error_reporting(0);

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Access-Control-Allow-Method:POST');
header('Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Header, Authorization, x-Request-With');

include('functionPay.php');
require '../middleware/verifyToken.php';

   /// Created By : Kavinda
   /// Date : 2026-06-04
   /// Description : Create the Bank payment for an invoice


$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == 'POST'){

    $inputData = json_decode(file_get_contents("php://input"),true);
    
    if(empty($inputData)){

        $userData = verifyToken();; // Verify the token before proceeding 
        $savePrintPayment = savePrintPaymentInfo($_POST,$userData['uid']);

    }
    else{

        $userData = verifyToken(); //Verify the token before proceeding 
        $savePrintPayment = savePrintPaymentInfo($_POST, $userData['uid']);

    }
    echo $savePrintPayment;

}
else{

    $data = [

        'status'=> 405,
        'message'=>  $requestMethod. ' Method Not Allowed',
    ];
    header('HTTP/1.0 405 Method is not Allowed');
    echo json_encode($data);

}


?>