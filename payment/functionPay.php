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
    
    //var_dump($data);exit;

    $PMID      = mysqli_real_escape_string($conn, $data['PMID']);
    $ShopID = mysqli_real_escape_string($conn, $data['ShopID']);
    $PayDate =  mysqli_real_escape_string($conn, $data['PayDate']);
    $PayAmount = (float) mysqli_real_escape_string($conn, $data['PayAmount']);
    $ChqNo = mysqli_real_escape_string($conn, $data['ChqNo']);
    $Bid = mysqli_real_escape_string($conn, $data['Bid']);
    $ChequeDate = mysqli_real_escape_string($conn, $data['ChequeDate']);
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
    elseif (empty(trim($ChequeDate))) {

        return error422('Cheque Grant Date is required');
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
            (PayHeaderID,ChqNo,ChequeAmount,ChequeDate,ChequeIssuedBnkId,Remarks,DeptBnkId,DeptImagePath,CreateBy,Active) 
            VALUES 
            ('$payID','$ChqNo','$PayAmount','$ChequeDate','$Bid', " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ",'$OwnBnkId','$path_db','$CreateUser','$Active')";

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

function deleteBankInfo($data, $userId) {

    /// Created By : Kavinda
    /// Date : 2025-11-06
    /// Description : Delete the bank Pay Info

    global $conn;

    if (!isset($data['PayHeaderID'])) {
        return error422('PayHeaderID not found in request');
    } elseif ($data['PayHeaderID'] == null) {
        return error422('PayHeaderID is required');
    }

    $id = mysqli_real_escape_string($conn, $data['PayHeaderID']);

    if (empty(trim($id))) {
        return error422('PayHeaderID cannot be empty');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Update PayHeader table
        $queryPayHeader = "UPDATE PayHeader 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PHID = '$id'";

        if (!mysqli_query($conn, $queryPayHeader)) {
            throw new Exception("Failed to update PayHeader: " . mysqli_error($conn));
        }

        // Update BankDeptInfo table
        $queryBankDeptInfo = "UPDATE BankDeptInfo 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PayHeaderID = '$id'";

        if (!mysqli_query($conn, $queryBankDeptInfo)) {
            throw new Exception("Failed to update BankDeptInfo: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 200,
            'message' => 'Bank Pay Info deleted successfully',
        ];
        header('HTTP/1.0 200 Success');
        return json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        $response = [
            'status' => 500,
            'message' => 'Failed to delete Bank Pay Info',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($response);
    }
}

function deleteCashPay($data, $userId) {

    /// Created By : Kavinda
    /// Date : 2025-11-06
    /// Description : Delete the Cash Pay Info

    global $conn;

    if (!isset($data['PayHeaderID'])) {
        return error422('PayHeaderID not found in request');
    } elseif ($data['PayHeaderID'] == null) {
        return error422('PayHeaderID is required');
    }

    $id = mysqli_real_escape_string($conn, $data['PayHeaderID']);

    if (empty(trim($id))) {
        return error422('PayHeaderID cannot be empty');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Update PayHeader table
        $queryPayHeader = "UPDATE PayHeader 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PHID = '$id'";

        if (!mysqli_query($conn, $queryPayHeader)) {
            throw new Exception("Failed to update PayHeader: " . mysqli_error($conn));
        }

        // Update BankDeptInfo table
        $queryBankDeptInfo = "UPDATE CashPay 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PayHeaderID = '$id'";

        if (!mysqli_query($conn, $queryBankDeptInfo)) {
            throw new Exception("Failed to update Cash pay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 200,
            'message' => 'Cash payment Info deleted successfully',
        ];
        header('HTTP/1.0 200 Success');
        return json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        $response = [
            'status' => 500,
            'message' => 'Failed to delete Cash Pay Info',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($response);
    }
}

function deleteChqPay($data, $userId) {

    /// Created By : Kavinda
    /// Date : 2025-11-06
    /// Description : Delete the chque Pay Info

    global $conn;

    if (!isset($data['PayHeaderID'])) {
        return error422('PayHeaderID not found in request');
    } elseif ($data['PayHeaderID'] == null) {
        return error422('PayHeaderID is required');
    }

    $id = mysqli_real_escape_string($conn, $data['PayHeaderID']);

    if (empty(trim($id))) {
        return error422('PayHeaderID cannot be empty');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Update PayHeader table
        $queryPayHeader = "UPDATE PayHeader 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PHID = '$id'";

        if (!mysqli_query($conn, $queryPayHeader)) {
            throw new Exception("Failed to update PayHeader: " . mysqli_error($conn));
        }

        // Update BankDeptInfo table
        $queryBankDeptInfo = "UPDATE ChequesDeptInfo 
            SET 
                Active = 0,
                ModifiedBy = '$userId',
                ModifiedDate = NOW()
            WHERE 
                PayHeaderID = '$id'";

        if (!mysqli_query($conn, $queryBankDeptInfo)) {
            throw new Exception("Failed to update Cash pay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 200,
            'message' => 'Cheques payment Info deleted successfully',
        ];
        header('HTTP/1.0 200 Success');
        return json_encode($response);

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);

        $response = [
            'status' => 500,
            'message' => 'Failed to delete Cheques Pay Info',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($response);
    }
}

function updateCashPayInfo($data,$userId)
{
    /// Created By : Kavinda
    /// Date : 2025-11-06
    /// Description : Update the Cash Pay Info

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

        // Update Pay header
        $queryHeader = "UPDATE PayHeader 
            SET 
            PMID = '$PMID',
            ShopID = '$ShopID',
            PayDate = '$PayDate',
            PayAmount = '$PayAmount',
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()
            WHERE 
            PHID = '$data[PayHeaderID]'";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to update Pay header: " . mysqli_error($conn));
        }

        // Update CashPay table
        $queryCashPay = "UPDATE CashPay 
            SET 
            PayDate = '$PayDate',
            PayAmount = '$PayAmount',
            Remarks = " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ",
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()
            WHERE 
            PayHeaderID = '$data[PayHeaderID]'";

        if (!mysqli_query($conn, $queryCashPay)) {
            throw new Exception("Failed to update CashPay: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Cash Pay Update successfully',
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

function updateBankPayInfo($data,$imageInfo,$userId) {

    /// Created By : Kavinda
   /// Date : 2025-11-07
   /// Description : update Bank payment for an invoice

    global $conn;


    $path_db = '';
    $path_dbProf = '';
    //var_dump($data);exit;

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
    $PHID = mysqli_real_escape_string($conn, $data['PHID']);
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
        // Update Pay header
        $queryHeader = "UPDATE PayHeader 
            SET 
            PMID = '$PMID',
            ShopID = '$ShopID',
            PayDate = '$PayDate',
            PayAmount = '$PayAmount',
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()
            WHERE 
            PHID = '$PHID'";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to update Pay header: " . mysqli_error($conn));
        }

        // Update BankDeptInfo table
        $queryBankDeptInfo = "UPDATE BankDeptInfo 
            SET 
            BankID = '$Bid',
            DepositeDate = '$PayDate',
            DeptAmount = '$PayAmount',
            Remarks = " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ",
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()";

        // Only update PaySlipImagePath if $path_db is not empty
        if (!empty($path_db)) {
            $queryBankDeptInfo .= ", PaySlipImagePath = '$path_db'";
        }

        $queryBankDeptInfo .= " WHERE PayHeaderID = '$PHID'";

        if (!mysqli_query($conn, $queryBankDeptInfo)) {
            throw new Exception("Failed to update BankDeptInfo: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Bank Pay update successfully',
            'pay_id' => $payID
        ];
        header('HTTP/1.0 201 Created');

    } catch (Exception $e) {

        var_dump($e);exit;
        // Rollback on error
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Failed to update Payment',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
    }

    // Close connection
    mysqli_close($conn);

    return json_encode($response);
}

function updateCheqPayInfo($data,$imageInfo,$userId) {

    /// Created By : Kavinda
   /// Date : 2025-09-28
   /// Description : update the Cheque payment for an invoice

    global $conn;

    $path_db = '';
    $path_dbProf = '';

    if(isset($imageInfo['image']) && !empty($imageInfo['image']['name'])){

        $img_name = $imageInfo['image']['name']; // To get file name
        $img_name_tmp = $imageInfo['image']['tmp_name']; // To get file name temporary location

        $ext = pathinfo($img_name, PATHINFO_EXTENSION);
        $img_new = 'front_'.time(); //New image name
        $path = "../chequeImg/" . $img_new . "." . $ext; //New path to move
        $path_db = "chequeImg/" . $img_new . "." . $ext;

        move_uploaded_file($img_name_tmp, $path); // To move the image to user_images folder       
    } else {
        //$path_db = null; // No value assigned if image is null
    }
    
    //var_dump($data);exit;

    $PMID      = mysqli_real_escape_string($conn, $data['PMID']);
    $ShopID = mysqli_real_escape_string($conn, $data['ShopID']);
    $PayDate =  mysqli_real_escape_string($conn, $data['PayDate']);
    $PayAmount = (float) mysqli_real_escape_string($conn, $data['PayAmount']);
    $ChqNo = mysqli_real_escape_string($conn, $data['ChqNo']);
    $Bid = mysqli_real_escape_string($conn, $data['Bid']);
    $ChequeDate = mysqli_real_escape_string($conn, $data['ChequeDate']);
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
    elseif (empty(trim($ChequeDate))) {

        return error422('Cheque Grant Date is required');
    }

    mysqli_begin_transaction($conn);

    try {
        // Update Pay header
        $queryHeader = "UPDATE PayHeader 
            SET 
            PMID = '$PMID',
            ShopID = '$ShopID',
            PayDate = '$PayDate',
            PayAmount = '$PayAmount',
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()
            WHERE 
            PHID = '$data[PayHeaderID]'";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to update Pay header: " . mysqli_error($conn));
        }
        // Update ChequesDeptInfo table
        $queryChequePay = "UPDATE ChequesDeptInfo 
            SET 
            ChqNo = '$ChqNo',
            ChequeAmount = '$PayAmount',
            ChequeDate = '$ChequeDate',
            ChequeIssuedBnkId = '$Bid',
            Remarks = " . ($Remarks !== null ? "'$Remarks'" : "NULL") . ",
            DeptBnkId = '$OwnBnkId',
            ModifiedBy = '$CreateUser',
            ModifiedDate = NOW()";

        // Only update DeptImagePath if $path_db is not empty
        if (!empty($path_db)) {
            $queryChequePay .= ", DeptImagePath = '$path_db'";
        }

        $queryChequePay .= " WHERE PayHeaderID = '$data[PayHeaderID]'";

        if (!mysqli_query($conn, $queryChequePay)) {
            throw new Exception("Failed to update ChequesDeptInfo: " . mysqli_error($conn));
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Cheques Pay updated successfully',
            'pay_id' => $data['PayHeaderID']
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