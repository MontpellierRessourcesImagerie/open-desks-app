<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");

main($pdo);
$pdo = null;

function validateDate($dateInput) {
    $selectedDate = strtotime($dateInput);
    $today = strtotime(date('Y-m-d'));
    
    if ($selectedDate < $today) {
        return "The date must be today or in the future.";
    }
    return "";
}

function validateLocation($locationInput) {
    $locationRegex = "/\(.*\)$/";
    if (!preg_match($locationRegex, $locationInput)) {
        return "Location must include a place in parentheses (e.g., 'Room Marcel Doree (CRBM)').";
    }
    return "";
}

function validateEngineers($engineersInput) {
    if (!is_numeric($engineersInput) || intval($engineersInput) < 1) {
        return "The number of engineers must be at least 1.";
    }
    return "";
}

function validateSession($dateInput, $locationInput, $engineersInput) {
    $date      = validateDate($dateInput);
    if ($date !== "") { return $date; }
    $location  = validateLocation($locationInput);
    if ($location !== "") { return $location; }
    $engineers = validateEngineers($engineersInput);
    if ($engineers !== "") { return $engineers; }
    return "";
}

function main($pdo) {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        header("Location: .");
        exit;
    }
    
    $date      = $_POST['session_date'];
    $location  = $_POST['location'];
    $engineers = $_POST['nb_engineers'];

    $error = validateSession($date, $location, $engineers);
    if ($error !== "") {
        header("Location: control-panel.php?error=" . urlencode($error));
        exit;
    }

    $stmt = $pdo->prepare("INSERT IGNORE INTO sessions (session_date, session_location, n_engineers) VALUES (:date, :location, :engineers)");
    $stmt->execute([':date' => $date, ':location' => $location, ':engineers' => $engineers]);

    header("Location: control-panel.php");
    exit;
}

?>