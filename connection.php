<?php



    require_once 'config.php';

    $database= new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

?>