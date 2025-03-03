<?php

header("Content-Type: application/json");
include("db.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel-update-locations.php");
$success = true;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

if (!isset($_POST["location_name"])) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

function validateNewLocation($locationInput) {
    $locationRegex = '/\(.*\)$/';

    if (!preg_match($locationRegex, $locationInput)) {
        return "Location must include a place in parentheses (e.g., 'Room Marcel Doree (CRBM)').";
    }

    return "";
}

$location_name = $_POST["location_name"];
$error = validateNewLocation($location_name);

if ($error !== "") {
    $success = false;
    echo json_encode(["success" => $success, "error" => $error]);
    exit;
}

try {
    $sql = "INSERT IGNORE INTO locations (location_name) VALUES (:location_name)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['location_name' => $location_name]);
} catch (Exception $e) {
    $pdo->rollBack();
    $success = false;
}

$pdo = null;
echo json_encode(["success" => $success]);
exit;
?>