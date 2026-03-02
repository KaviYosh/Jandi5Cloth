<?php

error_reporting(0);

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Access-Control-Allow-Method:POST');
header('Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Header, Authorization, x-Request-With');

include('functionMaterial.php');
require '../middleware/verifyToken.php';

   /// Created By : Kavinda
   /// Date : 2026-02-27
   /// Description : Create material record

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == 'POST'){

    $inputData = json_decode(file_get_contents("php://input"),true);  
    
    if(empty($inputData)){

        $userData = verifyToken();; // Verify the token before proceeding 
        
        var_dump($_POST);exit;

        $saveInvoice = saveMaterialInfo($_POST,$_FILES,$userData['uid']);

        //$saveInvoice = saveInfo();

    }
    else{

        $userData = verifyToken(); //Verify the token before proceeding 
        
        //var_dump(1234);exit;
        
        $saveInvoice = saveMaterialInfo($_POST, $userData['uid']);

       // $saveDebtors = saveDebtors($inputData);
        
    }
    echo $saveInvoice;

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