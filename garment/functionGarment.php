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

function saveGarment($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to save shop details 

    global $conn;
    
    $GarmentName=  mysqli_real_escape_string($conn,$shopInput['GarmentName']);
    $Town=  mysqli_real_escape_string($conn,$shopInput['Town']);
    $Address=  mysqli_real_escape_string($conn,$shopInput['Address']);
    $ContactNo1= mysqli_real_escape_string($conn,$shopInput['ContactNo1']);
    $ContactNo2=  mysqli_real_escape_string($conn,$shopInput['ContactNo2']);
    //$ContactNo2=  mysqli_real_escape_string($conn,$userId);
    $Active = 1;

   

    if(empty(trim($GarmentName)))
    {
        return error422('Enter your Garment Name');
    }
    elseif(empty(trim($Town)))
    {
        return error422('Enter Garment town');
    }
    elseif(empty(trim($Address)))
    {
        return error422('Enter Garment address');
    }
    elseif(empty(trim($ContactNo1)))
    {
        return error422('Enter contact No');
    }  
    else
    {
        $query = "INSERT INTO GarmentInfo (GarmentName, Town, Address, ContactNo1, ContactNo2, CreateUser, Active) 
              VALUES ('$GarmentName', '$Town', '$Address', '$ContactNo1', '$ContactNo2', '$userId', '$Active')";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $data = [
            'status' => 200,
            'message' => 'Garment saved successfully',
            ];
            header('HTTP/1.0 200 Success');
            echo json_encode($data);
        } else {
            $data = [
            'status' => 500,
            'message' => 'Internal server error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode($data);
        }
        
    }
    // Close the database connection
    $conn->close();
}

function deleteGarment($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to soft delete garment details 

    global $conn;

    $GarmentId = mysqli_real_escape_string($conn, $shopInput['GarmentId']);

    if (empty(trim($GarmentId))) {
        return error422('Garment ID is required');
    } else {
        $query = "UPDATE GarmentInfo SET Active = 0, ModifiedBy = '$userId', ModifiedDate = NOW() WHERE GID = '$GarmentId'";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $data = [
                'status' => 200,
                'message' => 'Garment deleted successfully',
            ];
            header('HTTP/1.0 200 Success');
            echo json_encode($data);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Internal server error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode($data);
        }
    }
    // Close the database connection
    $conn->close();
}

function updateGarment($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to update garment details 

    global $conn;

    $GarmentId = mysqli_real_escape_string($conn, $shopInput['GarmentId']);
    $GarmentName=  mysqli_real_escape_string($conn,$shopInput['GarmentName']);
    $Town=  mysqli_real_escape_string($conn,$shopInput['Town']);
    $Address=  mysqli_real_escape_string($conn,$shopInput['Address']);
    $ContactNo1= mysqli_real_escape_string($conn,$shopInput['ContactNo1']);
    $ContactNo2=  mysqli_real_escape_string($conn,$shopInput['ContactNo2']);

    if (empty(trim($GarmentId))) {
        return error422('Garment ID is required');
    } elseif (empty(trim($GarmentName))) {
        return error422('Enter your Garment Name');
    } elseif (empty(trim($Town))) {
        return error422('Enter Garment town');
    } elseif (empty(trim($Address))) {
        return error422('Enter Garment address');
    } elseif (empty(trim($ContactNo1))) {
        return error422('Enter contact No');
    } else {
        $query = "UPDATE GarmentInfo SET 
                GarmentName = '$GarmentName', Town = '$Town', Address = '$Address', ContactNo1 = '$ContactNo1', ContactNo2 = '$ContactNo2', ModifiedBy = '$userId', ModifiedDate = NOW() WHERE GID = '$GarmentId'";

        $result = mysqli_query($conn, $query);

        if ($result) {
            $data = [
                'status' => 200,
                'message' => 'Garment updated successfully',
            ];
            header('HTTP/1.0 200 Success');
            echo json_encode($data);
        } else {
            $data = [
                'status' => 500,
                'message' => 'Internal server error',
            ];
            header('HTTP/1.0 500 Internal Server Error');
            echo json_encode($data);
        }
    }
    // Close the database connection
    $conn->close();
}

function getGarmentInfo($shopInput = []){

    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to get garment details 

    global $conn;

    $query = "SELECT * FROM GarmentInfo WHERE Active = 1";

    if (isset($shopInput['GarmentId'])) {
        $GarmentId = mysqli_real_escape_string($conn, $shopInput['GarmentId']);
        $query .= " AND GID = '$GarmentId'";
    }

    $result = mysqli_query($conn, $query);

    //var_dump($query);exit;

    if ($result) {
        $garments = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $garments[] = $row;
        }
        header('HTTP/1.0 200 Success');
        echo json_encode([
            'status' => 200,
            'data' => $garments,
        ]);
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo json_encode([
            'status' => 500,
            'message' => 'Internal server error',
        ]);
    }
    
    // Close the database connection
    $conn->close();
}

function saveGarmentProduction($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2026-05-13
    /// Description : This function is used to save Garment ready design set to databased 

    global $conn;

     // Generate Invoice Number
    $GartInvoiceNo = createInvoiceNo();
    
    $GartShopId = mysqli_real_escape_string($conn, $shopInput['GartShopId'] ?? '');
    $DesignID = mysqli_real_escape_string($conn, $shopInput['DesignID'] ?? '');
    $GartInvoiceDate = mysqli_real_escape_string($conn, $shopInput['GartInvoiceDate'] ?? '');
    $GartSendQty = mysqli_real_escape_string($conn, $shopInput['GartSendQty'] ?? '');
    $GartUnitPrice = mysqli_real_escape_string($conn, $shopInput['GartUnitPrice'] ?? '');
    $GartTotalPrice = mysqli_real_escape_string($conn, $shopInput['GartTotalPrice'] ?? '');
    $ProcessStatus= mysqli_real_escape_string($conn,$shopInput['ProcessStatus']);
    $CreateBy = mysqli_real_escape_string($conn, $userId);
    $Active = 1;

   //var_dump($GartShopId);exit;

    if(empty(trim($GartShopId)))
    {
        return error422('Enter your Garment Shop ID');
    }
    elseif(empty(trim($DesignID)))
    {
        return error422('Enter your Design');
    }
    elseif(empty(trim($GartInvoiceDate)))
    {
        return error422('Enter Invoice Date');
    }
    elseif(empty(trim($GartSendQty)))
    {
        return error422('Enter Item Qty');
    } 
    elseif(empty(trim($GartUnitPrice)))
    {
        return error422('Enter Unit Price');
    }
    elseif(empty(trim($GartTotalPrice)))
    {
        return error422('Enter Total Price');
    } 
    else
    {
        mysqli_begin_transaction($conn);

        $query = "INSERT INTO GarmentInvoiceHeader (GartInvoiceNo, GartShopId, DesignID, GartInvoiceDate, GartSendQty, GartUnitPrice, GartTotalPrice, CreateBy, Active) 
                  VALUES ('$GartInvoiceNo', '$GartShopId', '$DesignID', '$GartInvoiceDate', '$GartSendQty', '$GartUnitPrice', '$GartTotalPrice', '$CreateBy', '$Active')";
        
        //xvar_dump($query);exit;

        $result = mysqli_query($conn,$query);

        if(!$result)
        {
            mysqli_rollback($conn);
            $data = [
                'status'=> 500,
                'message'=> 'Garment invoice save failed: ' . mysqli_error($conn),
            ];
            header('HTTP/1.0 500 Internal server Error');
            return json_encode($data);
        }


           // 2️⃣ Update Design Table
           // ⚠️ Design table name eka hariyata replace karanna (ex: DesignDetails / Design)
           $query2 = "UPDATE Designs 
               SET 
                   Active = '5',
                   ProcessStatus = '$ProcessStatus',
                   ModifiedBy = '$CreateBy' 
               WHERE DesignID = '$DesignID'";

           $result2 = mysqli_query($conn,$query2);

        if($result2 && mysqli_affected_rows($conn) > 0)
        {
            mysqli_commit($conn);
            $data = [

                'status'=> 200,
                'message'=> 'Garment ready design saved Successfully',
            ];
            header('HTTP/1.0 200 Success');
            return json_encode($data);
        }
        else{
            $errorMessage = $result2 ? 'Design not found or already updated' : 'Design update failed: ' . mysqli_error($conn);
            mysqli_rollback($conn);
            $data = [

                'status'=> 500,
                'message'=> $errorMessage,
            ];
            header('HTTP/1.0 500 Internal server Error');
            return json_encode($data);
        }
        
    }
    // Close the database connection
    $conn->close();
}

function createInvoiceNo() {
    global $conn;

    // Get current year and month
    $yearMonth = date("Y-m");

    // Count how many invoices exist for this year-month
    $query = "
        SELECT COUNT(*) AS cnt 
        FROM GarmentInvoiceHeader 
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
    $invoiceNo = "Grt_INV-" . $yearMonth . "-" . str_pad($seq, 4, "0", STR_PAD_LEFT);

    return $invoiceNo;
}

function updateGrtCompletedPrdtsInfo($shopParam,$userId){

    /// Created By : Kavinda
    /// Date : 2026-05-13
   /// Description : This function is used to update the garment complete products list
   ///              when update this table, update the design table also

   //var_dump($shopParam);exit;

    //ReceivedStatus  =  1 kiyanne print ekata dapu design eka complete wela garment ekata uawanna awilla kiyana eka

   global $conn;
   

   if(!isset($shopParam['GRIHID'])){

       return error422('Garment Invoice id not found in URL');
   }
   elseif($shopParam['GRIHID'] == null){
       return error422('Enter your Garment Invoice id');
   }

  
   $ReceivedQty=  mysqli_real_escape_string($conn,$shopParam['ReceivedQty']);
   $ReceivedStatus =  mysqli_real_escape_string($conn,$shopParam['ReceivedStatus']);
   $PaidAmount=  mysqli_real_escape_string($conn,$shopParam['PaidAmount']);
   $PaidDate=  mysqli_real_escape_string($conn,$shopParam['PaidDate']);
   $ReceivedQtyTotPrice= mysqli_real_escape_string($conn,$shopParam['ReceivedQtyTotPrice']);
   $GRIHID = mysqli_real_escape_string($conn,$shopParam['GRIHID']);
   $DesignID = mysqli_real_escape_string($conn,$shopParam['DesignID']);
   $ProcessStatus = mysqli_real_escape_string($conn,$shopParam['ProcessStatus']);
   $GartShopId = mysqli_real_escape_string($conn,$shopParam['GartShopId']);
   $ModifiedBy = $userId;
   $Active = 1;

   if(empty(trim($ReceivedQty)))
   {
       return error422('Enter Received Qty');
   } 
   elseif(empty(trim($ReceivedStatus)))
   {
       return error422('Enter Received Status');
   }   
   elseif(empty(trim($DesignID)))
   {
       return error422('Enter Design ID');
   }
   elseif(empty(trim($ReceivedQtyTotPrice)))
   {
       return error422('Enter Received Qty Total Price');
   }

   elseif(trim($PaidAmount) !== '' && !is_numeric($PaidAmount))
   {
       return error422('Paid Amount must be a number');
   }
   elseif(trim($PaidAmount) !== '' && (float)$PaidAmount !== 0.0 && empty(trim($PaidDate)))
   {
       return error422('Enter Paid Date when Paid Amount is provided');
   }
   else
   {

      mysqli_begin_transaction($conn); // 🔥 important

       try {


           // 1️⃣ Update PrintInvoiceHeader
           $query1 = "UPDATE GarmentInvoiceHeader 
               SET 
                   ReceivedQty = '$ReceivedQty', 
                   ReceivedStatus = '$ReceivedStatus', 
                   ReceivedQtyTotPrice = '$ReceivedQtyTotPrice',
                   ModifiedBy = '$ModifiedBy'
               WHERE GRIHID  = '$GRIHID'";

           $result1 = mysqli_query($conn,$query1);

           if(!$result1){
               throw new Exception("Header update failed");
           }

            // 2️⃣ Insert payment only when PaidAmount is provided and not zero
           if(trim($PaidAmount) !== '' && (float)$PaidAmount !== 0.0) {

                $nextId = getNextGrtHeaderId();
                $GrtPayRefNo = createGarmentRefNo($nextId);


               $queryPay = "INSERT INTO GartProPayTrans (GartShopId, GrtPayRefNo, PaidAmount, PaidDate,Active,CreateBy) 
                   VALUES ('$GartShopId', '$GrtPayRefNo', '$PaidAmount', '$PaidDate','$Active','$ModifiedBy')";

               $resultPay = mysqli_query($conn,$queryPay);

               if(!$resultPay){
                   throw new Exception("Payment transaction insert failed");
               }
           }

           // 3 Update Design Table
           // ⚠️ Design table name eka hariyata replace karanna (ex: DesignDetails / Design)
           $query2 = "UPDATE Designs 
               SET 
                   stock_qty = '$ReceivedQty',
                   Active = '1', -- 1 kiyanne sell karanna ready
                   ProcessStatus = '$ProcessStatus',
                   ModifiedBy = '$ModifiedBy'
               WHERE DesignID = '$DesignID'";

           $result2 = mysqli_query($conn,$query2);

           if(!$result2){
               throw new Exception("Design update failed");
           }

           // 3️⃣ Commit
           mysqli_commit($conn);

           $data = [
               'status'=> 200,
               'message'=> 'Updated Successfully (Header + Design)',
           ];
           header('HTTP/1.0 200 Success');
           return json_encode($data);

       } catch (Exception $e){

           mysqli_rollback($conn); // ❌ rollback if error

           $data = [
               'status'=> 500,
               'message'=> $e->getMessage(),
           ];
           header('HTTP/1.0 500 Internal server Error');
           return json_encode($data);
       }   
   }
}

function getGrtSendInvoiceById($shopParam) {
    
    /// Created By : Kavinda
    /// Date : 2026-05-13
    /// Description : This function is used to get Garment section send Invoice details by ID
    
    global $conn;

    if (!isset($shopParam) || !is_array($shopParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($shopParam['GRIHID']) || empty($shopParam['GRIHID'])) {
        return error422('Enter your Garment Invoice Number');
    }

    $PSID = mysqli_real_escape_string($conn, $shopParam['GRIHID']);


        //   $query =  "SELECT ph.PIHID,ph.DesignID,ph.PrtInvoiceNo,ph.PrtShopId,ph.PrtInvoiceDate,ph.PrtSendQty,ph.PrtUnitPrice,ph.PrtTotalPrice,
        //             ph.ReceivedQty,ph.ReceivedStatus,ph.PaidAmount,ph.PaidStatus,ph.PaidStatus,ph.PaidDate,
        //             ps.PShopName,ds.DesignName,ph.Active,ph.ReceivedStatus,ph.PaidStatus
        //         FROM PrintInvoiceHeader ph 
        //         INNER JOIN PrintShop ps 
        //         ON ph.PrtShopId = ps.PSID 
        //         INNER JOIN Designs ds
        //         ON ph.DesignID = ds.DesignID WHERE ph.Active = 1 AND ph.PIHID = '$PSID' ORDER BY ph.PIHID DESC";

        $query= "SELECT gh.GRIHID,gh.DesignID,gh.GartInvoiceNo,gh.GartShopId,gh.GartInvoiceDate,gh.GartSendQty,gh.GartUnitPrice,gh.GartTotalPrice,
                gh.ReceivedQty,gh.ReceivedStatus,gi.GarmentName,ds.DesignName,gh.Active,gh.ReceivedStatus,gh.ReceivedQtyTotPrice
                FROM GarmentInvoiceHeader gh 
                INNER JOIN GarmentInfo gi 
                ON gh.GartShopId = gi.GID 
                INNER JOIN Designs ds
                ON gh.DesignID = ds.DesignID WHERE gh.Active = 1 AND gh.GRIHID = '$PSID' ORDER BY gh.GRIHID DESC";

    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Printing invoice Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Printing invoice Found',
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

function getGrtSendInvoice() {
    
    /// Created By : Kavinda
    /// Date : 2026-05-13
    /// Description : This function is used to get Garment section send Invoice details as a List

    global $conn;

    $query = "SELECT gh.GRIHID,gh.DesignID,gh.GartInvoiceNo,gh.GartShopId,gh.GartInvoiceDate,gh.GartSendQty,gh.GartUnitPrice,gh.GartTotalPrice,
                    gh.ReceivedQty,gh.ReceivedStatus,gh.ReceivedQtyTotPrice,
                    gi.GarmentName,ds.DesignName,gh.Active,gh.ReceivedStatus
                FROM GarmentInvoiceHeader gh 
                INNER JOIN GarmentInfo gi 
                ON gh.GartShopId = gi.GID 
                INNER JOIN Designs ds
                ON gh.DesignID = ds.DesignID WHERE gh.Active = 1 ORDER BY gh.GRIHID DESC";
    
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {

        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Garment Invoices list Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Garment Invoices Found',
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

function getNextGrtHeaderId() {
    /// Created By : Kavinda
    /// Date : 2026-06-04
    /// Description : Get the next PayHeader ID

    global $conn;

    $queryNextId = "SELECT MAX(GPTID) AS last_id FROM GartProPayTrans";
    $resultNextId = mysqli_query($conn, $queryNextId);

    if ($resultNextId) {
        $row = mysqli_fetch_assoc($resultNextId);
        $nextId = isset($row['last_id']) ? $row['last_id'] + 1 : 1; // If no rows, start with 1
        return $nextId;
    } else {
        throw new Exception("Failed to retrieve the next PayHeader ID: " . mysqli_error($conn));
    }
}

function createGarmentRefNo($nextId) {
    global $conn;

    // Get current year and month
    $yearMonth = date("Y-m");

    // Count how many invoices exist for this year-month
    

    // Format invoice number: INV-2025-09-0001
    $invoiceNo = "Grt-Pay-" . $yearMonth . "-" . str_pad($nextId, 5, "0", STR_PAD_LEFT);

    return $invoiceNo;
}

function getGrtShopPayDetailsById($shopParam) {
    
    /// Created By : Kavinda
    /// Date : 2026-05-13
    /// Description : This function is used to get Garment section send Invoice details by ID
    
    global $conn;

    if (!isset($shopParam) || !is_array($shopParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($shopParam['GartShopId']) || empty($shopParam['GartShopId'])) {
        return error422('Enter your Garment Invoice Number');
    }

    $GartShopId = mysqli_real_escape_string($conn, $shopParam['GartShopId']);


        
        $query= "SELECT gpt.GartShopId,gpt.GrtPayRefNo,gpt.PaidAmount,gpt.PaidDate,gi.GarmentName,gi.ContactNo1,gi.Town
                    FROM GartProPayTrans gpt
                    INNER JOIN GarmentInfo gi
                    ON gpt.GartShopId = gi.GID
                    WHERE gpt.GartShopId  = '$GartShopId' AND gpt.Active = 1";

    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Garment Shop Payment Details Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Garment Shop Payment Details Found',
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