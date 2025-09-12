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

function getBankById($bankParam) {

    /// Created By : Kavinda
   /// Date : 2025-08-23
   /// Description : This function is used to update the shop details


    global $conn;

    if (!isset($bankParam) || !is_array($bankParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($bankParam['id']) || empty($bankParam['id'])) {
        return error422('Enter your Bank id');
    }

    $id = mysqli_real_escape_string($conn, $bankParam['id']);

    $query = "SELECT * FROM OwnerBankInfo WHERE Active = 1 AND id = '$id'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Bank List Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Bank Found',
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

function getBankList(){

    /// Created By : Kavinda
   /// Date : 2025-08-23
   /// Description : This function is used to update the shop details


    global $conn;

    $query = "SELECT * FROM OwnerBankInfo WHERE Active = 1 ORDER BY id DESC";
    $query_run = mysqli_query($conn,$query);
    
    if ($query_run){

        if(mysqli_num_rows($query_run) > 0 ){

            $res = mysqli_fetch_all($query_run,MYSQLI_ASSOC);

            $data = [

                'status'=> 200,
                'message'=> 'Bank List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);

        }else
        {
            $data = [

                'status'=> 404,
                'message'=> 'No Bank Found',
            ];
            header('HTTP/1.0 404 No Bank Found');
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

function saveBank($bankParam,$userId) {

    /// Created By : Kavinda
    /// Date : 2025-08-23
    /// Description : This function is used to insert bank details

    global $conn;

    if (!isset($bankParam) || !is_array($bankParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($bankParam['name']) || empty($bankParam['name'])) {
        return error422('Enter the Bank Name');
    }

    if (!isset($bankParam['account_no']) || empty($bankParam['account_no'])) {
        return error422('Enter the Account Number');
    }
    
    
    $name = mysqli_real_escape_string($conn, $bankParam['name']);
    $account_no = mysqli_real_escape_string($conn, $bankParam['account_no']);
    $branch = mysqli_real_escape_string($conn, $bankParam['branch']);
    $CreateBy = $userId;


    // Check for duplicate bank name and account number

    $check_duplicate_query = "SELECT * FROM OwnerBankInfo WHERE name LIKE '%$name%' AND account_no = '$account_no' AND Active = 1";
    $check_duplicate_query_run = mysqli_query($conn, $check_duplicate_query);

    if ($check_duplicate_query_run && mysqli_num_rows($check_duplicate_query_run) > 0) {
        return error422('Duplicate bank name and account number found.');
    }

    $query = "INSERT INTO OwnerBankInfo (name,branch,account_no,CreateBy ,Active) VALUES ('$name', '$branch','$account_no','$CreateBy', 1)";
    
   
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        $data = [
            'status' => 201,
            'message' => 'Bank Details Inserted Successfully',
        ];
        header('HTTP/1.0 201 Created');
        return json_encode($data);
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function deleteBank($bankParam,$userId) {

    /// Created By : Kavinda
    /// Date : 2025-08-23
    /// Description : This function is used to delete the bank details

    global $conn;

    if (!isset($bankParam) || !is_array($bankParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($bankParam['id']) || empty($bankParam['id'])) {
        return error422('Enter the Bank ID');
    }

    $id = mysqli_real_escape_string($conn, $bankParam['id']);
    $ModifiedBy = $userId;


    $query = "UPDATE OwnerBankInfo SET Active = 0, ModifiedBy = '$ModifiedBy' WHERE id = '$id'";
    
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_affected_rows($conn) > 0) {
            $data = [
                'status' => 200,
                'message' => 'Bank Details Deleted Successfully',
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No Bank Found with the given ID',
            ];
            header('HTTP/1.0 404 Not Found');
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}


function updateBankDetails($bankParam,$userId) {

    /// Created By : Kavinda
    /// Date : 2025-08-23
    /// Description : This function is used to update bank details

    global $conn;

    if (!isset($bankParam) || !is_array($bankParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($bankParam['id']) || empty($bankParam['id'])) {
        return error422('Enter the Bank ID');
    }

    $id = mysqli_real_escape_string($conn, $bankParam['id']);
    $ModifiedBy = $userId;

    $fields = [];
    if (isset($bankParam['name']) && !empty($bankParam['name'])) {
        $name = mysqli_real_escape_string($conn, $bankParam['name']);
        $fields[] = "name = '$name'";
    }

    if (isset($bankParam['account_no']) && !empty($bankParam['account_no'])) {
        $account_no = mysqli_real_escape_string($conn, $bankParam['account_no']);
        $fields[] = "account_no = '$account_no'";
    }

    if (isset($bankParam['branch']) && !empty($bankParam['branch'])) {
        $branch = mysqli_real_escape_string($conn, $bankParam['branch']);
        $fields[] = "branch = '$branch'";
    }

    if (empty($fields)) {
        return error422('No fields to update.');
    }
    
    // Always update ModifiedBy
    $fields[] = "ModifiedBy = '$ModifiedBy'";
    $fields_query = implode(', ', $fields);

    $query = "UPDATE OwnerBankInfo SET $fields_query WHERE id = '$id' AND Active = 1";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_affected_rows($conn) > 0) {
            $data = [
                'status' => 200,
                'message' => 'Bank Details Updated Successfully',
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No Bank Found with the given ID or No Changes Made',
            ];
            header('HTTP/1.0 404 Not Found');
            return json_encode($data);
        }
    } else {
        $data = [
            'status' => 500,
            'message' => 'Internal Server Error',
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getCommonBankList(){

    /// Created By : Kavinda
   /// Date : 2025-08-23
   /// Description : This function is to loads Commoan Bank info to list.


    global $conn;

    $query = "SELECT * FROM BankDetails WHERE Active = 1 ORDER BY id ASC";
    $query_run = mysqli_query($conn,$query);
    
    if ($query_run){

        if(mysqli_num_rows($query_run) > 0 ){

            $res = mysqli_fetch_all($query_run,MYSQLI_ASSOC);

            $data = [

                'status'=> 200,
                'message'=> 'Bank List Fetched Sucessfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK ');
            return json_encode($data);

        }else
        {
            $data = [

                'status'=> 404,
                'message'=> 'No Bank Found',
            ];
            header('HTTP/1.0 404 No Bank Found');
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

?>