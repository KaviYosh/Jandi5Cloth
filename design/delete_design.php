<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('function.php');
require '../middleware/verifyToken.php';

$requestMethod = $_SERVER["REQUEST_METHOD"];


if($requestMethod == 'POST')
{

    //var_dump(1234556);exit;
    $designData = json_decode(file_get_contents("php://input"),true);
    
    $userData = verifyToken();
    //$shopData['UserID'] = $userData['uid']; 

    if(empty($designData))
    {  
        $updateDesign = deleteDesignInfo($_POST,$userData['uid']); 
    }
    else
    {
        $updateDesign = deleteDesignInfo($designData,$userData['uid']); 
    }
    echo $updateDesign;
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