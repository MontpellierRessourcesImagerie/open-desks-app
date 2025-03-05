<?php

include("db.php");
include("utils.php");
include("connect-ensure.php");

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    header("Location: .");
    exit;
}

$fields = [
    'session_date',
    'location',
    'nb_engineers'
];

if (!requiredData($fields)) {
    header("Location: control-panel.php?error=" . urlencode("Missing data."));
    exit;
}

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
main($pdo);
$pdo = null;

/**
 * Verifies that the date is today or in the future.
 * 
 * @param string $dateInput The date to validate.
 * @return string An error message if the date is invalid, an empty string otherwise.
 */
function validateDate($dateInput) {
    $selectedDate = strtotime($dateInput);
    $today = strtotime(date('Y-m-d'));
    
    if ($selectedDate < $today) {
        return "The date must be today or in the future.";
    }
    return "";
}

/**
 * Verifies that the location ID is a non-null positive integer.
 * 
 * @param string $locationInput The location ID to validate.
 * @return string An error message if the location ID is invalid, an empty string otherwise.
 */
function validateLocation($locationInput) {
    $locationID = intval($locationInput);
    if ($locationID <= 0) {
        return "The location ID is invalid.";
    }
    return "";
}

/**
 * Verifies that the number of engineers is a non-null positive integer.
 */
function validateEngineers($engineersInput) {
    if (!is_numeric($engineersInput) || intval($engineersInput) < 1) {
        return "The number of engineers must be at least 1.";
    }
    return "";
}

/**
 * Validates the session data.
 * Applies sequencially the validation functions (validateDate, validateLocation, validateEngineers).
 * 
 * @param string $dateInput The date to validate.
 * @param string $locationInput The location ID to validate.
 * @param string $engineersInput The number of engineers to validate.
 * @return string An error message if the session data is invalid, an empty string otherwise.
 */
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