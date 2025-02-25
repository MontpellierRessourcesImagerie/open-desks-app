var min_uname_length = 3;
var max_uname_length = 28;
const uname_regex = /^[\p{L}\p{N}_-]+$/u;

function isValidUserName(username) {
    if (username.length <= min_uname_length) { return "The username is too short."; }
    if (username.length >= max_uname_length) { return "The username is too long."; }
    if (uname_regex.test(username)) {
        return "";
    }
    return "The username contains invalid characters. The characters allowed are: a-z, A-Z, 0-9, _ and -";
}

function isValidPassword(password) {
    if (password.length < 8) {
        return "The password must be at least 8 characters long.";
    }
    if (!/[a-z]/.test(password)) {
        return "The password must contain at least one lowercase letter.";
    }
    if (!/[A-Z]/.test(password)) {
        return "The password must contain at least one uppercase letter.";
    }
    if (!/[0-9]/.test(password)) {
        return "The password must contain at least one digit.";
    }
    if (!/[!@#$%^&*()_+!?~.,-]/.test(password)) {
        return "The password must contain at least one special character (@#$%^&*()_+!?~.,-).";
    }
    if (/\s/.test(password)) {
        return "The password must not contain spaces.";
    }

    // Check for repeated characters using a Set
    let seenChars = new Set();
    for (let char of password) {
        if (seenChars.has(char)) {
            return `The password must not contain repeated characters (found '${char}').`;
        }
        seenChars.add(char);
    }

    return ""; // Password is valid
}

function setError(isError, message) {
    let error_box = document.getElementById("error_box");
    let error_msg = document.getElementById("error_msg");
    let uname     = document.getElementById("username");
    if (isError) {
        error_box.style.display = "block";
        error_msg.textContent = message;
        uname.style.borderColor = "red";
        uname.style.backgroundColor = "rgba(255, 0, 0, 0.2)";
    } else {
        error_box.style.display = "none";
        uname.style.borderColor = "black";
        uname.style.backgroundColor = "white";
    }
}

document.querySelector(".login-form").addEventListener("submit", async function(event) {
    event.preventDefault();
    let usernameInput = document.getElementById("username");
    let password = document.getElementById("password").value;
    let confirm_password = document.getElementById("password_confirm").value;
    let msg = "";

    msg = isValidUserName(usernameInput.value);
    if (msg.length > 0) {
        setError(true, msg);
        return;
    }

    let isFree = await checkUsername(usernameInput.value);
    if (!isFree) {
        setError(true, "This username is already taken.");
        return;
    }

    msg = isValidPassword(password);
    if (msg.length > 0) {
        setError(true, msg);
        return;
    }

    if (password !== confirm_password) {
        setError(true, "The passwords do not match.");
        return;
    }
    
    event.target.submit();
});


async function checkUsername(username) {
    if (isValidUserName(username).length > 0) { return false; }

    try {
        let response = await fetch("check-username.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "username=" + encodeURIComponent(username)
        });
        let data = await response.json();
        return data.status !== "taken";
    } catch (error) {
        console.error("Error checking username:", error);
        return false;
    }
}

document.getElementById("username").addEventListener("input", function() {
    let username = this.value.trim();
    let msg = isValidUserName(username);

    setError(msg.length > 0, msg);
    if (msg.length > 0) {
        return;
    }

    // Send an AJAX request to check if the username exists
    fetch("./check-username.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "username=" + encodeURIComponent(username)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "taken") {
            setError(true, "This username is already taken.");
        } else {
            setError(false, "");
        }
    })
    .catch(error => console.error("Error:", error));
});

document.getElementById("password").addEventListener("input", function() {
    let password = this.value;
    let msg = isValidPassword(password);
    setError(msg.length > 0, msg);
});

document.getElementById("password_confirm").addEventListener("input", function() {
    let password = document.getElementById("password").value;
    let confirm_password = this.value;
    let msg = password !== confirm_password ? "The passwords do not match." : "";
    setError(msg.length > 0, msg);
});