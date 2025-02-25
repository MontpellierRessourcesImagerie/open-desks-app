<?php

header("Content-Type: application/json");

include("./db.php");
include("./ensure-connect.php");

$pdo = connect_db();
requireAuthentication($pdo, "./users_list.php");

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

$users = getUsersList($pdo);
$pdo = null;
echo json_encode($users);

?>