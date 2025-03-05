/**
 * This script builds the page confirming an appointment to an open-desk session.
 */

/// Block containing a summary of the appointment (date, time, location).
const info_box = document.getElementById('info_box');
/// Block containing the warning prompt to generate an access QR-code.
const qr_box   = document.getElementById('qr_box');

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

/**
 * Called to build a confirmation page, if the appointment was successfully booked.
 */
function makeConfirm() {
    info_box.className   = "confirm";
    qr_box.style.display = parseInt(qr_code) > 0 ? "none" : "block";
    info_box.innerHTML   = "We are pleased to confirm that you successfully booked to the open-desk session on the: " + msg ;
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
