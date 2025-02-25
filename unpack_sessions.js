
const session_report = document.getElementById('session_report');


const monthsList = [
    '---',
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December'
];


function isTodayOrLater(day, month, year) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const givenDate = new Date(year, month - 1, day);
    givenDate.setHours(0, 0, 0, 0);
    return givenDate >= today;
}


function addSession(root, day, month, year, location, nEngineers, nUsers, sessionID) {
    let str_day = day.toString().padStart(2, '0');
    let str_mth = monthsList[month];
    let str_yr  = year.toString();
    let str_eng = nEngineers.toString();
    let str_usr = nUsers.toString();

    let str_date = str_day + " " + str_mth + " " + str_yr;

    let s_selected = document.createElement("input");
    s_selected.type = "checkbox";
    s_selected.className = "s_selected";
    s_selected.checked = false;
    s_selected.addEventListener('change', (event) => {
        event.stopPropagation();
        const ids_list_field = document.getElementById('session_ids');
        const vals = ids_list_field.value.split(',').map(item => parseInt(item, 10)); 
        vals.push(parseInt(sessionID, 10));
        const uniqueVals = new Set(vals);
        if (!event.currentTarget.checked) {
            uniqueVals.delete(parseInt(sessionID, 10));
        }
        const filteredVals = [...uniqueVals].filter(Number.isInteger);
        ids_list_field.value = filteredVals.join(',');
    });

    let session = document.createElement("div");
    session.className = "session_block";

    let date = document.createElement("span");
    date.className = "s_date";
    date.innerHTML = str_date;

    let loc = document.createElement("span");
    loc.className = "s_loc";
    loc.innerHTML = location;

    let inges = document.createElement("span");
    inges.className = "s_n_inges";
    inges.innerHTML = str_eng;

    let users = document.createElement("span");
    users.className = "s_n_users";
    users.innerHTML = str_usr;

    session.appendChild(s_selected);
    session.appendChild(date);
    session.appendChild(loc);
    session.appendChild(inges);
    session.appendChild(users);

    root.appendChild(session);

    session.addEventListener('click', (event) => {
        let items  = document.getElementsByClassName('session_block');
        let header = document.getElementById('s_header');
        for (let i = 0 ; i < items.length ; i++) {
            if (items[i] === header) { continue; }
            items[i].id = "";
        }
        event.currentTarget.id = "s_active";
        
        session_report.innerHTML = "";
        if (!(sessionID in meetingsData)) {
            return;
        }
        let data = meetingsData[sessionID];
        
        for (let j = 0 ; j < data.length ; j++) {
            let block = document.createElement('div');
            block.className = "visit_block";

            let name_block = document.createElement('div');
            name_block.className = "v_name";
            
            let email_block = document.createElement('div');
            email_block.className = "v_contact";

            let info_block = document.createElement('div');
            info_block.className = "v_info";

            let time_block = document.createElement('div');
            time_block.className = "v_expected";

            let fname = document.createElement('span');
            fname.className = "v_fname";
            fname.innerHTML = data[j]['user_first_name'];

            let lname = document.createElement('span');
            lname.className = "v_lname";
            lname.innerHTML = data[j]['user_last_name'];

            let email = document.createElement('span');
            email.className = "v_email";
            email.innerHTML = "📧 " + data[j]['user_email'];

            let inst = document.createElement('span');
            inst.className = "v_institute";
            inst.innerHTML = "🏠 " + data[j]['user_institute'] + " | ";

            let team = document.createElement('span');
            team.className = "v_team";
            team.innerHTML = data[j]['user_team']

            let time = document.createElement('span');
            time.innerHTML = "🕒 " + data[j]['time_start'];

            let title = document.createElement('label');
            title.className = "v_header";
            title.innerHTML = data[j]['problem_header'] + " ▼";

            let description = document.createElement('div');
            description.className = "v_descr";
            description.innerHTML = data[j]['problem_description'];

            let get_data = document.createElement('button');
            get_data.className = "v_download";

            let reject = document.createElement('div');
            reject.className = "v_reject";
            reject.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M7 4V2H17V4H22V6H20V21C20 21.5523 19.5523 22 19 22H5C4.44772 22 4 21.5523 4 21V6H2V4H7ZM6 6V20H18V6H6ZM9 9H11V17H9V9ZM13 9H15V17H13V9Z"></path></svg>';

            name_block.appendChild(fname);
            name_block.appendChild(document.createTextNode(' '));
            name_block.appendChild(lname);
            email_block.appendChild(email);
            info_block.appendChild(inst);
            info_block.appendChild(team);
            time_block.append(time);

            block.appendChild(name_block);
            block.appendChild(email_block);
            block.appendChild(info_block);
            block.appendChild(time_block);
            block.appendChild(title);
            block.append(description);
            block.append(reject);

            let upFiles = data[j]['document_link'].split(';');
            let line_break = document.createElement('br');
            block.appendChild(line_break);

            for (let k = 0 ; k < upFiles.length ; k++) {
                let link = document.createElement('a');
                let url = upFiles[k];
                
                if (url.startsWith("./uploads/")) {
                    link.innerHTML = url;
                    link.href = upFiles[k];
                } else {
                    link.innerHTML = url;
                    link.href = upFiles[k];
                }

                link.className = 'v_download';
                block.appendChild(link);
            }

            session_report.appendChild(block);
        }
    });

    return session;
}

// Ajouter la classe "s_active" à l'élément actif
function unpackSessions(data) {
    let root = document.getElementById('sessions');
    if (root === null) { return; }
    let index = -1;
    let session = null;

    for (let i = 0 ; i < data.length ; i++) {
        let day = parseInt(data[i]['session']['day']);
        let month = parseInt(data[i]['session']['month']);
        let year = parseInt(data[i]['session']['year']);
        let k = addSession(
            root, 
            day,
            month,
            year,
            data[i]['session']['session_location'],
            parseInt(data[i]['session']['nb_ingenieurs']),
            parseInt(data[i]['userCount']),
            data[i]['session']['id']
        );

        if (isTodayOrLater(day, month, year) && index == -1) {
            index = i;
            session = k;
        }
    }
    if (session !== null) {
        session.click();
    }
}


function main() {
    if (typeof sessionsData !== 'undefined') {
        unpackSessions(sessionsData);
    }
}


main();