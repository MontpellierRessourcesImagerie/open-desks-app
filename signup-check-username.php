<?php

include("db.php");
include("utils.php");

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    header("Location: .");
    exit;
}

$fields = ["username"];
if (!requiredData($fields)) {
    echo json_encode(["status" => "error", "message" => "Username not provided"]);
    exit;
}

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Username cannot be empty"]);
    exit;
}

$pdo = connect_db();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM engineers WHERE username = :username");
$stmt->execute([':username' => $username]);
$userExists = $stmt->fetchColumn() > 0;

if ($userExists) {
    echo json_encode(["status" => "taken", "message" => "Username already taken"]);
} else {
    echo json_encode(["status" => "available", "message" => "Username is available"]);
}

?>