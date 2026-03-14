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
    /// Date : 2026-03-15
    /// Description : This function is used to save printing shop details 

    global $conn;
    


    $PShopName=  mysqli_real_escape_string($conn,$shopInput['PShopName']);
    $town=  mysqli_real_escape_string($conn,$shopInput['town']);
    $address=  mysqli_real_escape_string($conn,$shopInput['address']);
    $contactNo1= mysqli_real_escape_string($conn,$shopInput['contactNo1']);
    $contactNo2=  mysqli_real_escape_string($conn,$shopInput['contactNo2']);
    $CreateBy=  mysqli_real_escape_string($conn,$userId);
    $Active = 1;

   

    if(empty(trim($PShopName)))
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
    elseif(empty(trim($contactNo1)))
    {
        return error422('Enter contact No');
    }  
    else
    {
        //var_dump($path_db);exit;

        $query = "INSERT INTO PrintShop (PShopName, town, address, contactNo1, contactNo2, CreateBy, Active) 
                  VALUES ('$PShopName', '$town', '$address', '$contactNo1', '$contactNo2', '$CreateBy', '$Active')";
        

        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($result);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Print shop saved Successfully',
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

function deleteShopInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2026-03-15
   /// Description : This function is used to update the shop details

   //var_dump($shopParam);exit;

   global $conn;
   

   if(!isset($shopParam['PSID'])){

       return error422('Shop id not found in URL');
   }
   elseif($shopParam['PSID'] == null){
       return error422('Enter your Shop id');
   }
    
   $PSID =  mysqli_real_escape_string($conn,$shopParam['PSID']);
  
   if(empty(trim($id)))
   {
        return error422('Enter your Shop id');
   }
   else
   {
       //var_dump($path_dbProf);exit;

       $query = " UPDATE PrintShop 
           SET 
               Active = 0,
               ModifiedBy = '$userId'
               
           WHERE 
               PSID = '$PSID' ";
       
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

function updateShopInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2026-03-15
   /// Description : This function is used to update the shop details

   //var_dump($shopParam);exit;

   global $conn;
   

   if(!isset($shopParam['id'])){

       return error422('Shop id not found in URL');
   }
   elseif($shopParam['id'] == null){
       return error422('Enter your Shop id');
   }
   

  
   $PShopName=  mysqli_real_escape_string($conn,$shopInput['PShopName']);
   $PSID =  mysqli_real_escape_string($conn,$shopParam['PSID']);
   $town=  mysqli_real_escape_string($conn,$shopInput['town']);
   $address=  mysqli_real_escape_string($conn,$shopInput['address']);
   $contactNo1= mysqli_real_escape_string($conn,$shopInput['contactNo1']);
   $contactNo2=  mysqli_real_escape_string($conn,$shopInput['contactNo2']);
   


   if(empty(trim($PShopName)))
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
   elseif(empty(trim($contactNo1)))
   {
       return error422('Enter your contact No');
   }
   else
   {

       //var_dump($path_dbProf);exit;

       $query = " UPDATE PrintShop 
           SET 
               PShopName = '$PShopName', 
               town = '$town', 
               address = '$address', 
               contactNo1 = '$contactNo1',
               contactNo2 = '$contactNo2',
               ModifiedBy = '$userId'
               
           WHERE 
               PSID = '$PSID' ";
       
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




?>