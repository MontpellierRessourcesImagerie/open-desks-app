/**
 * This script builds the page confirming an appointment to an open-desk session.
 */

/// Block containing a summary of the appointment (date, time, location).
const info_box = document.getElementById('info_box');
/// Block containing the warning prompt to generate an access QR-code.
const qr_box   = document.getElementById('qr_box');
/// Block containing the cancelation link.
const cancel_box = document.getElementById('cancel');

/**
 * This function generates an ICS file for the appointment.
 * The ICS file is a calendar file that can be imported in most calendar applications.
 */
function generateICS() {
    const splitted = data.split(";");
    const date     = splitted[3].split("-"); // "YY-mm-dd"
    const year     = date[0];
    const month    = date[1];
    const day      = date[2];
    const time     = splitted[1].split(":"); // "HH:MM:SS"
    const hour     = time[0];
    const minute   = time[1];
    const title    = "Open-desk session";
    const location = splitted[2]; // Room name

    const startDate = `${year}${month}${day}T${hour}${minute}00`;
    const endDate = `${year}${month}${day}T${(parseInt(hour) + 1).toString().padStart(2, "0")}${minute}00`;

    const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Custom ICS Generator//EN
BEGIN:VEVENT
UID:${Date.now()}@example.com
DTSTAMP:${startDate}Z
DTSTART:${startDate}Z
DTEND:${endDate}Z
SUMMARY:${title}
LOCATION:${location}
DESCRIPTION:Open-desk session organized by the MRI-CIA (https://www.mri.cnrs.fr).
END:VEVENT
END:VCALENDAR`;

    const blob = new Blob([icsContent], { type: "text/calendar" });
    const url = URL.createObjectURL(blob);
    // create the button to download the ICS file
    const buttons_block = document.getElementById("button-container");
    const button = document.createElement("button");
    button.classList.add("btn");
    button.id = "ics_download";

    const a = document.createElement("a");
    a.href = url;
    a.textContent = "📅 Add to calendar";
    a.download = `${title.replace(/\s+/g, "_")}.ics`;

    button.appendChild(a);
    buttons_block.appendChild(button);
}

/**
 * Called to build an error page, if the PHP execution failed.
 */
function makeError() {
    info_box.className   = "error";
    qr_box.style.display = "none";
    info_box.innerHTML   = "An internal error happened. You should send an email to: <br> 📧 mri-cia@mri.cnrs.fr";
}

function showToast(message) {
    const toast = document.getElementById("toast");
    toast.textContent = message;
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 1500); // 1.5 seconds
}

function addLinkToClipboard(link) {
    navigator.clipboard.writeText(link)
        .then(() => {
            console.log("The link was copied to the clipboard.");
            showToast("Link copied!");
        })
        .catch(err => {
            console.error("Failed to copy the link: ", err);
        });
}

function getRootURL() {
    const loc = window.location;
    const pathParts = loc.pathname.split('/').filter(p => p);
    let root_folder = "";
    for (let i = 0; i < pathParts.length - 1; i++) {
        if (pathParts[i].includes('.')) { break; }
        root_folder += "/" + pathParts[i];
    }
    return loc.protocol + '//' + loc.host + root_folder;
}


/**
 * Called to build a confirmation page, if the appointment was successfully booked.
 */
function makeConfirm() {
    info_box.className = "confirm";
    qr_box.style.display = parseInt(qr_code) > 0 ? "none" : "block";
    let cancel_url = getRootURL() + "/cancel.php?id=" + cancel_id;
    let full_text = "We are pleased to confirm that you successfully booked to the open-desk session on the: ";
    full_text += msg;
    let cancel_text = "<b>🚫 Need to cancel?</b> <br> <span>" 
    cancel_text += "<a href='" + cancel_url + "'>" + cancel_url + "</a>"
    cancel_text += "<button title='Copy link to clipboard' onclick='addLinkToClipboard(" + "\"" + cancel_url + "\"" + ")'><img src='./data/medias/file-copy-fill.svg'></button>";
    cancel_text += "</span>";
    cancel_box.innerHTML = cancel_text;
    cancel_box.style.display = "block";
    info_box.innerHTML = full_text;
    generateICS();
}

/**
 * Called to build a page indicating that the user already booked to this open-desk session.
 */
function makeAlreadyBooked() {
    info_box.className   = "already";
    qr_box.style.display = "none";
    info_box.innerHTML   = "It appears that you already booked to this open-desk session.";
}

/**
 * Grabs the success code from the PHP script and calls the appropriate function.
 * | -1: error
 * |  0: already booked
 * |  1: success
 */
function main() {
    let code = parseInt(success);
    switch (code) {
        case -1:
            makeError();
            break;
        case 1:
            makeConfirm();
            break;
        case 0:
            makeAlreadyBooked();
            break;
        default:
            makeError();
    }
}

main();
