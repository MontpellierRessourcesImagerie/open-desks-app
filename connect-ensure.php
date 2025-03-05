<?php

/**
 * Makes a PDO request to get the information of the connected user.
 * Checks if the user has a valid session token, and if the local token matches the one in the database.
 * The user is fetched from the DB by the authentication token.
 * The cookie is only reachable from HTTP requests, so it is not possible to access it from JavaScript.
 * 
 * @param PDO $pdo The database connection.
 * @return array|null The user information, or null if the user is not authenticated.
 */
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
 * Redirect to the login page if the user is not authenticated.
 * 
 * @param PDO $pdo The database connection.
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
 * The token is removed from the database and the cookie is deleted.
 * 
 * @param PDO $pdo The database connection.
 */
function logoutUser($pdo) {
    if (isset($_COOKIE['session_token'])) {
        $stmt = $pdo->prepare("UPDATE engineers SET session_token = NULL WHERE session_token = :session_token");
        $stmt->execute([':session_token' => $_COOKIE['session_token']]);
        setcookie("session_token", "", time() - 3600, "/", "", true, true);
    }
}

?>
