<?php

header("Content-Type: application/json");
include("db.php");
include("connect-ensure.php");
include("index-announcement.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$success = true;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $success = false;
    echo json_encode(["success" => $success, "error" => "Invalid request method."]);
    exit;
}

if (!isset($_POST["message"])) {
    $success = false;
    echo json_encode(["success" => $success, "error" => "No message provided."]);
    exit;
}

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