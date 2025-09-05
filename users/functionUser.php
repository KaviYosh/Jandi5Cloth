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


function getUser($userParams){

    /// Created By : Kavinda
    /// Date : 2025-7-5
    /// Description : This function is used to get user details by phone number


    global $conn;

    if($userParams['userName'] ==  null){

        return error422('Enter your phone Number');
    }

    $userName = mysqli_real_escape_string ($conn,$userParams['userName']);     

    $query = "SELECT UId,FirstName,RoldId,Password FROM UserInfo WHERE UserName = $userName AND Active = 1 ";
    $result = mysqli_query($conn,$query);

    if($result){

        if(mysqli_num_rows($result) == 1){

            $res = mysqli_fetch_assoc($result);

            $data = [

                'status'=> 200,
                'message'=> 'You are loging successfully.',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);
        }
        else{

            $data = [

                'status'=> 404,
                'message'=> 'Your user name is not a registered one',
            ];
            header('HTTP/1.0 404 User name is not a registered one');
            return json_encode($data);
        }


    }else{
        $data = [

            'status'=> 500,
            'message'=> 'Internal server Error',
        ];
        header('HTTP/1.0 500 Internal server Error');
        return json_encode($data); 
    }

}

function updateUser($userParams){

    global $conn;

    if(!isset($userParams['UID'])){

        return error422('User id not found in URL');
    }
    elseif($userParams['UID'] == null){
        return error422('Enter your user id');
    }

    $userPassword = mysqli_real_escape_string ($conn,$userParams['password']);
    $userID = mysqli_real_escape_string ($conn,$userParams['UID']);

    if(empty(trim($userPassword)))
    {
        return error422('Enter your phone Number');
    }   
    else{
        $query = " UPDATE userlogin 
                SET 
                    Password = '$userPassword'
                    
                WHERE 
                    ULID = '$userID' ";

        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($query);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Password updated Successfully',
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

function saveUser($userInput,$imageInfo,$userId){

    /// Created By : Kavinda
    /// Date : 2025-8-25
    /// Description : This function is used to save the system users

    global $conn;
    $path_db = '';
    $path_dbProf = '';

    if(isset($imageInfo['image'])){

        $img_name = $imageInfo['image']['name']; // To get file name
        $img_name_tmp = $imageInfo['image']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'front_'.time(); //New image name
        $path = "../profileImg/" . $img_new . "." . $ext; //New path to move
        $path_db = "profileImg/" . $img_new . "." . $ext;

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder       
        
    }

    //var_dump($userInput);exit;
    //var_dump($path_db);exit;

    $FirstName =  mysqli_real_escape_string($conn,$userInput['FirstName']);
    $LastName =  mysqli_real_escape_string($conn,$userInput['LastName']);
    $PhoneNo =  mysqli_real_escape_string($conn,$userInput['PhoneNo']);
    $UserName = mysqli_real_escape_string($conn,$userInput['UserName']);
    $Password=  mysqli_real_escape_string($conn,$userInput['Password']); 
    $Address=  mysqli_real_escape_string($conn,$userInput['Address']);
    $RoleId=  mysqli_real_escape_string($conn,$userInput['RoleId']);
    //$ProfileImagePath = mysqli_real_escape_string($conn,$path_db);
    $CreateUser=  $userId;
    $Active = 1;

   

    if(empty(trim($FirstName)))
    {
        return error422('Enter your First Name');
    }
    elseif(empty(trim($LastName)))
    {
        return error422('Enter your Last Name');
    }
    elseif(empty(trim($PhoneNo)))
    {
        return error422('Enter your Phone Number');
    }
    elseif(empty(trim($UserName)))
    {
        return error422('Enter your User Name');
    }
    elseif(empty(trim($Password)))
    {
        return error422('Enter your Password');
    }
    elseif(empty(trim($Address)))
    {
        return error422('Enter your Address');
    }
    elseif (empty(trim($RoleId)))
    {
        return error422('Enter Role Id');
    }
    else
    {
        //var_dump($path_db);exit;

        $query = "INSERT INTO UserInfo (FirstName, LastName, PhoneNo, UserName, Password, Address, RoleId, ProfileImagePath, Active, CreateBy) 
                  VALUES ('$FirstName', '$LastName', '$PhoneNo', '$UserName', '$Password', '$Address', '$RoleId','$path_db','$Active', '$CreateUser')";

        //var_dump($query);exit;        

        $result = mysqli_query($conn,$query);

        

        if($result)
        {
            //var_dump($result);exit;
            $data = [

                'status'=> 200,
                'message'=> 'User saved Successfully',
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



?>