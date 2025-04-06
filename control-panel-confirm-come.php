<?php

header("Content-Type: application/json");
include("db.php");
include("utils.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$success = true;
$msg = "";

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    $success = false;
    $msg = "Invalid request method or no data received.";
    echo json_encode(["success" => $success, "msg" => $msg]);
    exit;
}

$fields = ["email", "session_id"];
if (!requiredData($fields)) {
    $success = false;
    $msg = "Missing required fields.";
    echo json_encode(["success" => $success, "msg" => $msg]);
    exit;
}

$session_id = $_POST["session_id"];
$email      = $_POST["email"];

try {
    $sql = "UPDATE appointments SET has_come = TRUE WHERE user_id = :email AND session_id = :session_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':email'      => $email,
        ':session_id' => $session_id
    ]);
} catch (Exception $e) {
    $success = false;
    $msg = "Database error: " . $e->getMessage();
}

$pdo = null;
echo json_encode(["success" => $success, "msg" => $msg, "s_id" => $session_id, "email" => $email]);
unset($_POST);
exit;
?>