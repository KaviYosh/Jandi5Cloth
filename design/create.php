<?php

error_reporting(0);

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Access-Control-Allow-Method:POST');
header('Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Header, Authorization, x-Request-With');

include('function.php');


$requestMethod = $_SERVER["REQUEST_METHOD"];


if($requestMethod == 'POST'){

    $inputData = json_decode(file_get_contents("php://input"),true);
    
    
    if(empty($inputData)){

        //echo $_POST['name'];
        $saveDesign = saveDesign($_POST,$_FILES);

    }
    else{

        //echo $inputData['name'];
        //var_dump($inputData);exit;
        //$saveDesign = saveDesign($inputData,$_FILES);

       // $saveDebtors = saveDebtors($inputData);
        
    }
    echo $saveDebtors;

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