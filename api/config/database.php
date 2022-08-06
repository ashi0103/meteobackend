<?php 
    class Database {
        private $host = "localhost: 3306";
        private $database_name = "METEODB";
        private $username = "meteo";
        private $password = "Test@1234567";

        public $conn;

        public function getConnection(){
            $this->conn = null;
            try{
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->database_name, $this->username, $this->password);
                $this->conn->exec("set names utf8");
            }catch(PDOException $exception){
                echo "Database could not be connected: " . $exception->getMessage();
            }
            return $this->conn;
        }
    }  
?>

