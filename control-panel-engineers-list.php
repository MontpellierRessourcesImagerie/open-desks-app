<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

include("db.php");
include("connect-ensure.php");

function getUsersList($pdo) {
    try {
        $query = $pdo->query("
            SELECT username, accepted, created_at 
            FROM engineers
        ");
        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    } catch (Exception $e) {
        return [];
    }
    return [];
}

$pdo = connect_db();
requireAuthentication($pdo, "control-panel-engineers-list.php");
$users = getUsersList($pdo);
$pdo   = null;
echo json_encode($users);

?>