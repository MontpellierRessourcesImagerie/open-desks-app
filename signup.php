<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OD Sign-up</title>
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&family=Yaldevi:wght@300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./signup-connect/connect-style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
</head>
<body>
    <div class="login-container">
        <form class="login-form" action="signup-confirm.php" method="POST">
            <h2>Sign-Up</h2>
            <div class="input-group">
                <label for="username">User Name</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="password_confirm">Confirm Password</label>
                <input type="password" id="password_confirm" name="password_confirm" required>
            </div>
            <button type="submit">Confirm</button>
            <a href="connect.php"><p class="signup-link">Already have an account? Log in</p></a>
            <div id="error_box">
                <span id="error_msg"></span>
            </div>
        </form>
    </div>
    <script type="text/javascript" src="signup-validity.js"></script>
</body>