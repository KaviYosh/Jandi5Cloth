<?php

// $host = "localhost";
// $username = "root";
// $password = "";
// $dbname = "liveyminvest";

// $conn = mysqli_connect($host,$username,$password,$dbname);


$host = "localhost";
$username = "crystal3_admin";
$password = "crystaladmin@123";
$dbname = "crystal3_jandiStyle"; 

$conn = mysqli_connect($host,$username,$password,$dbname);


if(!$conn){
    
    die("Connection Failed: " . mysqli_connect_error());

}


?>