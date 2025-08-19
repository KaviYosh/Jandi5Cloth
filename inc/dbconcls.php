<?php


class Database {
   
    private $host = "localhost";
    private $dbname = "crystal3_jandiStyle"; 
    private $username = "crystal3_admin";
    private $password = "crystaladmin@123";
    public $conn;


    public function getConnection() {

        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->dbname . ";charset=utf8",
                $this->username,
                $this->password
            );
    
            // Set error mode
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
            exit();
        }
    
        return $this->conn;






    }
}

?>
