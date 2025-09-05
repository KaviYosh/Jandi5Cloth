<?php

error_reporting(0);

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Access-Control-Allow-Method:POST');
header('Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Header, Authorization, x-Request-With');

include('functionUser.php');
require '../middleware/verifyToken.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == 'POST'){

    $inputData = json_decode(file_get_contents("php://input"),true);

    $userData = verifyToken();

    
    
    if(empty($inputData)){
        
        $saveUser = saveUser($_POST,$_FILES,$userData['uid']);
    }
    else{

        //echo $inputData['name'];
        
        //$saveDesign = saveDesign($inputData,$_FILES);

       // $saveDebtors = saveDebtors($inputData);
        
    }
    echo $saveUser;

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