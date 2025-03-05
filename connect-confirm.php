<?php

include("db.php");
include("utils.php");

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    header("Location: .");
    exit;
}

$fields = [
    'username', 
    'password'
];

if (!requiredData($fields)) { exit; }

$pdo = connect_db();
$username = $_POST['username'];
$password = $_POST['password'];
$target   = $_POST['target'] ?? '.';
if (!isSafeFile($target)) { $target = "."; }

$stmt = $pdo->prepare("SELECT username, password_hash, accepted FROM engineers WHERE username = :username");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the user exists.
if (!$user) {
    header("Location: ./connect.php?target=" . urlencode($target) . "&error=" . urlencode("The username is incorrect."));
    exit;
}
// Check if the password is correct.
if (!password_verify($password, $user['password_hash'])) {
    header("Location: ./connect.php?target=" . urlencode($target) . "&error=" . urlencode("The password is incorrect."));
    exit;
}
// Check if the user was accepted.
if ((int)$user['accepted'] !== 1) {
    header("Location: ./connect.php?target=" . urlencode($target) . "&error=" . urlencode("The user has not been accepted yet."));
    exit;
}

// Generate a new active connection token + store it in the DB.
$activeToken = bin2hex(random_bytes(32));

$updateStmt = $pdo->prepare("
    UPDATE engineers 
    SET active_token = :active_token, 
        created_at = NOW() 
    WHERE username = :username
");

$updateStmt->execute([
    ':active_token' => $activeToken,
    ':username' => $username
]);

setcookie("active_token", $activeToken, time() + 86400, "/", "", true, true);
$pdo = null;
unset($_POST);

header("Location: " . htmlspecialchars($target));
exit;

?>
