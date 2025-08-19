<?php

require '../inc/dbcon.php';


function error422($message){

    $data = [

        'status'=> 422,
        'message'=> $message,        
    ];
    header("HTTP/1.0 422 Unprocessable Entity");
    echo json_encode($data);
    exit();
}

function saveShop($shopInput){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to save shop details 

    global $conn;
    


    $shopName=  mysqli_real_escape_string($conn,$designInput['shopName']);
    $town=  mysqli_real_escape_string($conn,$designInput['town']);
    $address=  mysqli_real_escape_string($conn,$designInput['address']);
    $contact_no1= mysqli_real_escape_string($conn,$designInput['contact_no1']);
    $contact_no2=  mysqli_real_escape_string($conn,$designInput['contact_no2']);
    $CreateBy=  mysqli_real_escape_string($conn,$designInput['CreateBy']);
    $Active = 1;

   

    if(empty(trim($shopName)))
    {
        return error422('Enter your Shop Name');
    }
    elseif(empty(trim($town)))
    {
        return error422('Enter shop town');
    }
    elseif(empty(trim($address)))
    {
        return error422('Enter shop address');
    }
    elseif(empty(trim($contact_no1)))
    {
        return error422('Enter contact No');
    }  
    else
    {
        //var_dump($path_db);exit;

        $query = "INSERT INTO Shops (shopName, town, address, contact_no1, contact_no2, CreateBy, Active) 
                  VALUES ('$shopName', '$town', '$address', '$contact_no1', '$contact_no2', '$CreateBy', '$Active')";

        

        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($result);exit;
            $data = [

                'status'=> 200,
                'message'=> 'shop saved Successfully',
            ];
            header('HTTP/1.0 200 Success');
            return json_encode($data);
        }
        else{
            $data = [

                'status'=> 500,
                'message'=> 'Internal server Error',
            ];
            header('HTTP/1.0 500 Internal server Error');
            return json_encode($data);
        }
        
    }
    // Close the database connection
    $conn->close();
}

function getShopById($shopParam) {
    
    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to get shop details by ID

    global $conn;

    if (!isset($shopParam) || !is_array($shopParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($shopParam['id']) || empty($shopParam['id'])) {
        return error422('Enter your shop Number');
    }

    $id = mysqli_real_escape_string($conn, $shopParam['id']);

    $query = "SELECT * FROM Shops WHERE Active = 1 AND id = '$id'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Shop Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Designs Found',
            ];
            header('HTTP/1.0 404 Not Found');
            return json_encode($data);
        }
    } else {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error',
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getShopList() {
    
    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to get shop list

    global $conn;

    $query = "SELECT id,shopName,town,address, contact_no1, contact_no2 FROM Shops WHERE Active = 1 ORDER BY id DESC";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Shop list Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Designs Found',
            ];
            header('HTTP/1.0 404 Not Found');
            return json_encode($data);
        }
    } else {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error',
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

?>