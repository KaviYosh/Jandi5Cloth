<?php

header('Access-Control-Allow-Origin:*');
header('Content-Type:application/json;  charset=UTF-8');
header('Access-Control-Allow-Method:GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Content-Type: application/json; charset=utf-8");

// Optionally for HTTP/1.0 compatibility
header("Pragma: no-cache");

include('function.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod == "GET")
{
    // if (isset($_GET['id']) && isset($_GET['userTyId'])) {        

    //     $customerList = getDebtor($_GET);
    //     echo $customerList;
    // } 
    // elseif (isset($_GET['CIID'])) {

    //     $debtorParams = ['CIID' => $_GET['CIID']];
        
    //     //var_dump($debtorParams);exit;
    //     $customer = getDebtorForSearch($debtorParams);
    //     echo $customer;
    // } 
    // elseif (isset($_GET['UID']) && isset($_GET['UTId']) && isset($_GET['Status'])) {

    //     //var_dump($_GET['Status']);exit;

    //     $customer = getBlackListDebtors($_GET);
    //     echo $customer;
    // }
    // else {
    //     $customerList = getDebtorList();
    //     echo $customerList;
    // }

    if (isset($_GET['DesignNo'])) {
        $designId = $_GET['DesignNo'];
        $designDetails = getDesignById($designId);
        echo $designDetails;
    } 
    else {
        $designList = getDesignList();
        echo $designList;
    }
} 
else 
{
    $data = [

        'status' => 405,
        'message' =>  $requestMethod . 'Method Not Allowed',
    ];
    header('HTTP/1.0 405 Method is not Allowed');
    echo json_encode($data);

}
