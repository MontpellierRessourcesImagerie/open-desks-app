<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("db.php");
include("appointment-utils.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST)) { 
    echo "It looks like you reached this page in an unexpected way...";
    header("Location: .");
    exit; 
}

$fields = [
    'first_name', 
    'last_name', 
    'email', 
    'team', 
    'institute', 
    'appointmentTime', 
    'reason',
    'sessionID',
    'company'
];

if (!requiredData($fields)) { exit; }
if (!checkValidity())       { exit; }

$pdo     = connect_db();
$success = addUserAndAppointment($pdo);
$infos   = getInfos($pdo);

$splitted = explode(";", $infos);
$date = "<div id='date_block'>" . "<span id='date'>🗓️ &nbsp;" . $splitted[0] . "</span><br>" . "<span id='time'>🕒 &nbsp;" . $splitted[1] . "</span><br>" . "<span id='place'>📍 &nbsp;" . $splitted[2] . "</span>" . "</div>";
$notFromCampus = isset($_POST['notFromCampus']);

$pdo = null;
unset($_POST);

?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open-Desk Session Confirmation</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Yaldevi">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
    <link rel="stylesheet" type="text/css" href="appointment-confirm.css">
</head>
<body>

<div class="container">
    <h1>Open-Desk Session Confirmation</h1>

    <div id="info_box" class="error">
        It looks like you reached that page in an unexpected way...
    </div>

    <div id="qr_box">
        <strong>⚠️ Important:</strong> You indicated that you are not from the "Route de Mende" campus. To be allowed to enter the campus, you will need to request a QR-code:
        <br>
        <ul>
            <li><b>Plateau:</b> CRBM - MRI - UMR5237 (RDM)</li>
            <li><b>Valideur:</b> MRI - Volker BAECKER</li>
        </ul>
        <br>
        <a target="_blank" href="https://duo.dr13.cnrs.fr/visiteur/index" id="qr_button" class="btn">Generate a QR-Code</a>
    </div>

    <div id="button-container">
        <button onclick="window.location.href='.'" id="home_btn" class="btn">🏠 Home</button>
    </div>

    <script text="text/javascript">
        const success = '<?php echo $success; ?>';
        const msg     = "<?php echo $date; ?>";
        const qr_code = '<?php echo $notFromCampus; ?>';
        const data    = "<?php echo $infos; ?>";
    </script>
    <script type="text/javascript" src="appointment-confirm.js"></script>
</div>

</body>
</html>
