<?php

    class DBAccess {
        // properties
        private $host="localhost";
        private $db_name="voisinservice";
        private $login="root";
        private $password="";
        
        private $connection=null;
        
        // getConnection
        public function getConnection() {
            $connection_str = "mysql:host=$this->host;dbname=$this->db_name";
            if ($this->connection==null) {
                $this->connection = new PDO ($connection_str, $this->login, $this->password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->connection;
        }

        // releaseConnection
        public function releaseConnection() {
            $this->connection=null;
        }
    }
?>