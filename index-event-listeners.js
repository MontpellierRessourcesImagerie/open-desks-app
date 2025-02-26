/**
     * A first name is valid if it contains only letters, spaces, hyphens and apostrophes.
     */
function isValidFirstName(name) {
    const regex = /^[a-zA-Z\u00C0-\u024F\u1E00-\u1EFF]([a-zA-Z\u00C0-\u024F\u1E00-\u1EFF\-\s']*[a-zA-Z\u00C0-\u024F\u1E00-\u1EFF])?$/u;
    return regex.test(name);
}

/**
 * Checks if the email address is valid.
 */
function isValidEmail(email) {
    const regex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|.(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return email.match(regex);
}

/**
 * Verifies if the string contains at least one alphanumeric character.
 */
function containsSomething(str) {
    return /[a-zA-Z0-9]/.test(str);
}

/**
 * Verifies that the text is not empty and contains at most 'sz' characters.
 */
function validReason(str, sz) {
    return containsSomething(str) && str.length <= sz;
}

/**
 * Checks if the text contains a link.
 * This is a very basic check and will not catch all URLs.
 */
function containsLink(text) {
    const urlRegex = /(\b(https?|s?ftp):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])|(\bwww\.[\S]+(\b|$))|(\b[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}\b)/ig;
    return urlRegex.test(text);
}

/**
 * Checks if the time is in the format 'hh:mm'.
 */
function isValidTime(time_txt) {
    const timeRegex = /^\d{2}:\d{2}$/;
    return timeRegex.test(time_txt);
}

/**
 * Checks if the URL is valid.
 */
function isValidURL(url, no_data) {
    if (no_data) { return true; }
    const regex = /^(?:(s?ftp|https?):\/\/)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/;
    return url.match(regex);
}

function validateForm() {
    let first_name = document.getElementById('first_name').value;
    let last_name  = document.getElementById('last_name').value;
    let email      = document.getElementById('email').value;
    let team       = document.getElementById('team').value;
    let institute  = document.getElementById('institute').value;
    let app_time   = document.getElementById('appointmentTime').value;
    let reason     = document.getElementById('reason').value;
    let fs_link    = document.getElementById('dataLink').value;
    let no_d       = document.getElementById('noData').checked;

    if (!isValidFirstName(first_name)) { return "First name missing."; }
    if (!isValidFirstName(last_name))  { return "Last name missing."; }
    if (!isValidEmail(email))          { return "E-mail address missing or mis-formatted."; }
    if (!containsSomething(team))      { return "Team name empty or invalid."; }
    if (!containsSomething(institute)) { return "Institute name empty or invalid."; }
    if (!validReason(reason, 8192))    { return "Problem description missing or too long."; }
    if (!isValidTime(app_time))        { return "Invalid time slot selected."; }
    if (!isValidURL(fs_link, no_d))    { return "Invalid data link. Check 'I will come with a USB drive' if this is intended."; }

    return "";
}

document.addEventListener("DOMContentLoaded", function () {
    
    document.getElementById('first_name').addEventListener('input', (event) => {
        let t = event.target;
        t.style.backgroundColor = isValidFirstName(t.value) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('last_name').addEventListener('input', (event) => {
        let t = event.target;
        let val = t.value.toUpperCase();
        t.value = val;
        t.style.backgroundColor = isValidFirstName(val) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('email').addEventListener('input', (event) => {
        let t = event.target;
        t.style.backgroundColor = isValidEmail(t.value) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('reason').addEventListener('input', (event) => {
        let t = event.target;
        t.style.backgroundColor = validReason(t.value, 8192) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('team').addEventListener('input', (event) => {
        let t = event.target;
        t.style.backgroundColor = containsSomething(t.value) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('institute').addEventListener('input', (event) => {
        let t = event.target;
        t.style.backgroundColor = containsSomething(t.value) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('dataLink').addEventListener('input', (event) => {
        let t = event.target;
        let no_d = document.getElementById('noData').checked;
        t.style.backgroundColor = isValidURL(t.value, no_d) ? "#ffffffff" : _ERROR_COLOR;
    });

    document.getElementById('noData').addEventListener('change', (event) => {
        let t = event.target;
        let dl = document.getElementById('dataLink');
        let dl_lbl = document.getElementById('lbl_data_link');
        dl.disabled = t.checked;
        if (t.checked) {
            dl.style.backgroundColor = "#eeeeeeff";
            dl_lbl.style.opacity = "0.3";
        } else {
            dl.style.backgroundColor = isValidURL(dl.value, t.checked) ? "#ffffffff" : _ERROR_COLOR;
            dl_lbl.style.opacity = "1.0";
        }
    });

    document.getElementById('booking_form').addEventListener('submit', function(event) {
        event.preventDefault();
        let err = document.getElementById("sub_failed");
        let v = validateForm();

        if (v === "") {
            err.style.display = "none";
            document.getElementById('booking_form').submit();
        } else {
            err.style.display = "block";
            err.innerHTML = "A required field is either missing or incorrect: " + v;
        }
    });

    remove_scripts([
        'php_sessions_data', 
        'finish-js',
        'events-js'
    ]);
});
