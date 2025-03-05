<?php

header("Content-Type: application/json");
include("db.php");
include("utils.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");

$success = true;

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$fields = ["username"];
if (!requiredData($fields)) {
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
