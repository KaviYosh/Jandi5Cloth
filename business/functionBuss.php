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

function getShopTotalCrdSel($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-10-04
    /// Description : get the total credit amount related to a shop

    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['ShopID']) || empty($designParam['ShopID'])) {
            return error422('Enter your shop ID');
        }

        $ShopID = mysqli_real_escape_string($conn, $designParam['ShopID']);

        $queryCredit = "SELECT Sum(TotSellingDeliveCost) As TotalCredit FROM InvoiceHeader WHERE Active = 1 AND ShopID = '$ShopID'";
        $queryDebit = "SELECT Sum(PayAmount) As TotalDebit FROM PayHeader WHERE Active = 1 AND ShopID = '$ShopID'";

        $queryCreditRun = mysqli_query($conn, $queryCredit);
        $queryDebitRun = mysqli_query($conn, $queryDebit);

        if ($queryCreditRun && $queryDebitRun) {
            $creditResult = mysqli_fetch_assoc($queryCreditRun);
            $debitResult = mysqli_fetch_assoc($queryDebitRun);

            $totalCredit = $creditResult['TotalCredit'] ?? 0;
            $totalDebit = $debitResult['TotalDebit'] ?? 0;

            $finalBalance = $totalCredit - $totalDebit;

            $data = [
            'status' => 200,
            'message' => 'Final balance calculated successfully',
            'data' => [
                'TotalCredit' => $totalCredit,
                'TotalDebit' => $totalDebit,
                'FinalBalance' => $finalBalance
            ]
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            throw new Exception('Database query failed.');
        }
        
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getShopTotalDebitSel($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-10-04
    /// Description : get the total Debit amount related to a shop

    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['ShopID']) || empty($designParam['ShopID'])) {
            return error422('Enter your shop ID');
        }

        $ShopID = mysqli_real_escape_string($conn, $designParam['ShopID']);

        $query = "SELECT Sum(PayAmount) As TotalDebit FROM PayHeader WHERE Active = 1 AND ShopID = '$ShopID'";
        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Total Debit amount Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Debit Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getShopTrnsHistory($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-10-04
    /// Description : get the shop transaction history.

    global $conn;

    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['ShopID']) || empty($designParam['ShopID'])) {
            return error422('Enter your shop ID');
        }

        $ShopID = mysqli_real_escape_string($conn, $designParam['ShopID']);

        $query = "
            SELECT 
                IH.InvoiceNo AS TransactionNo,
                IH.InvoiceNo,
                IH.InvoiceDate AS PayDate,
                NULL AS DebitAmount,
                IH.TotSellingDeliveCost AS CreditAmount,
                NULL AS PaymentMethod
            FROM InvoiceHeader IH
            WHERE IH.Active = 1 AND IH.ShopID = '$ShopID'

            UNION ALL

            SELECT 
                PH.PayRefNo AS TransactionNo,
                NULL AS InvoiceNo,
                PH.PayDate,
                PH.PayAmount AS DebitAmount,
                NULL AS CreditAmount,
                PM.PaymentMethod
            FROM PayHeader PH
            INNER JOIN PaymentMethod PM
            ON PH.PMID = PM.PMID
            WHERE PH.Active = 1 AND PH.ShopID = '$ShopID'
        ";

        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Transaction history fetched successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No transaction history found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getPaymentDetail($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-10-04
    /// Description : get the shop Payment details.

    global $conn;
    //var_dump(1234);exit;
    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['ShopID']) || empty($designParam['ShopID'])) {
            return error422('Enter your shop ID');
        }

        $ShopID = mysqli_real_escape_string($conn, $designParam['ShopID']);

        $query = "SELECT PH.PHID,PH.PayDate,PH.PayAmount,PH.CreatedDate,PM.PaymentMethod FROM PayHeader PH 
           INNER JOIN PaymentMethod PM ON PH.PMID = PM.PMID WHERE PH.Active = 1 AND PH.ShopID = '$ShopID'";
        
        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Payment details Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Payment Details Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        var_dump($e);exit;
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getAllInvoiceInfo() {

    /// Created By : Kavinda
    /// Date : 2025-10-17
    /// Description : get all invoice information related to a sell

    global $conn;

    try {
        
        $query = "SELECT IH.IHID,IH.ShopID,IH.InvoiceNo,IH.InvoiceDate,IH.ItemsTotalAmount,IH.DeliveryCost,IH.TotSellingDeliveCost,sh.shopName
                    FROM `InvoiceHeader` IH 
                    INNER JOIN Shops sh
                    ON IH.ShopID = sh.id WHERE IH.Active = 1 ORDER BY IH.IHID ASC";
        
        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Invoice information Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Invoice Information Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }

}

function getAllPaymentInfo() {

    /// Created By : Kavinda
    /// Date : 2025-10-17
    /// Description : get all payment information related to a sell

    global $conn;

    try {
        
        $query = "SELECT 
                    PH.PHID,
                    PH.PMID,
                    PH.ShopID,
                    PH.PayDate,
                    PH.PayAmount,
                    SH.ShopName,
                    PM.PaymentMethod
                FROM PayHeader PH
                INNER JOIN Shops SH
                    ON PH.ShopID = SH.ID
                INNER JOIN PaymentMethod PM
                    ON PH.PMID = PM.PMID
                WHERE PH.Active = 1
                ORDER BY PH.PHID ASC";
        
        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Payment information Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Payment Information Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }

}

function getChqDetail($designParam) {

    /// Created By : Kavinda
    /// Date : 2025-10-18
    /// Description : get the shop chq details.

    global $conn;
    //var_dump(1234);exit;
    try {
        if (!isset($designParam) || !is_array($designParam)) {
            return error422('Invalid input data format.');
        }

        if (!isset($designParam['ShopID']) || empty($designParam['ShopID'])) {
            return error422('Enter your shop ID');
        }

        $ShopID = mysqli_real_escape_string($conn, $designParam['ShopID']);

        // $query = "SELECT * FROM PayHeader PH
        //         INNER JOIN ChequesDeptInfo CDI
        //         ON PH.PHID = CDI.PayHeaderID
        //         INNER JOIN BankDetails BD
        //         ON CDI.ChequeIssuedBnkId  = BD.Bid
        //         INNER JOIN OwnerBankInfo OBI
        //         ON CDI.DeptBnkId =  OBI.id
        //         WHERE (PH.Active = 1 || PH.Active = 2) AND PH.ShopID = '$ShopID' AND PH.PMID = 52";


        $query = "SELECT pyh.PHID,pyh.PayAmount,pyh.PMID, pyh.ShopID, pyh.PayDate,pyh.Active AS payHeaderStatus,
        pm.PaymentMethod, sp.shopName, sp.contact_no1,
        obi.name AS OwnerBankName,
        obi.branch AS OwneBnkBranch,
        obi.account_no AS OwnerAccountNo,
        cdi.CheqDId, cdi.ChqNo, cdi.ChequeAmount,
        cdi.ChequeDate, cdi.ChequeIssuedBnkId,
        cdi.DeptBnkId, cdi.ChequeDeptDate,
        cdi.DeptImagePath, cdi.Remarks,
        cdi.Active AS ChqDtlStatus,
        bd.BankName AS ChequesIssedBankName
        FROM PayHeader pyh
        INNER JOIN PaymentMethod pm ON pyh.PMID = pm.PMID
        INNER JOIN ChequesDeptInfo cdi ON pyh.PHID = cdi.PayHeaderID
        INNER JOIN Shops sp ON sp.id = pyh.ShopID
        INNER JOIN OwnerBankInfo obi ON cdi.DeptBnkId = obi.id
        INNER JOIN BankDetails bd ON cdi.ChequeIssuedBnkId = bd.Bid
        WHERE pyh.Active != 0 AND pyh.ShopID = '$ShopID' AND pyh.PMID = 52";

        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Cheques details Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Cheques Details Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        var_dump($e);exit;
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getAllChqDetails() {

    /// Created By : Kavinda
    /// Date : 2025-10-18
    /// Description : get the shop chq details.

    global $conn;
    //var_dump(1234);exit;
    try {

        // $query = "SELECT * FROM PayHeader PH
        //         INNER JOIN ChequesDeptInfo CDI
        //         ON PH.PHID = CDI.PayHeaderID
        //         INNER JOIN BankDetails BD
        //         ON CDI.ChequeIssuedBnkId  = BD.Bid
        //         INNER JOIN OwnerBankInfo OBI
        //         ON CDI.DeptBnkId =  OBI.id
        //         WHERE (PH.Active = 1 || PH.Active = 2) AND PH.PMID = 52"; /// 52 mean cheque payment method

        $query = "SELECT pyh.PHID,pyh.PayAmount,pyh.PMID, pyh.ShopID, pyh.PayDate,pyh.Active AS payHeaderStatus,
        pm.PaymentMethod, sp.shopName, sp.contact_no1,
        obi.name AS OwnerBankName,
        obi.branch AS OwneBnkBranch,
        obi.account_no AS OwnerAccountNo,
        cdi.CheqDId, cdi.ChqNo, cdi.ChequeAmount,
        cdi.ChequeDate, cdi.ChequeIssuedBnkId,
        cdi.DeptBnkId, cdi.ChequeDeptDate,
        cdi.DeptImagePath, cdi.Remarks,
        cdi.Active AS ChqDtlStatus,
        bd.BankName AS ChequesIssedBankName
        FROM PayHeader pyh
        INNER JOIN PaymentMethod pm ON pyh.PMID = pm.PMID
        INNER JOIN ChequesDeptInfo cdi ON pyh.PHID = cdi.PayHeaderID
        INNER JOIN Shops sp ON sp.id = pyh.ShopID
        INNER JOIN OwnerBankInfo obi ON cdi.DeptBnkId = obi.id
        INNER JOIN BankDetails bd ON cdi.ChequeIssuedBnkId = bd.Bid
        WHERE pyh.Active != 0 AND pyh.PMID = 52";



        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Cheques details Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Cheques Details Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        var_dump($e);exit;
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getAllTotalCredit(){

    /// Created By : Kavinda
    /// Date : 2025-10-22
    /// Description : get the Total credit details

    global $conn;

    try {
       

        $queryCredit = "SELECT Sum(TotSellingDeliveCost) As TotalCredit FROM InvoiceHeader WHERE Active = 1";
        $queryDebit = "SELECT Sum(PayAmount) As TotalDebit FROM PayHeader WHERE Active = 1";

        $queryCreditRun = mysqli_query($conn, $queryCredit);
        $queryDebitRun = mysqli_query($conn, $queryDebit);

        if ($queryCreditRun && $queryDebitRun) {
            $creditResult = mysqli_fetch_assoc($queryCreditRun);
            $debitResult = mysqli_fetch_assoc($queryDebitRun);

            $totalCredit = $creditResult['TotalCredit'] ?? 0;
            $totalDebit = $debitResult['TotalDebit'] ?? 0;

            $finalBalance = $totalCredit - $totalDebit;

            $data = [
            'status' => 200,
            'message' => 'Final balance calculated successfully',
            'data' => [
                'TotalCredit' => $totalCredit,
                'TotalDebit' => $totalDebit,
                'FinalBalance' => $finalBalance
            ]
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            throw new Exception('Database query failed.');
        }
        
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

function getAllTotalDebit() {

    /// Created By : Kavinda
    /// Date : 2025-10-04
    /// Description : get the total Debit amount related to a shop

    global $conn;

    try {

        $query = "SELECT Sum(PayAmount) As TotalDebit FROM PayHeader WHERE Active = 1";
        $query_run = mysqli_query($conn, $query);

        if ($query_run) {
            if (mysqli_num_rows($query_run) > 0) {
                $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
                $data = [
                    'status'=> 200,
                    'message'=> 'Total Debit amount Fetched Successfully',
                    'data' => $res
                ];
                header('HTTP/1.0 200 OK');
                return json_encode($data);
            } else {
                $data = [
                    'status'=> 404,
                    'message'=> 'No Debit Found',
                ];
                header('HTTP/1.0 404 Not Found');
                return json_encode($data);
            }
        } else {
            throw new Exception('Database query failed.');
        }
    } catch (Exception $e) {
        $data = [
            'status'=> 500,
            'message'=> 'Internal Server Error: ' . $e->getMessage(),
        ];
        header('HTTP/1.0 500 Internal Server Error');
        return json_encode($data);
    }
}

?>
