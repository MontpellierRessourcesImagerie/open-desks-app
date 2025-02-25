<?php

header("Content-Type: application/json");
include("./db.php");
include("./ensure-connect.php");

$pdo = connect_db();
requireAuthentication($pdo, "./manageUser.php");

$success = true;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

if (!isset($_POST["username"])) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$username = $_POST["username"];

try {
    $stmt = $pdo->prepare("DELETE FROM engineers WHERE username = :username");
    $stmt->execute([":username" => $username]);
} catch (Exception $e) {
    $success = false;
}

$pdo = null;
echo json_encode(["success" => $success]);
exit;
?>