<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET');
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

header("Cache-Control: no-cache, must-revalidate"); 
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); 
header("Pragma: no-cache");

include('functionBank.php');

$requestMethod = $_SERVER["REQUEST_METHOD"];

if ($requestMethod === 'GET') {

   
    $getBankList = getCommonBankList();

    // // Always return JSON
    // echo json_encode([
    //     'status' => 200,
    //     'data'   => $getBankList
    // ]);

    echo $getBankList;

} else {
    http_response_code(405);
    echo json_encode([
        'status'  => 405,
        'message' => $requestMethod . ' Method Not Allowed',
    ]);
}
?>
