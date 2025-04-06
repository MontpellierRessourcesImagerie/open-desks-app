<?php

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || !isset($_GET)) { 
    echo "It looks like you reached this page in an unexpected way...";
    header("Location: .");
    exit; 
}

include("db.php");
include("utils.php");
$pdo = connect_db();

$cancel_id = $_GET['id'] ?? null;
$appointment = null;
$error = null;

if ($cancel_id && isSafeCharChain($cancel_id) && strlen($cancel_id) === 6) {
    $stmt = $pdo->prepare("SELECT a.*, s.session_date 
                           FROM appointments a
                           JOIN sessions s ON a.session_id = s.session_date
                           WHERE a.cancel_id = ?");
    $stmt->execute([$cancel_id]);
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        $error = "Invalid cancellation link.";
    }
}

$pdo = null;
unset($_GET);

function isPast($date) {
    return strtotime($date) < strtotime(date('Y-m-d'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cancel Appointment</title>
    <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Yaldevi">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
    <link rel="stylesheet" type="text/css" href="cancel.css">
</head>
</head>
<body>
<div id="cancel-container">
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>

    <?php elseif ($appointment['canceled']): ?>
        <div class="info">This appointment has been canceled. <br>Please book a new one if needed.</div>

    <?php elseif (isPast($appointment['session_id'])): ?>
        <div class="info">This appointment has already taken place and can no longer be canceled.</div>

    <?php else: ?>
        <div class="appointment-details">
            <h2>Appointment Details</h2>
            <p><strong>Date:</strong> <?= htmlspecialchars($appointment['session_id']) ?></p>
            <p><strong>Start time:</strong> <?= htmlspecialchars($appointment['time_start']) ?></p>
            <p><strong>Problem:</strong> <?= nl2br(htmlspecialchars($appointment['problem_description'])) ?></p>
        </div>

        <form method="POST" action="./appointment-cancel.php" onsubmit="return confirmCancel();" id="cancel-form">
            <input type="hidden" name="cancel_id" value="<?= htmlspecialchars($cancel_id) ?>">
            <button type="submit" id="cancel-button">❌ Cancel this appointment</button>
        </form>

        <script>
        function confirmCancel() {
            return confirm("Are you sure you want to cancel this appointment?");
        }
        </script>
    <?php endif; ?>
</div>
</body>
</html>
