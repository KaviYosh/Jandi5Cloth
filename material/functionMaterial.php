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

// function saveMaterialInfo($MaterialInput,$userId){

//     /// Created By : Kavinda
//     /// Date : 2025-08-19
//     /// Description : This function is used to save shop details 

//     global $conn;
    
//     // Generate Invoice Number
//     $invoiceNo = createInvoiceNo();

//     $ProcessedDate =  mysqli_real_escape_string($conn,$MaterialInput['date']);
//     $TotalPayment =  mysqli_real_escape_string($conn,$MaterialInput['totalPayment']);
//     $TransNo = $invoiceNo;
//     $Remarks =  mysqli_real_escape_string($conn,$MaterialInput['remarks']);
//     $CreateBy =  mysqli_real_escape_string($conn,$userId);
//     $Active = 1;

   

//     if(empty(trim($date)))
//     {
//         return error422('Select date please');
//     }
//     elseif(empty(trim($totalPayment)))
//     {
//         return error422('Enter total Payment amount');
//     }
     
//     else
//     {
//         //var_dump($path_db);exit;

//         $query = "INSERT INTO MaterialInfo (ProcessedDate, TotalPayment,TransNo, Remarks,CreateBy, Active) 
//                   VALUES ('$ProcessedDate', '$TotalPayment', '$address', '$contact_no1', '$contact_no2', '$CreateBy', '$Active')";

        

//         $result = mysqli_query($conn,$query);

//         if($result)
//         {
//             //var_dump($result);exit;
//             $data = [

//                 'status'=> 200,
//                 'message'=> 'shop saved Successfully',
//             ];
//             header('HTTP/1.0 200 Success');
//             return json_encode($data);
//         }
//         else{
//             $data = [

//                 'status'=> 500,
//                 'message'=> 'Internal server Error',
//             ];
//             header('HTTP/1.0 500 Internal server Error');
//             return json_encode($data);
//         }
        
//     }
//     // Close the database connection
//     $conn->close();
// }

function saveMaterialInfo($MaterialInput, $MaterialFiles, $userId){
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
    $query = "INSERT INTO MaterialInfo (ProcessedDate, TotalPayment, TransNo, Remarks, CreateBy, Active) 
              VALUES ('$date', '$totalPayment', '$invoiceNo', '$remarks', '$createBy', '$active')";

    $result = mysqli_query($conn, $query);

    if($result){
        // අලුතින් Insert වුණු record එකේ ID එක ගන්න (මේක තමයි image table එකට ඕනේ වෙන්නේ)
        $last_id = mysqli_insert_id($conn);

        // 2. Images හැන්ඩ්ල් කරමු (Multiple Images)
        if(isset($MaterialFiles['image'])){
            
            // PHP වල Multiple files එද්දි ඒක Array එකක් විදියට එන්නේ
            // ඒ නිසා foreach එකක් පාවිච්චි කරලා එකින් එක process කරනවා
            foreach($MaterialFiles['image']['tmp_name'] as $key => $tmp_name){
                
                if(!empty($tmp_name)){
                    $img_name = $MaterialFiles['image']['name'][$key];
                    $ext = pathinfo($img_name, PATHINFO_EXTENSION);
                    
                    // Unique නමක් හදාගමු (time එකට අමතරව loop එකේ index එකත් දානවා පින්තූර කිහිපයක් එකම වෙලාවේ එන නිසා)
                    $img_new = 'front_' . time() . '_' . $key . '.' . $ext;
                    
                    $path = "../billImage/" . $img_new;
                    $path_db = "billImage/" . $img_new;

                    if(move_uploaded_file($tmp_name, $path)){
                        // 3. දැන් ඉමේජ් පාත් එක අනිත් table එකට සේව් කරනවා
                        $img_query = "INSERT INTO material_images (material_id, image_path) VALUES ('$last_id', '$path_db')";
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

?>