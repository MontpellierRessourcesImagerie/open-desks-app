<?php

header("Content-Type: application/json");
include("db.php");
include("connect-ensure.php");

/** 
 * Get the list of engineers from the database.
 * Includes the username, accepted status and creation date of the last known token.
 * An empty list is returned in case of error.
 * 
 * @param PDO $pdo
 * @return array Array of engineers.
 */
function getEngineersList($pdo) {
    try {
        $query = $pdo->query("
            SELECT username, accepted, created_at 
            FROM engineers
        ");
        $engineers = $query->fetchAll(PDO::FETCH_ASSOC);
        return $engineers;
    } catch (Exception $e) {
        return [];
    }
    return [];
}

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$engineers = getEngineersList($pdo);
$pdo = null;
echo json_encode($engineers);

?>
