<?php

header("Content-Type: application/json");
include("db.php");
include("utils.php");
include("connect-ensure.php");
include("index-announcement.php");

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    $success = false;
    echo json_encode(["success" => $success, "error" => "Invalid request method."]);
    exit;
}

$fields = ["message"];
if (!requiredData($fields)) {
    $success = false;
    echo json_encode(["success" => $success, "error" => "Missing required fields."]);
    exit;
}

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$success = true;

$message = $_POST["message"];
$error = "";

try {
    update_announcement($pdo, $message);
} catch (Exception $e) {
    $pdo->rollBack();
    $success = false;
    $error = $e->getMessage();
}

$pdo = null;
echo json_encode(["success" => $success, "error" => $error]);
exit;
?>