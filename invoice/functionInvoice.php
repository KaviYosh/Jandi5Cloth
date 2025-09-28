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


function saveInvoiceInfo($data,$userId) {
    global $conn;
    
    // Generate Invoice Number
    $invoiceNo = createInvoiceNo();
    
    // Extract and sanitize header data
    $InvoiceNo   = $invoiceNo;
    $ShopID      = mysqli_real_escape_string($conn, $data['ShopID']);
    $InvoiceDate = mysqli_real_escape_string($conn, $data['InvoiceDate']);
    $ItemsTotalAmount = (float) mysqli_real_escape_string($conn, $data['ItemsTotalAmount']);

    // Delivery cost (optional, default 0.00)
    $DeliveryCost = isset($data['DeliveryCost']) && trim($data['DeliveryCost']) !== '' 
        ? (float) mysqli_real_escape_string($conn, $data['DeliveryCost']) 
        : 0.00;

       
    // Final total
    $TotSellingDeliveCost = $ItemsTotalAmount + $DeliveryCost;

    $CreateUser = $userId;
    $Active     = 1;

    // Validation
    if (empty(trim($InvoiceNo))) {
        return error422('Invoice No is required');
    } elseif (empty(trim($ShopID))) {
        return error422('Shop id is required');
    } elseif (empty(trim($InvoiceDate))) {
        return error422('Invoice Date is required');
    } elseif ($ItemsTotalAmount <= 0) {
        return error422('Items Total Amount is required');
    }

    // Start transaction
    mysqli_begin_transaction($conn);

    try {

        

        // Insert invoice header
        $queryHeader = "INSERT INTO InvoiceHeader 
            (InvoiceNo, ShopID, InvoiceDate, ItemsTotalAmount, DeliveryCost, TotSellingDeliveCost, CreateBy, Active) 
            VALUES 
            ('$InvoiceNo', '$ShopID', '$InvoiceDate', '$ItemsTotalAmount', '$DeliveryCost', '$TotSellingDeliveCost', '$CreateUser', '$Active')";

        if (!mysqli_query($conn, $queryHeader)) {
            throw new Exception("Failed to insert invoice header: " . mysqli_error($conn));
        }
        
        // Get inserted header ID
        $invoiceHedID = mysqli_insert_id($conn);

       

        // Insert invoice details (expects $data['Details'] as array of line items)
        if (!empty($data['Details']) && is_array($data['Details'])) {

            foreach ($data['Details'] as $item) {
                
                $DesignID       = mysqli_real_escape_string($conn, $item['DesignID']);
                $Qty            = (int) $item['Qty'];
                $UnitPrice      = (float) $item['UnitPrice'];
                $SellingPrice   = (float) $item['SellingPrice'];

                $TotalUnitCost   = $Qty * $UnitPrice;
                $TotalSellingCost = $Qty * $SellingPrice;

                $queryDetail = "INSERT INTO InvoiceDetails 
                    (InvoiceHedID, DesignID, Qty, UnitPrice, SellingPrice, TotalUnitCost, TotalSelingCost, Active, CreateBy) 
                    VALUES 
                    ('$invoiceHedID', '$DesignID', '$Qty', '$UnitPrice', '$SellingPrice', '$TotalUnitCost', '$TotalSellingCost', '$Active', '$CreateUser')";

                if (!mysqli_query($conn, $queryDetail)) {
                    throw new Exception("Failed to insert invoice detail: " . mysqli_error($conn));
                }
            }
        } else {
            throw new Exception("No invoice details provided");
        }

        // Commit transaction
        mysqli_commit($conn);

        $response = [
            'status' => 201,
            'message' => 'Invoice created successfully',
            'invoice_id' => $invoiceHedID
        ];
        header('HTTP/1.0 201 Created');

    } catch (Exception $e) {

        var_dump($e);exit;
        // Rollback on error
        mysqli_rollback($conn);
        $response = [
            'status' => 500,
            'message' => 'Failed to create invoice',
            'error' => $e->getMessage()
        ];
        header('HTTP/1.0 500 Internal Server Error');
    }

    // Close connection
    mysqli_close($conn);

    return json_encode($response);
}

function createInvoiceNo() {
    global $conn;

    // Get current year and month
    $yearMonth = date("Y-m");

    // Count how many invoices exist for this year-month
    $query = "
        SELECT COUNT(*) AS cnt 
        FROM InvoiceHeader 
        WHERE DATE_FORMAT(CreatedDate, '%Y-%m') = '$yearMonth'
    ";
    $result = mysqli_query($conn, $query);

    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $seq = $row['cnt'] + 1; // Next sequence number
    } else {
        $seq = 1; // Default to 1 if query fails
    }

    // Format invoice number: INV-2025-09-0001
    $invoiceNo = "INV-" . $yearMonth . "-" . str_pad($seq, 4, "0", STR_PAD_LEFT);

    return $invoiceNo;
}

function getInvoiceListById($shopParam){
    global $conn;

    if (isset($shopParam['ShopID']) && !empty($shopParam['ShopID'])) {
        $ShopID = mysqli_real_escape_string($conn, $shopParam['ShopID']);

        $query = "SELECT IH.IHID, IH.InvoiceNo, IH.ShopID, IH.InvoiceDate, IH.ItemsTotalAmount, IH.DeliveryCost, IH.TotSellingDeliveCost, 
                  IH.Active, IH.CreatedDate 
                  FROM InvoiceHeader IH 
                  WHERE IH.Active = 1 AND IH.ShopID = '$ShopID'";
    }   
    else if (isset($shopParam['IHID']) && !empty($shopParam['IHID'])) {

        $IHID = mysqli_real_escape_string($conn, $shopParam['IHID']);

        $query = "SELECT IH.IHID, IH.InvoiceNo, IH.ShopID, IH.InvoiceDate, IH.ItemsTotalAmount, IH.DeliveryCost, IH.TotSellingDeliveCost, 
                  IH.Active, IH.CreatedDate 
                  FROM InvoiceHeader IH 
                  WHERE IH.Active = 1 AND IH.IHID = '$IHID'";
    }   
    else {
        return error422('Invalid parameters provided.');
    }
    
    $query_run = mysqli_query($conn, $query);
    

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            //var_dump($res);exit;


            $data = [
                'status'=> 200,
                'message'=> 'Invoice List Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No invoice Found for the given Shop ID',
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

function getInvoiceDetail($invoiceParam){
    
    global $conn;

    if (!isset($invoiceParam) || !is_array($invoiceParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($invoiceParam['IHID']) || empty($invoiceParam['IHID'])) {
        return error422('Send Invoice Header Id');
    }


    $IHID = mysqli_real_escape_string($conn, $invoiceParam['IHID']);

    $query = "SELECT *
              FROM InvoiceDetails IND 
              WHERE IND.Active = 1 AND IND.InvoiceHedID = '$IHID' ORDER BY IND.INDID ASC";
    $query_run = mysqli_query($conn, $query);
    

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);

            //var_dump($res);exit;


            $data = [
                'status'=> 200,
                'message'=> 'Invoice Details List Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No invoice Details Found for the given Shop ID',
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

?>