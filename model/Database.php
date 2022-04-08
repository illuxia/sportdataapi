<?php

class Database {

    public $dbserver = '';
    public $database = '';
    public $username = '';
    public $password = '';
    public $pdo = '';

    public function __construct(){
        $this->dbserver = 'localhost';
        $this->database = 'sportdataapi';
        $this->username = 'root';
        $this->password = '';
        $this->pdo = new PDO("mysql:host=".$this->dbserver.";dbname=".$this->database, $this->username, $this->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}