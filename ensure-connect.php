<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function getAuthenticatedUser($pdo) {
    if (!isset($_COOKIE['active_token'])) {
        return null;
    }

    $activeToken = $_COOKIE['active_token'];

    $query = $pdo->prepare("
        SELECT username, accepted, created_at
        FROM engineers
        WHERE active_token = :active_token
    ");

    try {
        $query->execute([':active_token' => $activeToken]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error while fetching user: " . $e->getMessage());
        return null;
    }

    return $user ?: null;
}

/**
 * Redirect to login if the user is not authenticated.
 * @param string $targetPage The page to redirect to after login.
 */
function requireAuthentication($pdo, $targetPage) {
    $username = getAuthenticatedUser($pdo);
    if ($username === null) {
        header("Location: connect.php?target=" . urlencode($targetPage));
        exit();
    }
}

/**
 * Log out the user by removing the session token.
 */
function logoutUser($pdo) {
    if (isset($_COOKIE['session_token'])) {
        $stmt = $pdo->prepare("UPDATE engineers SET session_token = NULL WHERE session_token = :session_token");
        $stmt->execute([':session_token' => $_COOKIE['session_token']]);
        setcookie("session_token", "", time() - 3600, "/", "", true, true);
    }
}

?>
