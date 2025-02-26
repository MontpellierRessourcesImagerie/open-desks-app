
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("utils.php");

/**
 * Checks if the first name is valid.
 * It implies that it only contains letters, including accentuated ones and hyphens.
 * 
 * @param string $name The first name to check.
 * @return bool True if the first name is valid, false otherwise.
 */
function isValidFirstName($name) {
    return preg_match("/^[a-zA-ZÀ-ÿ]([a-zA-ZÀ-ÿ\-\s']*[a-zA-ZÀ-ÿ])?$/u", $name);
}

/**
 * Checks if the last name is valid.
 * It implies that it only contains capital letters, including accentuated ones and hyphens.
 * 
 * @param string $name The last name to check.
 * @return bool True if the last name is valid, false otherwise.
 */
function isValidLastName($name) {
    return preg_match("/^[A-ZÀ-Ÿ]([A-ZÀ-Ÿ\-\s']*[A-ZÀ-Ÿ])?$/u", $name);
}

/**
 * Checks if the email is valid.
 * 
 * @param string $email The email to check.
 * @return bool True if the email is valid, false otherwise.
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Checks if the string contains at least one letter or digit.
 * 
 * @param string $str The string to check.
 * @return bool True if the string contains at least one letter or digit, false otherwise.
 */
function containsSomething($str) {
    return preg_match("/[a-zA-Z0-9]/", $str);
}

/**
 * Checks if the reason is valid.
 * It implies that it contains at least one letter or digit and is shorter than 8192 characters.
 * 
 * @param string $str The reason to check.
 * @param int $sz The maximum size of the reason.
 * @return bool True if the reason is valid, false otherwise.
 */
function isValidReason($str, $sz) {
    return containsSomething($str) && strlen($str) <= $sz;
}

/**
 * Checks if the string is a valid link with the HTTP(S) or (S)FTP protocol.
 * 
 * @param string $text The string to check.
 * @return bool True if the string is a link, false otherwise.
 */
function isValidLink($text) {
    $urlRegex = "/(\b(https?|s?ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])|(\bwww\.[\S]+(\b|$))|(\b[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b)/i";
    return preg_match($urlRegex, $text);
}

/**
 * Checks if the time is valid.
 * It is performed by checking if the string is present in the table produced by 'make_laps'.
 * 
 * @param string $time The time to check.
 * @return bool True if the time is valid, false otherwise.
 */
function isValidTime($time) {
    global $_TIME_START, $_TIME_END, $_RDV_DURATION;
    $allowed = make_laps($_TIME_START, $_TIME_END, $_RDV_DURATION);
    return in_array($time, $allowed);
}

/**
 * Runs all the validity checks on the content of $_POST.
 * If a field is invalid, it will be echoed and the function will return false.
 * 
 * @return bool True if all the fields are valid, false otherwise.
 */
function checkValidity() {
    if (!isValidFirstName($_POST['first_name']))                           { echo $_POST['first_name']; return false; }
    if (!isValidLastName($_POST['last_name']))                             { echo $_POST['last_name']; return false; }
    if (!isValidEmail($_POST['email']))                                    { echo $_POST['email']; return false; }
    if (!containsSomething($_POST['team']))                                { echo $_POST['team']; return false; }
    if (!containsSomething($_POST['institute']))                           { echo $_POST['institute']; return false; }
    if (!isValidTime($_POST['appointmentTime']))                           { echo $_POST['appointmentTime']; return false; }
    if (!isValidReason($_POST['reason'], 8192))                            { echo $_POST['reason']; return false; }
    if (isset($_POST['dataLink']) && !isValidLink($_POST['dataLink']))     { echo $_POST['dataLink']; return false; }
    if (isset($_POST['company']) && !containsSomething($_POST['company'])) { echo $_POST['company']; return false; }
    return true;
}

/**
 * Adds a user if it doesn't already exist.
 * Some user can already exist if they booked an appointment before.
 */
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
 * @return int 1 if the appointment was added, 0 otherwise.
 */
function addAppointment($pdo) {
    $meetingSql = "INSERT IGNORE INTO appointments 
                (user_id, session_id, problem_description, time_start, images_link) 
                VALUES (:user_id, :session_id, :problem_description, :time_start, :images_link)";

    $link = isset($_POST['dataLink']) ? $_POST['dataLink'] : "";
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

/**
 * Adds a user and an appointment.
 * @return int 1 if the user and the appointment were added, 
 *             0 if the user already booked for this session,
 *             -1 if an error occurred.
 */
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

/**
 * Fetches the session date and location.
 * This data is bundled in a string with the following format:
 * "session_date;appointmentTime;session_location;sessionID".
 * The date is in a "literal" format, e.g., "Monday, 1st of January 2021".
 * The appointment time is in the format "HH:MM".
 * @return string The session date, time, location and ID.
 */
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