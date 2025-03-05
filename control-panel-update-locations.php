<?php

header("Content-Type: application/json");
include("db.php");
include("utils.php");
include("connect-ensure.php");

if (($_SERVER["REQUEST_METHOD"] !== "POST") || !isset($_POST)) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$fields = ["location_name"];
if (!requiredData($fields)) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$success = true;

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
unset($_POST);
echo json_encode(["success" => $success]);
exit;
?>
