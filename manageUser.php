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

if (!isset($_POST["username"]) || !isset($_POST["accepted"])) {
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
echo json_encode(["success" => $success]);
exit;
?>
