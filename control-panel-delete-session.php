<?php

header("Content-Type: application/json");
include("db.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel.php");
$success = true;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

if (!isset($_POST["session_id"])) {
    $success = false;
    echo json_encode(["success" => $success]);
    exit;
}

$session_id = $_POST["session_id"];

try {
    $pdo->beginTransaction();

    $deleteAppointments = $pdo->prepare("DELETE FROM appointments WHERE session_id = :session_id");
    $deleteAppointments->execute([":session_id" => $session_id]);

    $deleteSession = $pdo->prepare("DELETE FROM sessions WHERE session_date = :session_id");
    $deleteSession->execute([":session_id" => $session_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    $success = false;
}

$pdo = null;
echo json_encode(["success" => $success]);
exit;
?>
