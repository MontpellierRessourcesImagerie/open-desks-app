<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: signup.php");
    exit;
}

include("db.php");

function isValidUserName($username) {
    $min_uname_length = 3;
    $max_uname_length = 20;
    $uname_regex = '/^[\p{L}0-9_-]+$/u';

    preg_match_all('/./us', $username, $matches);
    $charCount = count($matches[0]);

    if ($charCount < $min_uname_length || $charCount > $max_uname_length) {
        return false;
    }
    if (!preg_match($uname_regex, $username)) {
        return false;
    }
    return true;
}

function isValidPassword($password) {
    if (strlen($password) < 8) return false;
    if (!preg_match('/[a-z]/', $password)) return false; // At least one lowercase letter
    if (!preg_match('/[A-Z]/', $password)) return false; // At least one uppercase letter
    if (!preg_match('/[0-9]/', $password)) return false; // At least one digit
    if (!preg_match('/[!@#$%^&*()_+!?~.,-]/', $password)) return false; // At least one special character
    if (preg_match('/\s/', $password)) return false; // No spaces

    // Check for repeated characters
    $seenChars = [];
    for ($i = 0, $len = strlen($password); $i < $len; $i++) {
        $char = $password[$i];
        if (isset($seenChars[$char])) {
            return false;
        }
        $seenChars[$char] = true;
    }

    return true;
}

function isValidPasswordConfirm($password, $password_confirm) {
    return $password === $password_confirm;
}

function checkValidity() {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if (!isValidUserName($username)) {
        return ["status" => -1, "message" => "❌ Invalid username"];
    }

    if (!isValidPassword($password)) {
        return ["status" => -1, "message" => "❌ Invalid password"];
    }

    if (!isValidPasswordConfirm($password, $password_confirm)) {
        return ["status" => -1, "message" => "❌ Passwords do not match"];
    }

    return ["status" => 0, "message" => "All good!"];
}

function requestSignUp($pdo, $uname, $pwd) {
    // The first person to create an account is automatically accepted
    $checkStmt = $pdo->query("SELECT COUNT(*) FROM engineers");
    $isEmpty = $checkStmt->fetchColumn() == 0;

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO engineers (username, password_hash, accepted) 
        VALUES (:username, :password_hash, :accepted)
    ");
    
    $stmt->execute([
        ':username' => $uname,
        ':password_hash' => password_hash($pwd, PASSWORD_DEFAULT),
        ':accepted' => $isEmpty ? 1 : 0
    ]);

    return $stmt->rowCount() > 0;
}


$validity = checkValidity();
if ($validity["status"] === 0) {
    $pdo = connect_db();
    $res = requestSignUp($pdo, $_POST['username'], $_POST['password']);
    if ($res) {
        $validity["message"] = "✅ Your sign-up request has been received and is awaiting approval.";
    } else {
        $validity["status"] = 1;
        $validity["message"] = "ℹ️ It looks like you already requested an account with this username...";
    }
    $pdo = null;
}

unset($_POST);

?>


<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign-up confirmation</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Yaldevi">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
    <link rel="stylesheet" type="text/css" href="signup-confirm.css">
</head>
<body>

    <div class="container">
        <h1>Sign-up confirmation</h1>
        <script text="text/javascript">
            var validity = '<?php echo json_encode($validity) ?>';
        </script>
        <script type="text/javascript" src="signup-confirm.js"></script>
    </div>

</body>
</html>
