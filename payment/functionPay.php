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

function saveCashPaymentInfo($data,$userId) {

    /// Created By : Kavinda
   /// Date : 2025-09-28
   /// Description : Save the Cash payment for an invoice

    global $conn;
    
    $PMID      = mysqli_real_escape_string($conn, $data['PMID']);
    $ShopID = mysqli_real_escape_string($conn, $data['ShopID']);
    $PayDate =  mysqli_real_escape_string($conn, $data['PayDate']);
    $PayAmount = (float) mysqli_real_escape_string($conn, $data['PayAmount']);
    $CreateUser = $userId;
    $Active     = 1;

    // Remarks (optional, default null)
    $Remarks = isset($data['Remarks']) && trim($data['Remarks']) !== '' ? mysqli_real_escape_string($conn, $data['Remarks']) : null;




    // Validation
    if (empty(trim($ShopID))) {
        return error422('Shop ID is required');
    } elseif (empty(trim($PMID))) {
        return error422('Pay method is required');
    } elseif (empty(trim($PayDate))) {
        return error422('Pay Date is required');
    } elseif ($PayAmount <= 0) {
        return error422('Pay Amount is required');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {

        // Insert Pay header
        $queryHeader = "INSERT INTO PayHeader 
            (PMID, ShopID, PayDate, PayAmount,CreateBy, Active) 
            VALUES 
            ('$PMID', '$ShopID', '$PayDate', '$PayAmount','$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to insert Pay header: " . mysqli_error($conn));
        }
        
        // Get inserted header ID
        $payID = mysqli_insert_id($conn);
        if (!$payID) {
            throw new Exception("Failed to retrieve inserted Pay ID.");
        }

        // Insert into CashPay table
        $queryCashPay = "INSERT INTO CashPay 
            (PayHeaderID, PayDate, PayAmount, Remarks, CreateBy, Active) 
            VALUES 
            ('$payID', '$PayDate', '$PayAmount', " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ", '$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryCashPay)) {
            throw new Exception("Failed to insert into CashPay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Cash Pay created successfully',
            'pay_id' => $payID
        ];
        header('HTTP/1.0 201 Created');

    } catch (Exception $e) {

        var_dump($e);exit;
        // Rollback on error
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Failed to create Payment',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
    }

    // Close connection
    mysqli_close($conn);

    return json_encode($response);
}

function saveBankPaymentInfo($data,$imageInfo,$userId) {

    /// Created By : Kavinda
   /// Date : 2025-09-28
   /// Description : Save the Cash payment for an invoice

    global $conn;


    $path_db = '';
    $path_dbProf = '';

    if(isset($imageInfo['image'])){

        $img_name = $imageInfo['image']['name']; // To get file name
        $img_name_tmp = $imageInfo['image']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'front_'.time(); //New image name
        $path = "../bankSlipImg/" . $img_new . "." . $ext; //New path to move
        $path_db = "bankSlipImg/" . $img_new . "." . $ext;

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder       
        
    }
    
    $PMID      = mysqli_real_escape_string($conn, $data['PMID']);
    $ShopID = mysqli_real_escape_string($conn, $data['ShopID']);
    $PayDate =  mysqli_real_escape_string($conn, $data['PayDate']);
    $PayAmount = (float) mysqli_real_escape_string($conn, $data['PayAmount']);
    $Bid = mysqli_real_escape_string($conn, $data['Bid']);
    $ChequeDeptDate = mysqli_real_escape_string($conn, $data['ChequeDeptDate']);
    $CreateUser = $userId;
    $Active     = 1;

    // Remarks (optional, default null)
    $Remarks = isset($data['Remarks']) && trim($data['Remarks']) !== '' ? mysqli_real_escape_string($conn, $data['Remarks']) : null;




    // Validation
    if (empty(trim($ShopID))) {
        return error422('Shop ID is required');
    } elseif (empty(trim($PMID))) {
        return error422('Pay method is required');
    } elseif (empty(trim($PayDate))) {
        return error422('Pay Date is required');
    } elseif ($PayAmount <= 0) {
        return error422('Pay Amount is required');
    }
    elseif (empty(trim($Bid))) {
        return error422('Deposited Bank is required');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {

        // Insert Pay header
        $queryHeader = "INSERT INTO PayHeader 
            (PMID, ShopID, PayDate, PayAmount,CreateBy, Active) 
            VALUES 
            ('$PMID', '$ShopID', '$PayDate', '$PayAmount','$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to insert Pay header: " . mysqli_error($conn));
        }
        
        // Get inserted header ID
        $payID = mysqli_insert_id($conn);
        if (!$payID) {
            throw new Exception("Failed to retrieve inserted Pay ID.");
        }

        // Insert into CashPay table
        $queryCashPay = "INSERT INTO BankDeptInfo 
            (PayHeaderID,BankID,DepositeDate,PaySlipImagePath,DeptAmount, Remarks, CreateBy, Active) 
            VALUES 
            ('$payID','$Bid','$PayDate','$path_db','$PayAmount', " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ", '$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryCashPay)) {
            throw new Exception("Failed to insert into CashPay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Bank Pay created successfully',
            'pay_id' => $payID
        ];
        header('HTTP/1.0 201 Created');

    } catch (Exception $e) {

        var_dump($e);exit;
        // Rollback on error
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Failed to create Payment',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
    }

    // Close connection
    mysqli_close($conn);

    return json_encode($response);
}

function saveChequePaymentInfo($data,$imageInfo,$userId) {

    /// Created By : Kavinda
   /// Date : 2025-09-28
   /// Description : Save the Cheque payment for an invoice

    global $conn;

    $path_db = '';
    $path_dbProf = '';

    if(isset($imageInfo['image'])){

        $img_name = $imageInfo['image']['name']; // To get file name
        $img_name_tmp = $imageInfo['image']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'front_'.time(); //New image name
        $path = "../chequeImg/" . $img_new . "." . $ext; //New path to move
        $path_db = "chequeImg/" . $img_new . "." . $ext;

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder       
        
    }
    
    $PMID      = mysqli_real_escape_string($conn, $data['PMID']);
    $ShopID = mysqli_real_escape_string($conn, $data['ShopID']);
    $PayDate =  mysqli_real_escape_string($conn, $data['PayDate']);
    $PayAmount = (float) mysqli_real_escape_string($conn, $data['PayAmount']);
    $ChqNo = mysqli_real_escape_string($conn, $data['ChqNo']);
    $Bid = mysqli_real_escape_string($conn, $data['Bid']);
    $ChequeDeptDate = mysqli_real_escape_string($conn, $data['ChequeDeptDate']);
    $ChequeGrntDate = mysqli_real_escape_string($conn, $data['ChequeGrntDate']);
    $CDate =  mysqli_real_escape_string($conn, $data['CDate']);
    //$ChequeType = mysqli_real_escape_string($conn, $data['ChequeType']);
    $OwnBnkId = mysqli_real_escape_string($conn, $data['OwnBnkId']);
    $CreateUser = $userId;
    $Active     = 1;

    // Remarks (optional, default null)
    $Remarks = isset($data['Remarks']) && trim($data['Remarks']) !== '' ? mysqli_real_escape_string($conn, $data['Remarks']) : null;

    // Validation
    if (empty(trim($ShopID))) {
        return error422('Shop ID is required');
    } elseif (empty(trim($PMID))) {
        return error422('Pay method is required');
    } elseif (empty(trim($PayDate))) {
        return error422('Pay Date is required');
    } elseif ($PayAmount <= 0) {
        return error422('Pay Amount is required');
    }
    elseif (empty(trim($Bid))) {
        return error422('Deposited Bank is required');
    }
    elseif (empty(trim($ChqNo))) {
        return error422('Cheque No is required');
    }
    elseif (empty(trim($ChequeDeptDate))) {
        return error422('Cheque No is required');
    }

    mysqli_begin_transaction($conn);

    try {

        // Insert Pay header
        $queryHeader = "INSERT INTO PayHeader 
            (PMID, ShopID, PayDate, PayAmount,CreateBy, Active) 
            VALUES 
            ('$PMID', '$ShopID', '$PayDate', '$PayAmount','$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to insert Pay header: " . mysqli_error($conn));
        }
        
        // Get inserted header ID
        $payID = mysqli_insert_id($conn);
        if (!$payID) {
            throw new Exception("Failed to retrieve inserted Pay ID.");
        }

        // Insert into CashPay table
        $queryCashPay = "INSERT INTO ChequesDeptInfo 
            (PayHeaderID,ChqNo,ChequeGrntDate,ChequeIssuedBnkId,Remarks,DeptBnkId,ChequeDeptDate,DeptImagePath,CreateBy, Active) 
            VALUES 
            ('$payID','$ChqNo','$ChequeGrntDate','$Bid', " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ",'$OwnBnkId','$ChequeDeptDate','$path_db','$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryCashPay)) {
            throw new Exception("Failed to insert into CashPay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Cheques Pay created successfully',
            'pay_id' => $payID
        ];
        header('HTTP/1.0 201 Created');

    } catch (Exception $e) {

        var_dump($e);exit;
        // Rollback on error
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Failed to create Payment',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
    }

    // Close connection
    mysqli_close($conn);

    return json_encode($response);




}


?>