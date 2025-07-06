<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json');
header('Access-Control-Allow-Method:GET');
header('Access-Control-Allow-Header: Content-Type, Access-Control-Allow-Header, Authorization, x-Request-With');

include('functionUser.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if($requestMethod == "GET")
{
    if(isset($_GET['userName'])){

        $userInfo = getUser($_GET);
        echo $userInfo;
      

    }
    else
    {
        $data = [
            'status'=> 422,
            'message'=> 'Phone Number is required',
        ];
        header('HTTP/1.0 422 Unprocessable Entity');
        echo json_encode($data);
    }
}
else
{
    $data = [

        'status'=> 405,
        'message'=>  $requestMethod. 'Method Not Allowed',
    ];
    header('HTTP/1.0 405 Method is not Allowed');
    echo json_encode($data);
}

?>