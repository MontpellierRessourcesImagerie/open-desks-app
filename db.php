<?php

include("./config-db.php");

function connect_db() {
    global $_CONFIG_DB;

    $host     = $_CONFIG_DB["host"];
    $dbname   = $_CONFIG_DB["dbname"];
    $charset  = $_CONFIG_DB["charset"];
    $username = $_CONFIG_DB["username"];
    $password = $_CONFIG_DB["password"];

    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        die("Connection error: " . $e->getMessage());
    }
}

?>