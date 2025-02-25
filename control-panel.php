<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");
include("extract-sessions.php");
include("connect-ensure.php");

/**
 * Fetches the appointments from the database.
 * Returns a dictionary with the session id as key and the appointments as value.
 */
function get_detailed_appointments($pdo) {
    $sql = "SELECT 
                a.id, 
                a.session_id, 
                a.problem_description, 
                a.time_start, 
                a.images_link, 
                u.first_name, 
                u.last_name, 
                u.institute, 
                u.team,
                u.email,
                COUNT(a.user_id) OVER(PARTITION BY a.user_id) AS total_appointments
            FROM appointments a
            JOIN users u ON a.user_id = u.email
            ORDER BY a.session_id ASC, a.time_start ASC;";

    $stmt = $pdo->query($sql);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $result = [];

    foreach ($appointments as $appointment) {
        $session_id = $appointment['session_id'];

        if (!isset($result[$session_id])) {
            $result[$session_id] = [];
        }

        $result[$session_id][] = [
            'first_name'         => $appointment['first_name'],
            'last_name'          => $appointment['last_name'],
            'institute'          => $appointment['institute'],
            'team'               => $appointment['team'],
            'email'              => $appointment['email'],
            'problem_description'=> $appointment['problem_description'],
            'time_start'         => $appointment['time_start'],
            'images_link'        => $appointment['images_link'],
            'total_appointments' => $appointment['total_appointments']
        ];
    }

    return $result;
}

$pdo = connect_db();

// Ensures that the user is connected.
requireAuthentication($pdo, "control-panel.php");

$sessionData    = json_encode(get_sessions_map($pdo, true));
$sessionDetails = json_encode(get_detailed_appointments($pdo));
$userData       = json_encode(getAuthenticatedUser($pdo));

/**
 * Used to prevent the user to create a session in the past.
 */
function isDateAnterior($day, $month, $year) {
    $givenDate = new DateTime("$year-$month-$day");
    $today = new DateTime();
    return $givenDate <= $today;
}


$pdo = null;

?>


<html>
<head>
    <title>OD Control Panel</title>
    <link rel="stylesheet" type="text/css" href="control-panel-style-desktop.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lilita+One">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Yaldevi">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
</head>

<body>
    <div id="navbar">
        <div id="logo-container">
            <a href="."><img src="./data/medias/logo-mri.png" alt="Home"></a>
        </div>
        <div id="nb_buttons">
            <div class="nb_btn" id="user-info"></div>
            <button class="nb_btn" id="download"><a href="control-panel-download-report.php">Download CSV</a></button>
            <button class="nb_btn" id="logout">
                <img alt="onoff" src="./data/medias/on-off.png" id="onoff" />
            </button>
        </div>
    </div>

    <div id="title">
        <span>Open-desks</span>
        <br>
        <span>control panel</span>
    </div>

    <h2>Active sessions</h2>

    <div id="sessions_list"></div>
    <!--<table id="sessions">
        <tr>
            <td>Date</td>
            <td>Location</td>
            <td># engineers</td>
            <td>Delete</td>
        </tr>
    </table>-->

    <h2>Appointments</h2>

    <div id="appointments">
    </div>

    <h2>New session</h2>

    <div id="new_session">
        <form id="new_session_form" action="control-panel-confirm-session.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="session_date">Date</label>
                <input type="date" id="session_date" name="session_date" required>
            </div>
            <div class="input-group">
                <label for="location">Location</label>
                <input type="text" id="location" name="location" required>
            </div>
            <div class="input-group">
                <label for="nb_engineers">Number of engineers</label>
                <input type="number" id="nb_engineers" name="nb_engineers" min="1" required>
            </div>
            <button id="button_new_session" type="submit">Create</button>
        </form>
        <span id="errorBox">
        </span>
    </div>

    <h2>Users management</h2>

    <div id="users-list">
    </div>

    <script>
        const sessionData    = <?php echo $sessionData; ?>;
        const sessionDetails = <?php echo $sessionDetails; ?>;
        const userData       = <?php echo $userData; ?>;
    </script>
    <script type="text/javascript" src="control-panel-finish.js"></script>

</body>
</html>