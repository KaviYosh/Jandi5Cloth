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

function saveDesign($debtorInput,$imageInfo){

    /// Created By : Kavinda
    /// Date : 2025-7-5
    /// Description : This function is used to save design details with images

    global $conn;
    $path_db = '';
    $path_dback = '';
    $path_dbProf = '';

   
    ///NIC image Upload Section 
    if(isset($imageInfo['image'])){

        $img_name = $imageInfo['image']['name']; // To get file name
        $img_name_tmp = $imageInfo['image']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'front_'.time(); //New image name
        $path = "../uploads/" . $img_new . "." . $ext; //New path to move
        $path_db = "uploads/" . $img_new . "." . $ext;

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder       
        
    }

    if(isset($imageInfo['imageBack'])){

        $img_nameBack = $imageInfo['imageBack']['name']; // To get file name
        $img_name_tmpBack = $imageInfo['imageBack']['tmp_name']; // To get file name temporary location  
        
        $ext2 = pathinfo($img_nameBack, PATHINFO_EXTENSION);
        $img_new2 = 'back_'.time(); //New image name
        $pathBack = "../uploads/" . $img_new2 . "." . $ext2; //New path to move
        $path_dback = "uploads/" . $img_new2 . "." . $ext2;

        move_uploaded_file($img_name_tmpBack, $pathBack);
    }


    $DesignName =  mysqli_real_escape_string($conn,$debtorInput['DesignName']);
    $Description =  mysqli_real_escape_string($conn,$debtorInput['Description']);
    $PricePerUnit =  mysqli_real_escape_string($conn,$debtorInput['PricePerUnit']);
    $DateAdded=  mysqli_real_escape_string($conn,$debtorInput['DateAdded']);
    $ImagePath=  mysqli_real_escape_string($conn,$debtorInput['ImagePath']);
    $CreateUser=  mysqli_real_escape_string($conn,$debtorInput['CreateUser']);
    $Active = 1;

    ///Get the current Year

    $currentYear = date("Y");

    if(empty(trim($DesignName)))
    {
        return error422('Enter your Design Name');
    }
    elseif(empty(trim($Description)))
    {
        return error422('Enter your Description');
    }
    elseif(empty(trim($PricePerUnit)))
    {
        return error422('Enter Item Price Per Unit');
    }
    elseif(empty(trim($DateAdded)))
    {
        return error422('Enter Date Added');
    }  
    else
    {
        $query1 = "SELECT * FROM `customerinfo` WHERE `NICNo` LIKE '%$NICNo%' AND Active='1'";
        //var_dump($query1); exit;
        $result1 = mysqli_query($conn,$query1);
        $rowcount=mysqli_num_rows($result1);



        if($rowcount > 0)
        {
            $data = [

                'status'=> 201,
                'message'=> 'Already exist this customer',
            ];
            header('HTTP/1.0 201 Created');
            return json_encode($data);  
        }
        else
        {

            $queryMaxID = "SELECT MAX(CIID) AS CusID FROM `customerinfo`";
            $resultMaxID = mysqli_query($conn, $queryMaxID);

            // Fetch the result as an associative array
            $row = mysqli_fetch_assoc($resultMaxID);

            // Get the CusID (maximum CIID)
            $maxCIID = $row['CusID'];

            $CusRegNo = "YW" . $currentYear . "-" .$maxCIID+1;
            
            //var_dump($CusRegNo);exit;

            $query = "INSERT INTO customerinfo (
                FullName, 
                Address, 
                NICNo, 
                ContactNo1,
                Gender, 
                CreateUser,
                ProfileImage,
                NICFrontImg,
                NICBackImg,
                CusRegNo,
                Active
            ) VALUES (
                '$FullName', 
                '$Address', 
                '$NICNo', 
                '$ContactNo1', 
                '$Gender', 
                '$CreateUser',
                '$path_dbProf',
                '$path_db',
                '$path_dback',
                '$CusRegNo',
                '$Active'
            )";      
            //var_dump($query);exit;
            $result = mysqli_query($conn,$query);

            if($result)
            {
                $data = [

                    'status'=> 201,
                    'message'=> 'Customer created Successfully',
                ];
                header('HTTP/1.0 201 Created');
                return json_encode($data);
            }
            else
            {
                $data = [

                    'status'=> 500,
                    'message'=> 'Internal server Error',
                ];
                header('HTTP/1.0 500 Internal server Error');
                return json_encode($data);
            }  
        }
        
    }
    // Close the database connection
    $conn->close();
}

function getDebtorList(){

    global $conn;

    $query = "SELECT * FROM customerinfo ";
    $query_run = mysqli_query($conn,$query);
    
    if ($query_run){

        if(mysqli_num_rows($query_run) > 0 ){

            $res = mysqli_fetch_all($query_run,MYSQLI_ASSOC);

            $data = [

                'status'=> 200,
                'message'=> 'Debtors List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);

        }else
        {
            $data = [

                'status'=> 404,
                'message'=> 'No Debtors Found',
            ];
            header('HTTP/1.0 404 No Debtors Found');
            return json_encode($data);

        }

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

function getDebtor($debtorParams){

    global $conn;

    if($debtorParams['id'] ==  null){

        return error422('Enter your customer id');
    }

    $createdUserId = mysqli_real_escape_string ($conn,$debtorParams['id']);
    $createdUserTyId = mysqli_real_escape_string ($conn,$debtorParams['userTyId']);
    
    
    if($createdUserTyId == "1") {
        $query1 = "SELECT CIID, CusRegNo, FullName, Address, NICNo, ContactNo1, Gender, NICFrontImg, NICBackImg, Active, CreateUser 
                   FROM `customerinfo` 
                   WHERE `Active` = '1' 
                   ORDER BY CIID ASC";
                          
        $result = mysqli_query($conn, $query1);
    } else {
        $query2 = "SELECT CIID, CusRegNo, FullName, Address, NICNo, ContactNo1, Gender, NICFrontImg, NICBackImg, Active, CreateUser 
                   FROM `customerinfo` 
                   WHERE CreateUser = '$createdUserId' AND `Active` = '1' 
                   ORDER BY CIID ASC";
        $result = mysqli_query($conn, $query2);
    }
    
    
    
    if($result){

        if(mysqli_num_rows($result) > 0 ){

            $res = mysqli_fetch_all($result,MYSQLI_ASSOC); //MYSQLI_ASSOC

            $data = [

                'status'=> 200,
                'message'=> 'Debtors List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);

             // Clear the $res variable
            $res = null;

        }else
        {
            $data = [

                'status'=> 404,
                'message'=> 'No Debtors Found',
            ];
            header('HTTP/1.0 404 No Debtors Found');
            return json_encode($data);

        }

         // Free the result set
        $result->free();

    }else{
        $data = [

            'status'=> 500,
            'message'=> 'Internal server Error',
        ];
        header('HTTP/1.0 500 Internal server Error');
        return json_encode($data); 
    }
    // Close the database connection
    $conn->close();
}

function getDebtorForSearch($debtorParams){

    global $conn;

     //var_dump($debtorParams);exit;
    if($debtorParams['CIID'] ==  null){

        return error422('Enter required data');
    }   

    $debtorsId = mysqli_real_escape_string ($conn,$debtorParams['CIID']);
   
    //var_dump($query);exit;
    $query = "SELECT * FROM customerinfo WHERE CIID = '$debtorsId' AND (Active =1 OR Active = 0  OR Active = 2)";     
    
    $result = mysqli_query($conn,$query);
    
    if($result){
        
        if(mysqli_num_rows($result) > 0){

            $res = mysqli_fetch_assoc($result); 
            //$res = mysqli_fetch_all($result);
            //var_dump($res); exit;
            $data = [

                'status'=> 200,
                'message'=> 'Debtors List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);
        }
        else{

            $data = [

                'status'=> 404,
                'message'=> 'No Debtors Found',
            ];
            header('HTTP/1.0 404 No Debtors Found');
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

function getBlackListDebtors($debtorParams){

    global $conn;

    if($debtorParams['UID'] ==  null){

        return error422('Enter your customer id');
    }

    $createdUserId = mysqli_real_escape_string ($conn,$debtorParams['UID']);
    $createdUserTyId = mysqli_real_escape_string ($conn,$debtorParams['UTId']);
    $blackListStatus = mysqli_real_escape_string ($conn,$debtorParams['Status']);
    //var_dump($blackListStatus);exit;
    
    if($createdUserTyId == "1") {
        $query1 = "SELECT CIID, CusRegNo, FullName, Address, NICNo, ContactNo1, Gender, NICFrontImg, NICBackImg, Active, CreateUser, ModifiedDate 
                   FROM `customerinfo` 
                   WHERE `Active` = '$blackListStatus' 
                   ORDER BY ModifiedDate ASC"; 

        $result = mysqli_query($conn, $query1);

    } else {
        $query2 = "SELECT CIID, CusRegNo, FullName, Address, NICNo, ContactNo1, Gender, NICFrontImg, NICBackImg, Active, CreateUser, ModifiedDate 
                   FROM `customerinfo` 
                   WHERE CreateUser = '$createdUserId' AND `Active` = '$blackListStatus' 
                   ORDER BY ModifiedDate ASC";

        $result = mysqli_query($conn, $query2);
    }
    if($result){

        if(mysqli_num_rows($result) > 0 ){

            $res = mysqli_fetch_all($result,MYSQLI_ASSOC); //MYSQLI_ASSOC

            $data = [

                'status'=> 200,
                'message'=> 'Blacklisted Debtors List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);

             // Clear the $res variable
            $res = null;

        }else
        {
            $data = [

                'status'=> 404,
                'message'=> 'No blacklisted Debtors Found',
            ];
            header('HTTP/1.0 404 No blacklisted Debtors Found');
            return json_encode($data);

        }

         // Free the result set
        $result->free();

    }else{
        $data = [

            'status'=> 500,
            'message'=> 'Internal server Error',
        ];
        header('HTTP/1.0 500 Internal server Error');
        return json_encode($data); 
    }
    // Close the database connection
    $conn->close();
}

function updateDebtorWithImage($debtorInput,$debtorParams,$imageInfo){

    global $conn;
    $path_dbProf = '';

    
    if(!isset($debtorParams['id'])){

        return error422('Debtor id not found in URL');
    }
    elseif($debtorParams['id'] == null){
        return error422('Enter your Debtor id');
    }

    //var_dump($imageInfo['imageProfile']);exit;
    ///NIC image Upload Section 
    if(isset($imageInfo['imageProfile'])){

        $img_name = $imageInfo['imageProfile']['name']; // To get file name
        $img_name_tmp = $imageInfo['imageProfile']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'profileImg_Update'.time(); //New image name
        $path = "../debtorsProfileImg/" . $img_new . "." . $ext; //New path to move
        $path_dbProf = "debtorsProfileImg/" . $img_new . "." . $ext;

        //var_dump($img_name);exit;       

        //imagedestroy($image);

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder

    }


    $DebtorId =  mysqli_real_escape_string($conn,$debtorParams['id']);
    $FullName =  mysqli_real_escape_string($conn,$debtorInput['Fullname']);
    $Address =  mysqli_real_escape_string($conn,$debtorInput['Address']);
    $NICNo =  mysqli_real_escape_string($conn,$debtorInput['NICNo']);
    $ContactNo1=  mysqli_real_escape_string($conn,$debtorInput['ContactNo1']);
    $Gender=  mysqli_real_escape_string($conn,$debtorInput['Gender']);
    //$today = date('Y-m-d');
    
    if(empty(trim($FullName)))
    {
        return error422('Enter your Full Name');
    }
    elseif(empty(trim($Address)))
    {
        return error422('Enter your Address');
    }
    elseif(empty(trim($NICNo)))
    {
        return error422('Enter your NIC No');
    }
    elseif(empty(trim($ContactNo1)))
    {
        return error422('Enter your contact No');
    }
    else
    {

        //var_dump($path_dbProf);exit;

        $query = " UPDATE customerinfo 
        SET 
            FullName = '$FullName', 
            Address = '$Address', 
            NICNo = '$NICNo', 
            ContactNo1 = '$ContactNo1',
            ProfileImage = '$path_dbProf',
            Gender = '$Gender'
        WHERE 
            CIID = '$DebtorId' ";      

        
        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($query);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Debtors updated Successfully',
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

function updateDebtor($debtorInput,$debtorParams){

    global $conn;
    $path_dbProf = '';

    if(!isset($debtorParams['id'])){

        return error422('Debtor id not found in URL');
    }
    elseif($debtorParams['id'] == null){
        return error422('Enter your Debtor id');
    }

    ///NIC image Upload Section 
    if(isset($imageInfo['imageProfile'])){

        $img_name = $imageInfo['imageProfile']['name']; // To get file name
        $img_name_tmp = $imageInfo['imageProfile']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'profileImg_Update'.time(); //New image name
        $path = "../debtorsProfileImg/" . $img_new . "." . $ext; //New path to move
        $path_dbProf = "debtorsProfileImg/" . $img_new . "." . $ext;

        //var_dump($img_name);exit;       

        //imagedestroy($image);

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder

    }


    $DebtorId =  mysqli_real_escape_string($conn,$debtorParams['id']);
    $FullName =  mysqli_real_escape_string($conn,$debtorInput['Fullname']);
    $Address =  mysqli_real_escape_string($conn,$debtorInput['Address']);
    $NICNo =  mysqli_real_escape_string($conn,$debtorInput['NICNo']);
    $ContactNo1=  mysqli_real_escape_string($conn,$debtorInput['ContactNo1']);
    $Gender=  mysqli_real_escape_string($conn,$debtorInput['Gender']);
    //$today = date('Y-m-d');
    
    if(empty(trim($FullName)))
    {
        return error422('Enter your Full Name');
    }
    elseif(empty(trim($Address)))
    {
        return error422('Enter your Address');
    }
    elseif(empty(trim($NICNo)))
    {
        return error422('Enter your NIC No');
    }
    elseif(empty(trim($ContactNo1)))
    {
        return error422('Enter your contact No');
    }
    else
    {

        //var_dump($path_dbProf);exit;

        $query = " UPDATE customerinfo 
            SET 
                FullName = '$FullName', 
                Address = '$Address', 
                NICNo = '$NICNo', 
                ContactNo1 = '$ContactNo1',
                Gender = '$Gender'
            WHERE 
                CIID = '$DebtorId' ";
        
        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($query);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Debtors updated Successfully',
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

function deleteDebtor($debtorParams){

    global $conn;

    if(!isset($debtorParams['id'])){

        return error422('Debtor id not found in URL');
    }
    elseif($debtorParams['id'] == null){
        return error422('Enter your Debtor id');
    }

    $DebtorId =  mysqli_real_escape_string($conn, $debtorParams['id']);
    //$today = date('Y-m-d');

    $query1 = "SELECT SUM(OngoingLoanCount) as TotalOngoingLoans FROM (
        SELECT Count(CLHID) As OngoingLoanCount 
        FROM cusDailyWeekLoanheader
        WHERE CusInfoID = $DebtorId AND Active = 1
        UNION ALL
        SELECT Count(CMLHID) As OngoingLoanCountMonth 
        FROM cusMonthlyLoanHeader
        WHERE CusInfoID = $DebtorId AND Active = 1
    ) as OngoingLoans";

    $result1 = mysqli_query($conn, $query1);

    if ($result1) {
        $row = mysqli_fetch_assoc($result1);
        $totalOngoingLoans = $row['TotalOngoingLoans'];

        if ($totalOngoingLoans > 0) {
            $data = [

                'status' => 201,
                'message' => 'There are ongoing loans for this debtor.',
            ];
            header('HTTP/1.0 201 Not Black Listed');
            return json_encode($data);
        } else {

            $query = " UPDATE customerinfo 
            SET 
                Active = 0                  
            WHERE 
                CIID = '$DebtorId' ";

            $result = mysqli_query($conn, $query);

            if ($result) {
                //var_dump($query);exit;
                $data = [

                    'status' => 200,
                    'message' => 'Debtor Deleted Successfully',
                ];
                header('HTTP/1.0 200 Success');
                return json_encode($data);
            } else {
                $data = [

                    'status' => 500,
                    'message' => 'Internal server Error',
                ];
                header('HTTP/1.0 500 Internal server Error');
                return json_encode($data);
            }
        }
    }
}

function blackListUpdateDebtor($debtorParams)
{

    global $conn;

    if (!isset($debtorParams['id'])) {

        return error422('Debtor id not found in URL');
    } elseif ($debtorParams['id'] == null) {
        return error422('Enter your Debtor id');
    }

    $DebtorId =  mysqli_real_escape_string($conn, $debtorParams['id']);
    $Status =  mysqli_real_escape_string($conn, $debtorParams['Status']);

    // $query1 = "SELECT SUM(OngoingLoanCount) as TotalOngoingLoans FROM (
    //             SELECT Count(CLHID) As OngoingLoanCount 
    //             FROM cusDailyWeekLoanheader
    //             WHERE CusInfoID = 66 AND Active = 1
    //             UNION ALL
    //             SELECT Count(CMLHID) As OngoingLoanCountMonth 
    //             FROM cusMonthlyLoanHeader
    //             WHERE CusInfoID = 66 AND Active = 1
    //         ) as OngoingLoans";

    // $result1 = mysqli_query($conn, $query1);

    // if ($result1) {
    //     $row = mysqli_fetch_assoc($result1);
    //     $totalOngoingLoans = $row['TotalOngoingLoans'];

    //     if ($totalOngoingLoans > 0) {
    //         $data = [

    //             'status' => 201,
    //             'message' => 'There are ongoing loans for this debtor.',
    //         ];
    //         header('HTTP/1.0 201 Not Black Listed');
    //         return json_encode($data);
    //     } else {

    //     }
    // }


    //$today = date('Y-m-d');

    $query = " UPDATE customerinfo 
    SET 
       Active = $Status,
       ModifiedDate = CURDATE()                    
   WHERE 
       CIID = '$DebtorId' ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        //var_dump($query);exit;
        $data = [

            'status' => 200,
            'message' => 'Debtors blacklisted Successfully',
        ];
        header('HTTP/1.0 200 Success');
        return json_encode($data);
    } else {
        $data = [

            'status' => 500,
            'message' => 'Internal server Error',
        ];
        header('HTTP/1.0 500 Internal server Error');
        return json_encode($data);
    }
}


?>