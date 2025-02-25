
<?php

include('./utils.php');

/**
 * Extracts the sessions from the database and returns them as an array of maps.
 * Only sessions that are scheduled for the future are returned.
 * Sessions are returned in ascending order of date.
 * 
 * @param PDO $pdo The database connection.
 * @param bool $includePassed Whether to include passed sessions.
 * 
 * @return array[map] The array of maps. Each map contains the following values:
 *    - location: The location of the session.
 *    - n_engineers: The number of engineers that can be present.
 *    - day: The day of the session [1, 31].
 *    - month: The month of the session [1, 12].
 *    - year: The year of the session.
 */
function get_sessions_map($pdo, $includePassed=false) {
    $sql = "SELECT session_date, session_location, n_engineers 
            FROM sessions ";
    
    if (!$includePassed) { // Include passed sessions?
        $sql .= "WHERE session_date >= CURDATE() ";
    }

    $sql .= "ORDER BY session_date ASC";
    
    try {
        $stmt = $pdo->query($sql);
        $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }

    $result = [];
    foreach ($sessions as $session) {
        $date = $session['session_date'];
        $datetime = new DateTime($date);
        $result[$date] = [
            'location'    => $session['session_location'],
            'n_engineers' => (int) $session['n_engineers'],
            'day'         => (int) $datetime->format('d'),
            'month'       => (int) $datetime->format('m'),
            'year'        => (int) $datetime->format('Y')
        ];
    }

    return $result;
}


/**
 * Extracts the appointment counts (== number of users that booked that slot) for each time slot, for each open-desk session.
 * The map is indexed by session date (as the session's date is the session's primary key) and contains an array of appointment counts.
 * The arrays are all the same length, whatever slot is booked or not. The length is equal to the array produced by make_laps().
 * 
 * @param PDO $pdo The database connection.
 * 
 * @return map The map of appointment counts.
 *             Ex: {
 *                    "2021-01-01": [0, 1, 0, 2, 1, 1, 0, 1],
 *                    "2021-01-16": [0, 0, 0, 0, 0, 0, 0, 0],
 *                    ...
 *                 }
 */
function get_appointment_counts($pdo) {
    global $_TIME_START, $_TIME_END, $_RDV_DURATION;

    // Get all existing session primary keys from today onward (date == primary key).
    $sql_sessions  = "SELECT session_date 
                      FROM sessions
                      WHERE session_date >= CURDATE() 
                      ORDER BY session_date ASC";
    try {
        $stmt_sessions = $pdo->query($sql_sessions);
        $sessions      = $stmt_sessions->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        return [];
    }
    
    // Get existing appointments from today onward, grouped by session and time.
    $sql_appointments = "SELECT session_id, time_start, COUNT(*) AS count
                         FROM appointments
                         WHERE session_id >= CURDATE() 
                         GROUP BY session_id, time_start";
    try {
        $stmt_appointments = $pdo->query($sql_appointments);
        $appointments      = $stmt_appointments->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }

    // Initialize a blank map with all the session dates as keys and empty arrays of fixed length.
    $result = [];
    $time_slots = make_laps($_TIME_START, $_TIME_END, $_RDV_DURATION);
    foreach ($sessions as $date) {
        $datetime = new DateTime($date);
        $result[$date] = array_fill(0, count($time_slots), 0);
    }

    // Fill the map with the actual appointment counts
    $reverse = array_flip($time_slots);
    foreach ($appointments as $appointment) {
        $date = $appointment['session_id'];
        $time = substr($appointment['time_start'], 0, 5); // Format HH:MM
        $result[$date][$reverse[$time]] = (int) $appointment['count'];
    }

    return $result;
}

?>