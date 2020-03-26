<?php

require_once 'env.config.php';

global $db;

try {
    $options = array(
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_FOUND_ROWS => true
    );
    $db = new PDO("mysql:dbname=".DBNAME.";host=".HOST, DBUSER, DBPASS, $options);
} catch (PDOException $e) {
    echo "ERRO: ".$e->getMessage();
    exit;
}