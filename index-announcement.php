<?php

function fetch_announcement($pdo) {
    $sql = "SELECT message FROM global WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function make_announcement($pdo) {
    $message = fetch_announcement($pdo);
    if ($message === false || $message === null || $message === "") {
        return "";
    }
    $open = "<div id='announcement'><span>";
    $close = "</span></div>";
    return $open . $message . $close;
}

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