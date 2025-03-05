<?php

/**
 * Gets the announcement message from the database.
 * This table is supposed to contain only one row for each column (hence the id = 1).
 * 
 * @param PDO $pdo
 * @return string
 */
function fetch_announcement($pdo) {
    $sql = "SELECT message FROM global WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

/**
 * Creates the announcement div with the message from the database.
 * If the message is empty, returns an empty string (== no supplemental HTML).
 * 
 * @param PDO $pdo
 * @return string
 */
function make_announcement($pdo) {
    $message = fetch_announcement($pdo);
    if ($message === false || $message === null || $message === "") {
        return "";
    }
    $message = htmlspecialchars($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $open = "<div id='announcement'><span>";
    $close = "</span></div>";
    return $open . $message . $close;
}

/**
 * Updates or creates an announcement message in the database.
 * If the message is empty, it will be set to NULL in the database.
 */
function update_announcement($pdo, $message) {
    $message = ($message === "") ? null : $message;
    $stmt = $pdo->query("SELECT COUNT(*) FROM global");
    $rowCount = $stmt->fetchColumn();

    if ($rowCount > 0) {
        $sql = "UPDATE global SET message = :message WHERE id = 1";
    } else {
        $sql = "INSERT INTO global (id, message) VALUES (1, :message)";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":message", $message, $message === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
    $stmt->execute();
    
    return $stmt->rowCount();
}


?>