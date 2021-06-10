<?php
// used to get mysql database connection
class DatabaseService{

    private $db_host = "kpm_db";
    private $db_name = "kpm";
    private $db_user = "admin";
    private $db_password = "admin";
    private $connection;

    public function getConnection(){

        $this->connection = null;

        try{
            $this->connection = new PDO("pgsql:host=" . $this->db_host . ";dbname=" . $this->db_name, $this->db_user, $this->db_password);
        }catch(PDOException $exception){
            echo "Connection failed: " . $exception->getMessage();
        }

        return $this->connection;
    }
}
?>