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

function saveShop($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to save shop details 

    global $conn;
    


    $shopName=  mysqli_real_escape_string($conn,$shopInput['shopName']);
    $town=  mysqli_real_escape_string($conn,$shopInput['town']);
    $address=  mysqli_real_escape_string($conn,$shopInput['address']);
    $contact_no1= mysqli_real_escape_string($conn,$shopInput['contact_no1']);
    $contact_no2=  mysqli_real_escape_string($conn,$shopInput['contact_no2']);
    $CreateBy=  mysqli_real_escape_string($conn,$userId);
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
                'message'=> 'No Shops Found',
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
                'message'=> 'No Shops Found',
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

function updateShopInfo($shopParam,$userId){

     /// Created By : Kavinda
    /// Date : 2025-08-23
    /// Description : This function is used to update the shop details

    //var_dump($shopParam);exit;

    global $conn;
    

    if(!isset($shopParam['id'])){

        return error422('Shop id not found in URL');
    }
    elseif($shopParam['id'] == null){
        return error422('Enter your Shop id');
    }
    

    $shopName=  mysqli_real_escape_string($conn,$shopParam['shopName']);
    $id=  mysqli_real_escape_string($conn,$shopParam['id']);
    $town=  mysqli_real_escape_string($conn,$shopParam['town']);
    $address=  mysqli_real_escape_string($conn,$shopParam['address']);
    $contact_no1= mysqli_real_escape_string($conn,$shopParam['contact_no1']);
    $contact_no2=  mysqli_real_escape_string($conn,$shopParam['contact_no2']);
   
    
    if(empty(trim($shopName)))
    {
        return error422('Enter your Shop Name');
    }
    elseif(empty(trim($town)))
    {
        return error422('Enter your town');
    }
    elseif(empty(trim($address)))
    {
        return error422('Enter your address');
    }
    elseif(empty(trim($contact_no1)))
    {
        return error422('Enter your contact No');
    }
    else
    {

        //var_dump($path_dbProf);exit;

        $query = " UPDATE Shops 
            SET 
                shopName = '$shopName', 
                town = '$town', 
                address = '$address', 
                contact_no1 = '$contact_no1',
                contact_no2 = '$contact_no2',
                ModifiedBy = '$userId'
                
            WHERE 
                id = '$id' ";
        
        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($query);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Shop updated Successfully',
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
}

function deleteShopInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2025-08-23
   /// Description : This function is used to update the shop details

   //var_dump($shopParam);exit;

   global $conn;
   

   if(!isset($shopParam['id'])){

       return error422('Shop id not found in URL');
   }
   elseif($shopParam['id'] == null){
       return error422('Enter your Shop id');
   }
    
   $id =  mysqli_real_escape_string($conn,$shopParam['id']);
  
   if(empty(trim($id)))
   {
        return error422('Enter your Shop id');
   }
   else
   {
       //var_dump($path_dbProf);exit;

       $query = " UPDATE Shops 
           SET 
               Active = 0,
               ModifiedBy = '$userId'
               
           WHERE 
               id = '$id' ";
       
       $result = mysqli_query($conn,$query);

       if($result)
       {
           //var_dump($query);exit;
           $data = [

               'status'=> 200,
               'message'=> 'Shop Delete Successfully',
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
}

?>