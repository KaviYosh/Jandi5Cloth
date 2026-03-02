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


function saveMaterialInfo($MaterialInput, $imageInfo, $userId){
  /// Created By : Kavinda
  /// Date : 2025-02-28
 /// Description : This function is used to save Material details along with multiple images 
     
    global $conn;
    
    // Generate Invoice Number
    $invoiceNo = createInvoiceNo();

    // අගයන් ටික clean කරගමු
    $date = mysqli_real_escape_string($conn, $MaterialInput['date']);
    $totalPayment = mysqli_real_escape_string($conn, $MaterialInput['totalPayment']);
    $remarks = mysqli_real_escape_string($conn, $MaterialInput['remarks']);
    $createBy = mysqli_real_escape_string($conn, $userId);
    $active = 1;

    // Validation
    if(empty(trim($date))){
        return error422('Select date please');
    }
    if(empty(trim($totalPayment))){
        return error422('Enter total Payment amount');
    }

    // 1. ප්‍රධාන Material තොරතුරු ටික සේව් කරමු
    $query = "INSERT INTO MaterialInfo (ProcessedDate, TotalPayment, TransNo, Remarks, CreateUser, Active) 
              VALUES ('$date', '$totalPayment', '$invoiceNo', '$remarks', '$createBy', '$active')";

    $result = mysqli_query($conn, $query);

    if($result){
        // අලුතින් Insert වුණු record එකේ ID එක ගන්න (මේක තමයි image table එකට ඕනේ වෙන්නේ)
        $last_id = mysqli_insert_id($conn);

        //var_dump($imageInfo['image']);exit;

        if(isset($imageInfo['image']) && !empty($imageInfo['image']['name'])) {

            //var_dump($imageInfo['image']);exit;

            // 1. Array එකක්ද නැද්ද කියලා check කරලා loop එකකට ගන්න පුළුවන් විදියට හදාගන්නවා
            $files = $imageInfo['image'];
            $file_count = is_array($files['name']) ? count($files['name']) : 1;
        
            for ($i = 0; $i < $file_count; $i++) {
                // Multiple නම් index එක ගන්නවා, Single නම් null (දත්ත කෙලින්ම ගන්නවා)
                $img_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $img_name_tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
        
                if (!empty($img_name)) {
                    $ext = pathinfo($img_name, PATHINFO_EXTENSION);
                    
                    // 2. අලුත් නමක් හදනවා (Unique වෙන්න time එකයි index එකයි ගන්නවා)
                    $img_new = 'bill_' . time() . '_' . $i; 
                    $path = "../billImage/" . $img_new . "." . $ext; 
                    $path_db = "billImage/" . $img_new . "." . $ext;
        
                    // 3. Folder එකට upload කරනවා
                    if (move_uploaded_file($img_name_tmp, $path)) {
                        
                        // 4. DB එකට save කරනවා (SQL Injection වලින් බේරෙන්න mysqli_real_escape_string පාවිච්චි කිරීම වඩාත් හොඳයි)
                        $img_query = "INSERT INTO MaterialBillInfo (MateID, ImagePath, Active, CreateUser) 
                                     VALUES ('$last_id', '$path_db', $active, $createBy)";
                        
                        mysqli_query($conn, $img_query);
                    }
                }
            }
        }

        $data = [
            'status'=> 200,
            'message'=> 'Material and Images saved successfully',
        ];
        header('HTTP/1.0 200 Success');
        return json_encode($data);

    } else {
        $data = [
            'status'=> 500,
            'message'=> 'Internal server Error',
        ];
        header('HTTP/1.0 500 Internal server Error');
        return json_encode($data);
    }
}

function getMaterialInfo(){

    /// Created By : Kavinda
    /// Date : 2025-03-02
    /// Description : This function is used to get the material details along with the associated images (if any)

    global $conn;

    $query = "
         SELECT 
            MaterialInfo.*, 
            GROUP_CONCAT(MaterialBillInfo.ImagePath) AS ImagePaths 
        FROM 
            MaterialInfo 
        LEFT JOIN 
            MaterialBillInfo 
        ON 
            MaterialInfo.MID = MaterialBillInfo.MateID 
        WHERE 
            MaterialInfo.Active = 1 
        GROUP BY 
            MaterialInfo.MID 
        ORDER BY 
            MaterialInfo.CreatedDate DESC
    ";
    //var_dump($query );exit;

    $result = mysqli_query($conn, $query);

    

    if(mysqli_num_rows($result) > 0){
        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return json_encode([
            'status' => 200,
            'message' => 'Material records retrieved successfully',
            'data' => $data
        ]);
    } else {
        return json_encode([
            'status' => 404,
            'message' => 'No active Material records found',
            'data' => []
        ]);
    }
}   

function getMaterialInfoByID($shopParam){
    /// Created By : Kavinda
  /// Date : 2025-03-02
 /// Description : his function is used to get the material details by ID along with the associated images (if any)
    global $conn;

    // Validate the input
    $materialID = mysqli_real_escape_string($conn, $shopParam['MID']);
    if (empty(trim($materialID))) {
        return json_encode([
            'status' => 422,
            'message' => 'Material ID is required'
        ]);
    }

    // Query to fetch material info by ID
    $query = "
        SELECT 
            MaterialInfo.*, 
            GROUP_CONCAT(MaterialBillInfo.ImagePath) AS ImagePaths 
        FROM 
            MaterialInfo 
        LEFT JOIN 
            MaterialBillInfo 
        ON 
            MaterialInfo.MID = MaterialBillInfo.MateID 
        WHERE 
            MaterialInfo.MID = '$materialID' 
            AND MaterialInfo.Active = 1 
        GROUP BY 
            MaterialInfo.MID
    ";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        return json_encode([
            'status' => 200,
            'message' => 'Material record retrieved successfully',
            'data' => $data
        ]);
    } else {
        return json_encode([
            'status' => 404,
            'message' => 'Material record not found',
            'data' => []
        ]);
    }
}

function createInvoiceNo() {
    global $conn;

    // Get current year and month
    $yearMonth = date("Y-m");

    // Count how many invoices exist for this year-month
    $query = "
        SELECT COUNT(*) AS cnt 
        FROM MaterialInfo 
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
    $invoiceNo = "Mat-". "INV-" . $yearMonth . "-" . str_pad($seq, 4, "0", STR_PAD_LEFT);

    return $invoiceNo;
}

function deleteMaterialInfo($bankParam,$userId) {

    /// Created By : Kavinda
    /// Date : 2026-03-02
    /// Description : This function is used to delete the Material details by setting the 
    //                Active field to 0 (soft delete)

    global $conn;

    //var_dump($bankParam['MID']);exit;

    if (!isset($bankParam) || !is_array($bankParam)) {
        return error422('Invalid input data format.');
    }

    if (!isset($bankParam['MID']) || empty($bankParam['MID'])) {
        return error422('Enter the Material ID');
    }

    $id = mysqli_real_escape_string($conn, $bankParam['MID']);
    $ModifiedBy = $userId;

    // Soft delete MaterialInfo record
    $query = "UPDATE MaterialInfo SET Active = 0, ModifiedBy = '$ModifiedBy' WHERE MID = '$id'";
    $query_run = mysqli_query($conn, $query);

    if ($query_run) {
        if (mysqli_affected_rows($conn) > 0) {
            // Soft delete related MaterialBillInfo records
            $bill_query = "UPDATE MaterialBillInfo SET Active = 0, ModifiedBy = '$ModifiedBy' WHERE MateID = '$id'";
            mysqli_query($conn, $bill_query);

            $data = [
                'status' => 200,
                'message' => 'Material Details Deleted Successfully',
            ];
            header('HTTP/1.0 200 OK');
            return json_encode($data);
        } else {
            $data = [
                'status' => 404,
                'message' => 'No Material Found with the given ID',
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


?>