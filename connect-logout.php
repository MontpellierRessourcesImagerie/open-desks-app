<?php

include("db.php");

if (isset($_COOKIE['active_token'])) {
    $activeToken = $_COOKIE['active_token'];
    $pdo = connect_db();
    try {
        $updateStmt = $pdo->prepare("
            UPDATE engineers 
            SET active_token = NULL, 
                created_at = NULL 
            WHERE active_token = :active_token
        ");
        $updateStmt->execute([':active_token' => $activeToken]);
        setcookie("active_token", "", time() - 3600, "/", "", true, true);
        http_response_code(200);
        echo "DONE.";
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(501);
        echo "ERROR: " . $e->getMessage();
    }
    $pdo = null;
}

?>
