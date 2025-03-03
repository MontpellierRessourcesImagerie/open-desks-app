const _MONTHS_LIST = [
    "---",
    "January", 
    "February", 
    "March", 
    "April", 
    "May", 
    "June", 
    "July", 
    "August", 
    "September", 
    "October", 
    "November", 
    "December"
]

main();

function main() {
    if (typeof(sessionData) === "undefined") {
        console.error("No session data found.");
        return;
    }
    if (typeof(sessionDetails) !== "object") {
        console.error("No appointments details found.");
        return;
    }
    if (typeof(userData) !== "object") {
        console.error("No user data found.");
        return;
    }
    if (typeof(locations) !== "object") {
        console.error("No locations data found.");
        return;
    }
    unpack_sessions(sessionData, sessionDetails);
    showUserDetails(userData);
    fetchUsers();
    showLocations(locations);
}

function showLocations(locs) {
    let target = document.getElementById("location");
    target.innerHTML = "";
    // Loc is a map ID => Name. The name must be displayed and the ID should be the value
    for (const [id, name] of Object.entries(locs)) {
        let option = document.createElement("option");
        option.value = id;
        option.textContent = name;
        target.appendChild(option);
    }
}

function showUserDetails(usrInfo) {
    let uname = document.getElementById("user-info");
    uname.textContent = usrInfo['username'];
}

function createAppointmentBlock(data) {
    // Create main container
    let appointmentDiv = document.createElement("div");
    appointmentDiv.classList.add("appointment");

    // Contact info
    let contactP = document.createElement("p");
    contactP.classList.add("u_attr");

    let contactLabel = document.createElement("span");
    contactLabel.classList.add("u_contact");
    contactLabel.textContent = "👤 Contact: ";

    let nameSpan = document.createElement("span");
    nameSpan.classList.add("u_name");
    nameSpan.textContent = `${data['first_name']} ${data['last_name']}`;

    let emailLink = document.createElement("a");
    emailLink.href = `mailto:${data['email']}`;
    emailLink.target = "_blank";

    let emailSpan = document.createElement("span");
    emailSpan.classList.add("email");
    emailSpan.textContent = `(${data['email']})`;

    let visitsSpan = document.createElement("span");
    visitsSpan.classList.add("u_visits");
    visitsSpan.innerHTML = data['total_appointments'] > 1 ? `(x${data['total_appointments']})` : `(🌟)`;

    emailLink.appendChild(emailSpan);
    contactP.append(contactLabel, nameSpan, " ", emailLink, " ", visitsSpan);

    // Affiliation info
    let instituteP = document.createElement("p");
    instituteP.classList.add("u_attr", "institute");

    let affiliationLabel = document.createElement("span");
    affiliationLabel.classList.add("u_affiliation");
    affiliationLabel.textContent = "🏢 Affiliation: ";

    let instituteSpan = document.createElement("span");
    instituteSpan.classList.add("u_institute");
    instituteSpan.textContent = data['institute'];

    let teamSpan = document.createElement("span");
    teamSpan.classList.add("u_team");
    teamSpan.textContent = data['team'];

    instituteP.append(affiliationLabel, instituteSpan, " (", teamSpan, ")");

    // Time info
    let timeP = document.createElement("p");
    timeP.classList.add("time");
    timeP.textContent = `⏰ ${data['time_start']}`;

    // Data section
    let dataP = document.createElement("p");
    let dataLabel = document.createElement("span");
    dataLabel.classList.add("u_data");
    dataLabel.textContent = "📌 Data: ";

    let dataSpan = document.createElement("span");
    dataSpan.classList.add("u_link");

    if (data['images_link']) {
        let dataLink = document.createElement("a");
        dataLink.href = data['images_link'];
        dataLink.target = "_blank";
        dataLink.classList.add("data_link");
        dataLink.innerHTML = "🔗 " + data['images_link'];
        dataSpan.appendChild(dataLink);
    } else {
        dataSpan.classList.add("no_data");
        dataSpan.textContent = "🚫 no data";
    }

    dataP.append(dataLabel, dataSpan);

    // Problem description
    let problemP = document.createElement("p");
    problemP.classList.add("problem");

    let problemSpan = document.createElement("span");
    problemSpan.classList.add("u_pb_description");
    problemSpan.textContent = data['problem_description'];

    problemP.appendChild(problemSpan);

    // Append all sections to the main appointment div
    appointmentDiv.append(contactP, instituteP, timeP, dataP, problemP);

    return appointmentDiv;
}

function reset_appointments(root, details) {
    root.innerHTML = "";
    if (details === undefined) {
        root.innerHTML = "<div class='nothing'>🔍 Nothing here!</div>";
    } else {
        details.forEach(data => {
            root.appendChild(createAppointmentBlock(data));
        });
    }
}

function addSession(root, day, month, year, location, n_engineers, session_id, appointments) {
    if (!root || !(root instanceof HTMLTableElement)) {
        console.error("Invalid table reference.");
        return;
    }

    let row = root.insertRow(-1);
    let details = document.getElementById('appointments');

    row.addEventListener("click", function() {
        // Reset the color of every row
        for (let i = 0; i < root.rows.length; i++) {
            root.rows[i].style.backgroundColor = "";
            root.rows[i].style.fontWeight = "";
        }
        this.style.backgroundColor = "rgba(0, 255, 0, 0.2)";
        this.style.fontWeight = "bold";
        reset_appointments(details, appointments);
    });

    let dateCell = row.insertCell(0);
    dateCell.textContent = `${day.toString().padStart(2, '0')} ${_MONTHS_LIST[month]} ${year}`;

    let locationCell = row.insertCell(1);
    locationCell.textContent = location;

    let engineersCell = row.insertCell(2);
    engineersCell.textContent = n_engineers;

    let deleteCell = row.insertCell(3);
    let deleteButton = document.createElement("button");
    deleteButton.textContent = "🗑️ Delete";
    deleteButton.classList.add("btn-delete");
    deleteButton.onclick = function () {
        removeSession(session_id);
    };

    deleteCell.appendChild(deleteButton);
}

function is_today_or_later(day, month, year) {
    let today = new Date();
    let session_date = new Date(year, month - 1, day);
    return session_date >= today;
}

function unpack_sessions(sessions, appointments) {
    let root = document.getElementById('sessions_list');
    root.innerHTML = "";

    if (sessions.length === 0) {
        root.innerHTML = "<div class='nothing'>🔍 Nothing here!</div>";
        return;
    }

    let table = document.createElement("table");
    table.id = "sessions";
    root.appendChild(table);

    let thead = document.createElement("thead");
    thead.innerHTML = `
        <tr>
            <th>Date</th>
            <th>Location</th>
            <th># engineers</th>
            <th>Delete</th>
        </tr>
    `;
    table.appendChild(thead);

    let index = -1;
    let current = 0;
    for (const [session_id, info] of Object.entries(sessions)) {
        let day   = info['day'];
        let month = info['month'];
        let year  = info['year'];

        addSession(
            table, 
            day,
            month,
            year,
            info['location'],
            info['n_engineers'],
            session_id,
            appointments[session_id]
        );
        if (index == -1 && is_today_or_later(day, month, year)) {
            index = current;
        }
        current += 1;
    }
    if (index >= 0) {
        // +1 due to the header
        table.rows[index+1].click();
    }
}

function removeSession(session_id) {
    if (confirm("Are you sure you want to delete this session?")) {
        const formData = new FormData();
        formData.append("session_id", session_id);

        fetch("control-panel-delete-session.php", {
            method: "POST",
            body: formData
        }).then(response => response.json())
        .then(result => {
            if (result.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error("Error deleting the session:", error));
    }
}

function validateNewLocation(locationInput) {
    const locationRegex = /\(.*\)$/;
    if (!locationRegex.test(locationInput)) {
        return "Location must include a place in parentheses (e.g., 'Room Marcel Doree (CRBM)').";;
    }
    return "";
}

function validateDate(dateInput) {
    const selectedDate = new Date(dateInput);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    if (selectedDate < today) {
        return "The date must be today or in the future.";;
    }
    return "";
}

function validateLocation(locationInput) {
    const locationID = parseInt(locationInput, 10);
    if (isNaN(locationID) || locationID < 0) {
        return "The location ID is invalid";
    }
    return "";
}

function validateEngineers(engineersInput) {
    const engineersCount = parseInt(engineersInput, 10);
    if (isNaN(engineersCount) || engineersCount < 1) {
        return "The number of engineers must be at least 1.";
    }
    return "";
}

function validateForm() {
    let vd = validateDate(document.getElementById("session_date").value);
    if (vd.length > 0) { return vd; }
    let vl = validateLocation(document.getElementById("location").value);
    if (vl.length > 0) { return vl; }
    let ve = validateEngineers(document.getElementById("nb_engineers").value);
    if (ve.length > 0) { return ve; }
    return "";
}

function setError(errorMessage) {
    target = document.getElementById("errorBox");
    isError = errorMessage.length > 0;
    if (isError) {
        target.style.display = "block";
        target.innerHTML = errorMessage;
    } else {
        target.style.display = "none";
    }
}

function setErrorLocation(errorMessage) {
    target = document.getElementById("locationErrorBox");
    isError = errorMessage.length > 0;
    if (isError) {
        target.style.display = "block";
        target.innerHTML = errorMessage;
    } else {
        target.style.display = "none";
    }
}

function fetchUsers() {
    fetch("control-panel-engineers-list.php")
        .then(response => response.json())
        .then(users => {
            const userListDiv = document.getElementById("users-list");
            userListDiv.innerHTML = "";

            if (users.length === 0) {
                userListDiv.innerHTML = "<div class='nothing'>🔍 Nothing here!</div>";
                return;
            }

            let table = document.createElement("table");
            table.id = "user-table";

            let thead = document.createElement("thead");
            thead.innerHTML = `
                <tr>
                    <th>Username</th>
                    <th>Created At</th>
                    <th>Action</th>
                    <th>Delete</th>
                </tr>
            `;
            table.appendChild(thead);

            let tbody = document.createElement("tbody");
            users.forEach(user => {
                let row = document.createElement("tr");

                let usernameCell = document.createElement("td");
                usernameCell.textContent = user.username;

                let createdAtCell = document.createElement("td");
                createdAtCell.textContent = user.created_at || "N/A";

                let actionCell = document.createElement("td");
                let actionButton = document.createElement("button");
                actionButton.textContent = user.accepted ? "Revoke" : "Accept";
                actionButton.classList.add(user.accepted ? "btn-revoke" : "btn-accept");

                actionButton.addEventListener("click", function() {
                    toggleUser(user.username, !user.accepted);
                });

                actionCell.appendChild(actionButton);

                let deleteCell = document.createElement("td");
                let deleteButton = document.createElement("button");
                deleteButton.textContent = "🗑️ Delete";
                deleteButton.classList.add("btn-delete");

                deleteButton.addEventListener("click", function() {
                    deleteUser(user.username);
                });

                deleteCell.appendChild(deleteButton);

                row.appendChild(usernameCell);
                row.appendChild(createdAtCell);
                row.appendChild(actionCell);
                row.appendChild(deleteCell);

                tbody.appendChild(row);
            });

            table.appendChild(tbody);
            userListDiv.appendChild(table);
        })
        .catch(error => console.error("Error fetching users:", error));
}

function deleteUser(username) {
    if (confirm(`Are you sure you want to delete ${username}?`)) {
        const formData = new FormData();
        formData.append("username", username);

        fetch("control-panel-delete-engineer.php", {
            method: "POST",
            body: formData
        }).then(response => response.json())
        .then(result => {
            if (result.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error("Error deleting the user:", error));
    }
}

function toggleUser(username, newStatus) {
    const formData = new FormData();
    formData.append("username", username);
    formData.append("accepted", newStatus ? "true" : "false");

    fetch("control-panel-manage-engineers.php", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(result => {
          if (result.success) {
              window.location.reload();
          }
      })
      .catch(error => console.error("Error updating user status:", error));
}

function addNewLocation(locationName) {
    const formData = new FormData();
    formData.append("location_name", locationName);
    fetch("control-panel-update-locations.php", {
        method: "POST",
        body: formData
    }).then(response => response.json())
      .then(result => {
          if (result.success) {
              window.location.reload();
          }
      })
      .catch(error => console.error("Error updating locations list:", error));
}

document.getElementById("button_location").addEventListener("click", function (event) {
    const value = document.getElementById("new_location").value;
    errorMessage = validateNewLocation(value);
    setErrorLocation(errorMessage);
    if (errorMessage.length > 0) {
        return;
    }
    addNewLocation(value);
});

document.getElementById("new_location").addEventListener("input", function(event) {
    setErrorLocation(validateNewLocation(event.target.value));
});

document.getElementById("new_session_form").addEventListener("submit", function (event) {
    event.preventDefault();
    errorMessage = validateForm();
    setError(errorMessage);
    if (errorMessage.length > 0) {
        return;
    }
    event.target.submit();
});

document.getElementById("session_date").addEventListener("input", function(event) {
    setError(validateForm());
});

document.getElementById("location").addEventListener("input", function(event) {
    setError(validateForm());
});

document.getElementById("nb_engineers").addEventListener("input", function(event) {
    setError(validateForm());
});

document.getElementById("logout").addEventListener("click", function() {
    fetch("connect-logout.php", {
        method: "POST",
        credentials: "same-origin"
    }).then(response => {
        if (response.ok) {
            window.location.href = "control-panel.php";
        }
    }).catch(error => console.error("Logout failed:", error));
});
