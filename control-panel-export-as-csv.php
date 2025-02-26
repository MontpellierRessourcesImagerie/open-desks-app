<?php

include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['session_ids'])) {
    // Extract session IDs from POST data
    $sessionIds = explode(',', $_POST['session_ids']);
    
    // Convert session IDs to integers to ensure safety
    $sessionIds = array_map('intval', $sessionIds);

    // Prepare the SQL placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($sessionIds), '?'));

    // Prepare the SQL query to fetch session and meeting data, now including team and institute
    $query = "
        SELECT s.day, s.month, s.year, s.session_location, m.time_start, 
               m.problem_header, m.problem_description, u.email, 
               CONCAT(u.first_name, ' ', u.last_name) as name, u.team, u.institute
        FROM sessions s
        JOIN meetings m ON s.id = m.session_id
        JOIN users u ON m.user_id = u.id
        WHERE s.id IN ($placeholders)
        ORDER BY s.id, m.time_start;
    ";

    // Prepare statement using PDO
    $stmt = $GLOBALS['pdo']->prepare($query);

    // Execute query with session IDs
    $stmt->execute($sessionIds);

    // Prepare CSV headers
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="session_data.csv"');

    // Open output stream for writing CSV
    $output = fopen('php://output', 'w');
    fputcsv($output, [
        'Day', 'Month', 'Year', 'Location', 'Time Start', 
        'Problem Header', 'Problem Description', 'Email', 'Name', 'Team', 'Institute'
    ]);

    // Fetch and write each row to the CSV
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['day'],
            $row['month'],
            $row['year'],
            $row['session_location'],
            $row['time_start'],
            $row['problem_header'],
            $row['problem_description'],
            $row['email'],
            $row['name'],
            $row['team'],
            $row['institute']
        ]);
    }

    // Close output stream
    fclose($output);
}

// There's no need to close PDO connections as they are automatically closed at the end of the script.
?>
