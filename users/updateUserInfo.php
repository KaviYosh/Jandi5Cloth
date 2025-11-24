<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('functionUser.php');
require '../middleware/verifyToken.php';


$requestMethod = $_SERVER["REQUEST_METHOD"];


if($requestMethod == 'POST')
{
   
    $userData = verifyToken();
  
    if(!empty($_POST))
    {  
         //var_dump($_POST);exit;

        $userInfo = updateUserInfo($_POST,$_FILES,$userData['uid']);
        
    }
    else
    {
        $updateShop = "Record not updated. Missing parameters"; 
    }
    echo $userInfo;
}
else
{
    $data = [

        'status'=> 405,
        'message'=>  $requestMethod. ' Method Not Allowed',
    ];
    header('HTTP/1.0 405 Method is not Allowed');
    echo json_encode($data);
}


?>