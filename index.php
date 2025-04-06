<?php

    include("db.php");
    include("extract-sessions.php");
    include("index-announcement.php");
    global $_TIME_START, $_TIME_END, $_RDV_DURATION;

    $pdo          = connect_db();
    $sessionData  = get_sessions_map($pdo);
    $appointments = get_appointment_counts($pdo);
    $announcement = make_announcement($pdo);
    $pdo          = null;
    
    $timeSlots    = json_encode(make_laps($_TIME_START, $_TIME_END, $_RDV_DURATION), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $sessionsData = json_encode($sessionData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $appointments = json_encode($appointments, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    $n_sessions   = count($sessionData);
?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open-desks MRI-CIA</title>
    <link rel="stylesheet" type="text/css" href="index-style-desktop.css">
    <link rel="stylesheet" type="text/css" href="index-style-mobile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lilita+One">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Yaldevi">
    <link rel="icon" type="image/png" sizes="32x32" href="./data/medias/logo-mri.png">
</head>

<body>

    <!-- MAIN HEADER -->
    <div id="navbar">
        <div id="logo_container">
            <a href=".">
                <img src="./data/medias/logo-mri.png" alt="Home">
            </a>
        </div>
    </div>

    <div id="title">
        <span>Open-desks</span>
        <br>
        <span>by MRI-CIA</span>
    </div>

    <!-- ANNOUNCEMENT -->

    <?php echo $announcement; ?>

    <!--  INTRODUCTION  -->
    <div id="introduction">
        <ul id="intro_list">
            <li class="intro_line">Open-desk sessions are <b>one-to-one meetings</b> where you can get help with your <b>image analysis problems</b>.</li>
            <li class="intro_line">Registration is <b>completely free</b>, and there is <b>no charge</b> for any work undertaken <b>during the session</b>.</li>
            <li class="intro_line">We can handle <b>any imaging modality</b><span> (e.g. fluo, IHC, µ-CT, FLIM/FRET, FRAP, ...)</span>.</li>
            <li class="intro_line">You can come for <b>any kind of issue</b><span> (e.g. deconvolution, stitching, colocalization, tracking, cells segmentation, ...)</span>.</li>
            <li class="intro_line">We can help you get your <b>hands on analysis softwares</b><span> (e.g. ImageJ/Fiji, QuPath, Huygens, Imaris, CellPose, ...)</span>.</li>
        </ul>
    </div>

    <?php if ($n_sessions > 0): ?>

    <!-- SESSIONS LIST -->
    <div id="upcoming">
        <span>Upcoming sessions</span>
    </div>
    <div id="sessions"></div>


    <!-- BOOKING -->
    <div id="booking">
        <div id="session_details">
            <a href="https://maps.app.goo.gl/sv5nYkmYYd3VbMVC7" target="_blank">
                <img alt="pin point location" src="./data/medias/pin-point.png" id="pinpoint" />
            </a>
            <div id="details">
                <span class="session_info" id="session_date">---</span>
                <span class="session_info" id="session_duration">14h → 18h</span>
                <span class="session_info" id="session_location">---</span>
            </div>
        </div>

        <form action="./appointment-confirm.php" method="POST" enctype="multipart/form-data" id="booking_form">
            <table>
                <tr>
                    <td>
                        <label for="first_name">First name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </td>
                    <td>
                        <label for="last_name">Last name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </td>
                </tr>
            </table>

            <label for="email">Email address</label>
            <input type="email" id="email" name="email" required><br>

            <label for="team">Team</label>
            <input type="text" id="team" name="team" required><br>

            <label for="institute">Institute</label>
            <input type="text" id="institute" name="institute" required><br>

            <label for="appointmentTime">When would you like to come?</label>
            <select id="appointmentTime" name="appointmentTime" required></select><br>
            
            <label for="reason">How can we help you?</label>
            <textarea id="reason" name="reason" rows="12" cols="50" placeholder="Max 8192 characters."></textarea><br>

            <label id="lbl_data_link" for="dataLink">Attach a <a href="https://filesender.renater.fr/">FileSender</a> link or an SFTP path:
                <a href="https://www.mri.cnrs.fr/images/documents/sFTP.pdf"><img 
                    id="question" 
                    src="./data/medias/question-line.svg" 
                    style="width: 15px; height: 15px; vertical-align: super; margin-left: 1px; cursor: pointer;" 
                    title="When you use FileZilla to transfer your data on 'Sftp MRI', they are on 'sftp://utah.mri.cnrs.fr'. Place them in _COMMUN for us to access them. Click here for more details about FileZilla."
                ></a>
            </label><br>
            <input type="text" id="dataLink" name="dataLink" placeholder="ex: sftp://utah.mri.cnrs.fr/_COMMUN/myname/my-images"><br>
            
            <input type="checkbox" id="noData" name="noData">
            <label for="noData">I will come with a USB drive</label><br>

            <input type="checkbox" id="notFromCampus" name="notFromCampus">
            <label for="notFromCampus">I'm on the <u>Route de Mende</u> campus</label><br>

            <input id="sessionID" name="sessionID" type="hidden">

            <input id="company" name="company" type="text">
            
            <input type="submit" value="Submit" id="book_ok">
          </form>

          <div id="sub_failed">
            <span>A required field is either missing or incorrect.</span>
          </div>

    </div>

    <?php else: ?>

    <div class='nothing'>🔍 There is no session planed for now</div>

    <?php endif; ?>

    <!-- LINKS -->
    <div id="links">
        <table>
            <tr>
                <td>
                    <a target="_blank" href="mailto:mri-cia@mri.cnrs.fr"><img alt="email icon" src="./data/medias/email.png"/></a>
                </td>
                <td>
                    <a target="_blank" href="mailto:mri-cia@mri.cnrs.fr">mri-cia@mri.cnrs.fr</a>
                </td>
            </tr>
            
            <tr>
                <td>
                    <a target="_blank" href="https://www.github.com/MontpellierRessourcesImagerie"><img alt="github icon" src="./data/medias/github.png"/></a>
                </td>
                <td>
                    <a target="_blank" href="https://www.github.com/MontpellierRessourcesImagerie">GitHub MRI</a>
                </td>
            </tr>
            
            <tr>
                <td>
                    <a target="_blank" href="https://www.mri.cnrs.fr/en/data-analysis/about-mri-cia.html"><img alt="MRI icon" src="./data/medias/logo-mri.png"/></a>
                </td>
                <td>
                    <a target="_blank" href="https://www.mri.cnrs.fr/en/data-analysis/about-mri-cia.html">MRI website</a>
                </td>
            </tr>

            <tr>
                <td>
                    <a target="_blank" href="https://duo.dr13.cnrs.fr/visiteur/index"><img alt="QR code icon" src="./data/medias/qr_code.png"/></a>
                </td>
                <td>
                    <a target="_blank" href="https://duo.dr13.cnrs.fr/visiteur/index">Access QR-code</a>
                </td>
            </tr>

            <tr>
                <td>
                    <a target="_blank" href="https://github.com/MontpellierRessourcesImagerie/mri-cia-documents/releases/latest/download/mri-cia-project-form.pdf"><img alt="Project form icon" src="./data/medias/project-form.png"/></a>
                </td>
                <td>
                    <a target="_blank" href="https://github.com/MontpellierRessourcesImagerie/mri-cia-documents/releases/latest/download/mri-cia-project-form.pdf">Project form</a>
                </td>
            </tr>

            <tr>
                <td>
                    <a href="control-panel.php"><img alt="Control panel gear icon" src="./data/medias/gear-icon.png"/></a>
                </td>
                <td>
                    <a href="control-panel.php">Control panel</a>
                </td>
            </tr>

        </table>
    </div>
    
    <script id="php_sessions_data" type="application/json">
        {
            "timeSlots"   : <?php echo $timeSlots; ?>,
            "sessionsData": <?php echo $sessionsData; ?>,
            "appointments": <?php echo $appointments; ?>
        }
    </script>
    <script id="finish-js" type="text/javascript" src="index-builder.js"></script>
    <script id="events-js" type="text/javascript" src="index-event-listeners.js"></script>

</body>
</html>
