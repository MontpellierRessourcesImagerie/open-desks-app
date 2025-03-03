<?php

include("db.php");
include("connect-ensure.php");

$pdo = connect_db();
requireAuthentication($pdo, "control-panel-download-report.php");

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sessions_appointments.csv');

$output = fopen('php://output', 'w');

fputcsv($output, ["Session Date", "Session Location", "Engineers", "User Email", "First Name", "Last Name", "Institute", "Team", "Problem Description", "Time Start"]);

$query = $pdo->query("
    SELECT 
        s.session_date,
        l.location_name,
        s.n_engineers,
        u.email,
        u.first_name,
        u.last_name,
        u.institute,
        u.team,
        a.problem_description,
        a.time_start
    FROM sessions s
    INNER JOIN appointments a ON s.session_date = a.session_id
    INNER JOIN users u ON a.user_id = u.email
    INNER JOIN locations l ON s.session_location = l.location_id
    ORDER BY s.session_date, a.time_start
");

$query->execute();
$sessions = $query->fetchAll(PDO::FETCH_ASSOC);

$pdo = null;

foreach ($sessions as $row) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
