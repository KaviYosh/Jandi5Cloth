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

function getCashPaymentInfo($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-11-04
    /// Description : get the Cash Pay info By Id
    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['PHID']) || empty($designParam['PHID'])) {
            return error422('Need Payment Header ID.');
        }

        $PHID = mysqli_real_escape_string($conn, $designParam['PHID']);

        //var_dump($PHID);exit;

        $query = "SELECT pyh.PHID,pyh.PMID,pyh.ShopID,pyh.PayDate,pyh.PayAmount,pyh.CreatedDate,pm.PaymentMethod,
                    cp.PayDate as CashPayDate,cp.PaySlipImagePath,sp.shopName,sp.contact_no1
                    FROM PayHeader pyh
                    INNER JOIN PaymentMethod pm
                    ON pyh.PMID = pm.PMID
                    INNER JOIN CashPay cp
                    ON pyh.PHID = cp.PayHeaderID
                    INNER JOIN Shops sp
                    ON sp.id = pyh.ShopID
                    WHERE pyh.PHID = '$PHID' AND pyh.Active = 1;";

        $query_run = mysqli_query($conn, $query);

        //var_dump($query);exit;  
        
        if ($query_run) 
        {
            $cashPayData = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            //var_dump($cashPayData);exit;  

            $data = [
                'status'=> 200,
                'message'=> 'Cash Payment data fetched successfully',
                'data'=> $cashPayData,
            ];
            header('HTTP/1.0 200 Success');
            return json_encode($data);
        } 
        else {
            $data = [
                'status'=> 500,
                'message'=> 'Internal Server Error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode($data);
        }

        
    } 
    catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getBankPaymentInfo($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-11-04
    /// Description : get the Bank Pay info By Id
    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['PHID']) || empty($designParam['PHID'])) {
            return error422('Need Payment Header ID.');
        }

        $PHID = mysqli_real_escape_string($conn, $designParam['PHID']);

        //var_dump($PHID);exit;

        $query = "SELECT pyh.PHID,pyh.PMID,pyh.ShopID,pyh.PayDate,pm.PaymentMethod,bdi.BDID,bdi.BankID,bdi.DepositeDate,bdi.DeptAmount,bdi.PaySlipImagePath,bdi.Remarks,sp.shopName,sp.contact_no1,obi.name,obi.branch,obi.account_no
                    FROM PayHeader pyh
                    INNER JOIN PaymentMethod pm
                    ON pyh.PMID = pm.PMID
                    INNER JOIN BankDeptInfo bdi
                    ON pyh.PHID = bdi.PayHeaderID
                    INNER JOIN Shops sp
                    ON sp.id = pyh.ShopID
                    INNER JOIN OwnerBankInfo obi
                    ON bdi.BankID = obi.id
                    WHERE pyh.PHID = '$PHID'AND pyh.Active=1";

        $query_run = mysqli_query($conn, $query);

        //var_dump($query);exit;  
        
        if ($query_run) 
        {
            $cashPayData = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            //var_dump($cashPayData);exit;  

            $data = [
                'status'=> 200,
                'message'=> 'Bank Payment data fetched successfully',
                'data'=> $cashPayData,
            ];
            header('HTTP/1.0 200 Success');
            return json_encode($data);
        } 
        else {
            $data = [
                'status'=> 500,
                'message'=> 'Internal Server Error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode($data);
        }

        
    } 
    catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getChquePaymentInfo($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-11-04
    /// Description : get the Cheque info By Id
    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['PHID']) || empty($designParam['PHID'])) {
            return error422('Need Payment Header ID.');
        }

        $PHID = mysqli_real_escape_string($conn, $designParam['PHID']);

        //var_dump($PHID);exit;

        $query = "SELECT pyh.PHID, pyh.PMID, pyh.ShopID, pyh.PayDate,
       pm.PaymentMethod, sp.shopName, sp.contact_no1,
       obi.name AS OwnerBankName,
       obi.branch AS OwneBnkBranch,
       obi.account_no AS OwnerAccountNo,
       cdi.CheqDId, cdi.ChqNo, cdi.ChequeAmount,
       cdi.ChequeDate, cdi.ChequeIssuedBnkId,
       cdi.DeptBnkId, cdi.ChequeDeptDate,
       cdi.DeptImagePath, cdi.Remarks,
       bd.BankName AS ChequesIssedBankName
        FROM PayHeader pyh
        INNER JOIN PaymentMethod pm ON pyh.PMID = pm.PMID
        INNER JOIN ChequesDeptInfo cdi ON pyh.PHID = cdi.PayHeaderID
        INNER JOIN Shops sp ON sp.id = pyh.ShopID
        INNER JOIN OwnerBankInfo obi ON cdi.DeptBnkId = obi.id
        INNER JOIN BankDetails bd ON cdi.ChequeIssuedBnkId = bd.Bid
        WHERE pyh.PHID = '$PHID';";

        $query_run = mysqli_query($conn, $query);

        if ($query_run) 
        {
            $cashPayData = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            $data = [
                'status'=> 200,
                'message'=> 'Cheque Info fetched successfully',
                'data'=> $cashPayData,
            ];
            header('HTTP/1.0 200 Success');
            return json_encode($data);
        } 
        else {
            $data = [
                'status'=> 500,
                'message'=> 'Internal Server Error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            return json_encode($data);
        }
    } 
    catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

?>