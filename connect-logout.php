<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");

if (isset($_COOKIE['active_token'])) {
    $activeToken = $_COOKIE['active_token'];

    try {
        $pdo = connect_db();

        $updateStmt = $pdo->prepare("
            UPDATE engineers 
            SET active_token = NULL, 
                created_at = NULL 
            WHERE active_token = :active_token
        ");
        $updateStmt->execute([':active_token' => $activeToken]);

        setcookie("active_token", "", time() - 3600, "/", "", true, true);

        $pdo->commit();
        $pdo = null;

        http_response_code(200);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo "Error: " . $e->getMessage();
    }
} else {
    http_response_code(400);
    echo "No active token found.";
}
?>
