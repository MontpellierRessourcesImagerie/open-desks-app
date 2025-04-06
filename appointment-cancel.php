<?php
include("db.php");
include("utils.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "It looks like you reached this page in an unexpected way...";
    header("Location: .");
    exit;
}

$cancel_id = $_POST['cancel_id'] ?? null;

if ($cancel_id && isSafeCharChain($cancel_id) && strlen($cancel_id) === 12) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT a.*, s.session_date
                            FROM appointments a
                            JOIN sessions s ON a.session_id = s.session_date
                            WHERE a.cancel_id = ?");
    $stmt->execute([$cancel_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment && !$appointment['canceled'] && strtotime($appointment['session_id']) >= strtotime(date('Y-m-d'))) {
        $update = $pdo->prepare("UPDATE appointments
                                    SET canceled = TRUE, has_come = FALSE
                                    WHERE cancel_id = ?");
        $update->execute([$cancel_id]);
    }
    $pdo = null;
    unset($_POST);
}

header("Location: cancel.php?id=" . urlencode($cancel_id));
exit;
?>
