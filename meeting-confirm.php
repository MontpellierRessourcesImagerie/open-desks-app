
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("./utils.php");

function isValidFirstName($name) {
    return preg_match("/^[a-zA-ZÀ-ÿ]([a-zA-ZÀ-ÿ\-\s']*[a-zA-ZÀ-ÿ])?$/u", $name);
}

function isValidLastName($name) {
    return preg_match("/^[A-ZÀ-Ÿ]([A-ZÀ-Ÿ\-\s']*[A-ZÀ-Ÿ])?$/u", $name);
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function containsSomething($str) {
    return preg_match("/[a-zA-Z0-9]/", $str);
}

function validReason($str, $sz) {
    return containsSomething($str) && strlen($str) <= $sz;
}

function containsLink($text) {
    $urlRegex = "/(\b(https?|s?ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])|(\bwww\.[\S]+(\b|$))|(\b[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b)/i";
    return preg_match($urlRegex, $text);
}

function validTime($time) {
    global $_TIME_START, $_TIME_END, $_RDV_DURATION;
    $allowed = make_laps($_TIME_START, $_TIME_END, $_RDV_DURATION);
    return in_array($time, $allowed);
}

function checkValidity() {
    if (!isValidFirstName($_POST['first_name'])) { echo $_POST['first_name']; return false; }
    if (!isValidLastName($_POST['last_name'])) { echo $_POST['last_name']; return false; }
    if (!isValidEmail($_POST['email'])) { echo $_POST['email']; return false; }
    if (!containsSomething($_POST['team'])) { echo $_POST['team']; return false; }
    if (!containsSomething($_POST['institute'])) { echo $_POST['institute']; return false; }
    if (!validTime($_POST['appointmentTime'])) { echo $_POST['appointmentTime']; return false; }
    if (!validReason($_POST['reason'], 8192)) { echo $_POST['reason']; return false; }
    if (isset($_POST['dataLink']) && !containsLink($_POST['dataLink'])) { echo $_POST['dataLink']; return false; }
    return true;
}

function addUser($pdo) {
    $email = $_POST['email'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $team = $_POST['team'];
    $institute = $_POST['institute'];

    $sql = "INSERT IGNORE INTO users (email, first_name, last_name, team, institute) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $firstName, $lastName, $team, $institute]);
}

/**
 * Adds an appointment if the user didn't already book one for the same session.
 * Returns 1 if the appointment was added, 0 otherwise.
 */
function addAppointment($pdo) {
    $meetingSql = "INSERT IGNORE INTO appointments 
                (user_id, session_id, problem_description, time_start, images_link) 
                VALUES (:user_id, :session_id, :problem_description, :time_start, :images_link)";

$link = "";
if (isset($_POST['dataLink'])) {
    $link = $_POST['dataLink'];
}    
$stmt = $pdo->prepare($meetingSql);
    $stmt->execute([
        ':user_id'             => $_POST['email'],
        ':session_id'          => $_POST['sessionID'],
        ':time_start'          => $_POST['appointmentTime'],
        ':problem_description' => $_POST['reason'],
        ':images_link'         => $link
    ]);

    return $stmt->rowCount();
}


function addUserAndAppointment($pdo) {
    try {
        addUser($pdo);
        return addAppointment($pdo);
    } catch (Exception $e) {
        echo $e->getMessage();
        error_log("Error while inserting user and appointment: " . $e->getMessage());
        return -1;
    }
    return -1;
}


function getInfos($pdo) {
    global $monthsList; 
    $stmt = $pdo->prepare("SELECT session_location FROM sessions WHERE session_date = ?");
    $stmt->execute([$_POST['sessionID']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row === false) { return ""; }
    $litteral_date = makeLitteralDate($_POST['sessionID']);
    return $litteral_date . ";" . $_POST['appointmentTime'] . ";" . $row['session_location'] . ";" . $_POST['sessionID'];
}

?>