<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    exit;
}

$username = trim($_POST['username']);

if (empty($username)) {
    echo json_encode(["status" => "error", "message" => "Username cannot be empty"]);
    exit();
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