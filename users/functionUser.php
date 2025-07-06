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

    $query = "SELECT ULId,FullName,RoldId,Password FROM UserInfo WHERE UserName = $userName AND Active = 1 ";
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

?>