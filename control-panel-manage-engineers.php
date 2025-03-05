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

$fields = ["username", "accepted"];
if (!requiredData($fields)) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$username = $_POST["username"];
$accepted = $_POST["accepted"] === "true" ? 1 : 0;

try {
    $stmt = $pdo->prepare("
        UPDATE engineers 
        SET accepted = :accepted 
        WHERE username = :username
    ");
    $stmt->execute([
        ":accepted" => $accepted,
        ":username" => $username
    ]);

} catch (Exception $e) {
    $success = false;
}

$pdo = null;
unset($_POST);
echo json_encode(["success" => $success]);
exit;
?>
