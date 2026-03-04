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

?>