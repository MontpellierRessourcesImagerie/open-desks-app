<?php
include("utils.php");

$target = isset($_GET['target']) ? htmlspecialchars($_GET['target']) : '.';
$errorMessage = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
if (!isSafeFile($target)) { $target = '.'; }

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OD Connection</title>
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Yaldevi:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./connect-style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
</head>

<body>
    <div class="login-container">
        <form action="connect-confirm.php" method="POST" enctype="multipart/form-data" class="login-form">
            <h2>Connection</h2>
            <div class="input-group">
                <label for="username">User Name</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <input type="hidden" name="target" value="<?php echo $target; ?>">
            <button type="submit">Connection</button>
            <a href="signup.php"><p class="signup-link">I don't have an account yet</p></a>
        </form>
        <div id="error_box">
            <span id="error_msg"><?php echo $errorMessage; ?></span>
        </div>
    </div>
    <script type="text/javascript">
        const error = '<?php echo $errorMessage; ?>';
        const err_b = document.getElementById('error_box');
        err_b.style.display = error ? 'block' : 'none';
    </script>
</body>
