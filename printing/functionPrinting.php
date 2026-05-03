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

function saveShop($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2026-03-15
    /// Description : This function is used to save printing shop details 

    global $conn;

    
    $PShopName=  mysqli_real_escape_string($conn,$shopInput['PShopName']);
    $town=  mysqli_real_escape_string($conn,$shopInput['town']);
    $address=  mysqli_real_escape_string($conn,$shopInput['address']);
    $contactNo1= mysqli_real_escape_string($conn,$shopInput['contactNo1']);
    $contactNo2=  mysqli_real_escape_string($conn,$shopInput['contactNo2']);
    $CreateBy=  mysqli_real_escape_string($conn,$userId);
    $Active = 1;

   

    if(empty(trim($PShopName)))
    {
        return error422('Enter your Shop Name');
    }
    elseif(empty(trim($town)))
    {
        return error422('Enter shop town');
    }
    elseif(empty(trim($address)))
    {
        return error422('Enter shop address');
    }
    elseif(empty(trim($contactNo1)))
    {
        return error422('Enter contact No');
    }  
    else
    {
        //var_dump($path_db);exit;

        $query = "INSERT INTO PrintShop (PShopName, town, address, contactNo1, contactNo2, CreateBy, Active) 
                  VALUES ('$PShopName', '$town', '$address', '$contactNo1', '$contactNo2', '$CreateBy', '$Active')";
        
        //xvar_dump($query);exit;

        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($result);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Print shop saved Successfully',
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

function deleteShopInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2026-03-15
   /// Description : This function is used to update the shop details

   //var_dump($shopParam);exit;

   global $conn;
   

   if(!isset($shopParam['PSID'])){

       return error422('Shop id not found in URL');
   }
   elseif($shopParam['PSID'] == null){
       return error422('Enter your Shop id');
   }
    
   $PSID =  mysqli_real_escape_string($conn,$shopParam['PSID']);
  
   if(empty(trim($PSID)))
   {
        return error422('Enter your Shop id');
   }
   else
   {
       //var_dump($path_dbProf);exit;

       $query = " UPDATE PrintShop 
           SET 
               Active = 0,
               ModifiedBy = '$userId'
               
           WHERE 
               PSID = '$PSID' ";
       
       $result = mysqli_query($conn,$query);

       if($result)
       {
           //var_dump($query);exit;
           $data = [

               'status'=> 200,
               'message'=> 'Shop Delete Successfully',
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

function updateShopInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2026-03-15
   /// Description : This function is used to update the shop details

   //var_dump($shopParam);exit;

   global $conn;
   

   if(!isset($shopParam['PSID'])){

       return error422('Shop id not found in URL');
   }
   elseif($shopParam['PSID'] == null){
       return error422('Enter your Shop id');
   }
   

  
   $PShopName=  mysqli_real_escape_string($conn,$shopParam['PShopName']);
   $PSID =  mysqli_real_escape_string($conn,$shopParam['PSID']);
   $town=  mysqli_real_escape_string($conn,$shopParam['town']);
   $address=  mysqli_real_escape_string($conn,$shopParam['address']);
   $contactNo1= mysqli_real_escape_string($conn,$shopParam['contactNo1']);
   $contactNo2=  mysqli_real_escape_string($conn,$shopParam['contactNo2']);
   


   if(empty(trim($PShopName)))
   {
       return error422('Enter your Shop Name');
   }
   elseif(empty(trim($town)))
   {
       return error422('Enter your town');
   }
   elseif(empty(trim($address)))
   {
       return error422('Enter your address');
   }
   elseif(empty(trim($contactNo1)))
   {
       return error422('Enter your contact No');
   }
   else
   {

       //var_dump($path_dbProf);exit;

       $query = " UPDATE PrintShop 
           SET 
               PShopName = '$PShopName', 
               town = '$town', 
               address = '$address', 
               contactNo1 = '$contactNo1',
               contactNo2 = '$contactNo2',
               ModifiedBy = '$userId'
               
           WHERE 
               PSID = '$PSID' ";
       
       $result = mysqli_query($conn,$query);

       if($result)
       {
           //var_dump($query);exit;
           $data = [

               'status'=> 200,
               'message'=> 'Shop updated Successfully',
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

function getShopById($shopParam) {
    
    /// Created By : Kavinda
    /// Date : 2026-03-15
    /// Description : This function is used to get shop details by ID

    global $conn;

    if (!isset($shopParam) || !is_array($shopParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($shopParam['PSID']) || empty($shopParam['PSID'])) {
        return error422('Enter your shop Number');
    }

    $PSID = mysqli_real_escape_string($conn, $shopParam['PSID']);

    $query = "SELECT * FROM PrintShop WHERE Active = 1 AND PSID = '$PSID'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Printing Shop Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Printing Shops Found',
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

function getShopList() {
    
    /// Created By : Kavinda
    /// Date : 2025-08-19
    /// Description : This function is used to get shop list

    global $conn;

    $query = "SELECT PSID,PShopName,town,address, contactNo1, contactNo2 FROM PrintShop WHERE Active = 1 ORDER BY PSID DESC";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Print Shop list Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Print Shops Found',
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

function savePrintProduction($shopInput,$userId){

    /// Created By : Kavinda
    /// Date : 2026-04-29
    /// Description : This function is used to save Print ready design set to databased 

    global $conn;

     // Generate Invoice Number
    $PrtInvoiceNo = createInvoiceNo();
    
    $PrtShopId=  mysqli_real_escape_string($conn,$shopInput['PrtShopId']);
    $DesignID=  mysqli_real_escape_string($conn,$shopInput['DesignID']);
    $PrtInvoiceDate=  mysqli_real_escape_string($conn,$shopInput['PrtInvoiceDate']);
    $PrtSendQty= mysqli_real_escape_string($conn,$shopInput['PrtSendQty']);
    $PrtUnitPrice=  mysqli_real_escape_string($conn,$shopInput['PrtUnitPrice']);
    $PrtTotalPrice=  mysqli_real_escape_string($conn,$shopInput['PrtUnitPrice']);
    $CreateBy = $userId;
    $Active = 1;

   

    if(empty(trim($PrtShopId)))
    {
        return error422('Enter your Print Shop Name');
    }
    elseif(empty(trim($DesignID)))
    {
        return error422('Enter your Design');
    }
    elseif(empty(trim($PrtInvoiceDate)))
    {
        return error422('Enter Invoice Date');
    }
    elseif(empty(trim($PrtSendQty)))
    {
        return error422('Enter Item Qty');
    } 
    elseif(empty(trim($PrtUnitPrice)))
    {
        return error422('Enter Unit Price');
    } 
    else
    {
        //var_dump($path_db);exit;

        $query = "INSERT INTO PrintInvoiceHeader (PrtInvoiceNo, PrtShopId, DesignID, PrtInvoiceDate, PrtSendQty,PrtUnitPrice,PrtTotalPrice, CreateBy, Active) 
                  VALUES ('$PrtInvoiceNo', '$PrtShopId', '$DesignID', '$PrtInvoiceDate', '$PrtSendQty','$PrtUnitPrice','$PrtTotalPrice','$CreateBy', '$Active')";
        
        //xvar_dump($query);exit;

        $result = mysqli_query($conn,$query);

        if($result)
        {
            //var_dump($result);exit;
            $data = [

                'status'=> 200,
                'message'=> 'Print ready design  saved Successfully',
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

function createInvoiceNo() {
    global $conn;

    // Get current year and month
    $yearMonth = date("Y-m");

    // Count how many invoices exist for this year-month
    $query = "
        SELECT COUNT(*) AS cnt 
        FROM PrintInvoiceHeader 
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
    $invoiceNo = "Prt_INV-" . $yearMonth . "-" . str_pad($seq, 4, "0", STR_PAD_LEFT);

    return $invoiceNo;
}

function updatePrtCompltPrdtsInfo($shopParam,$userId){

    /// Created By : Kavinda
   /// Date : 2026-04-30
   /// Description : This function is used to update the Print complete products list
   ///              when update this table, update the design table also

   //var_dump($shopParam);exit;

    //ReceivedStatus  =  1 kiyanne print ekata dapu design eka complete wela garment ekata uawanna awilla kiyana eka

   global $conn;
   

   if(!isset($shopParam['PIHID'])){

       return error422('Print Invoice id not found in URL');
   }
   elseif($shopParam['PIHID'] == null){
       return error422('Enter your Print Invoice id');
   }

  
   $ReceivedQty=  mysqli_real_escape_string($conn,$shopParam['ReceivedQty']);
   $ReceivedStatus =  mysqli_real_escape_string($conn,$shopParam['ReceivedStatus']);
   $PaidAmount=  mysqli_real_escape_string($conn,$shopParam['PaidAmount']);
   $PaidDate=  mysqli_real_escape_string($conn,$shopParam['PaidDate']);
   $PaidStatus= mysqli_real_escape_string($conn,$shopParam['PaidStatus']);
   $PIHID= mysqli_real_escape_string($conn,$shopParam['PIHID']);
   $DesignID = mysqli_real_escape_string($conn,$shopParam['DesignID']);
   $ProcessStatus = mysqli_real_escape_string($conn,$shopParam['ProcessStatus']);
   $ModifiedBy = $userId;

   if(empty(trim($ReceivedQty)))
   {
       return error422('Enter Received Qty');
   }
   elseif(empty(trim($PaidAmount)))
   {
       return error422('Enter Paid Amount');
   }
   elseif(empty(trim($PaidDate)))
   {
       return error422('Enter Paid Date');
   }
   elseif(empty(trim($ReceivedStatus)))
   {
       return error422('Enter Received Status');
   }
   elseif(empty(trim($PaidStatus)))
   {
       return error422('Enter Paid Status');
   }
   elseif(empty(trim($DesignID)))
   {
       return error422('Enter Design ID');
   }
   else
   {

      mysqli_begin_transaction($conn); // 🔥 important

       try {

           // 1️⃣ Update PrintInvoiceHeader
           $query1 = "UPDATE PrintInvoiceHeader 
               SET 
                   ReceivedQty = '$ReceivedQty', 
                   ReceivedStatus = '$ReceivedStatus', 
                   PaidAmount = '$PaidAmount', 
                   PaidDate = '$PaidDate',
                   PaidStatus = '$PaidStatus',
                   ModifiedBy = '$ModifiedBy'
               WHERE PIHID = '$PIHID'";

           $result1 = mysqli_query($conn,$query1);

           if(!$result1){
               throw new Exception("Header update failed");
           }

           // 2️⃣ Update Design Table
           // ⚠️ Design table name eka hariyata replace karanna (ex: DesignDetails / Design)
           $query2 = "UPDATE Designs 
               SET 
                   stock_qty = '$ReceivedQty',
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

function getPrintSndInvoiceById($shopParam) {
    
    /// Created By : Kavinda
    /// Date : 2026-05-02
    /// Description : This function is used to get Print section send Invoice details by ID
    global $conn;

    if (!isset($shopParam) || !is_array($shopParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($shopParam['PIHID']) || empty($shopParam['PIHID'])) {
        return error422('Enter your shop Number');
    }

    $PSID = mysqli_real_escape_string($conn, $shopParam['PIHID']);

    $query = "SELECT PIHID,PrtInvoiceNo,PrtShopId,PrtInvoiceDate,PrtSendQty,PrtUnitPrice,PrtTotalPrice,PShopName,DesignName 
                FROM PrintInvoiceHeader ph 
                INNER JOIN PrintShop ps 
                ON ph.PrtShopId = ps.PSID 
                INNER JOIN Designs ds
                ON ph.DesignID = ds.DesignID WHERE ph.Active = 1 AND ph.ReceivedStatus = 0 AND ph.PIHID = '$PSID' ORDER BY ph.PIHID DESC";

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

function getPrintSndInvoice() {
    
    /// Created By : Kavinda
    /// Date : 2026-05-02
    /// Description : This function is used to get Print section send Invoice details as a List

    global $conn;

    $query = "SELECT PIHID,PrtInvoiceNo,PrtShopId,PrtInvoiceDate,PrtSendQty,PrtUnitPrice,PrtTotalPrice,PShopName,DesignName 
                FROM PrintInvoiceHeader ph 
                INNER JOIN PrintShop ps 
                ON ph.PrtShopId = ps.PSID 
                INNER JOIN Designs ds
                ON ph.DesignID = ds.DesignID WHERE ph.Active = 1 AND ph.ReceivedStatus = 0 ORDER BY ph.PIHID DESC";
    
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {

        if (mysqli_num_rows($query_run) > 0) {
            $res = mysqli_fetch_all($query_run, MYSQLI_ASSOC);
            $data = [
                'status'=> 200,
                'message'=> 'Print Invoices list Fetched Successfully',
                'data' => $res
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status'=> 404,
                'message'=> 'No Print Invoices Found',
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