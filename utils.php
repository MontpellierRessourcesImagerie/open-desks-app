
<?php

$monthsList = [
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

$_TIME_START   = 14.0;
$_TIME_END     = 18.0;
$_RDV_DURATION = 0.5;

function make_laps(float $start, float $end, float $duration): array {
    $laps = [];
    
    for ($time = $start; $time < $end; $time += $duration) {
        $hours = floor($time);
        $minutes = ($time - $hours) * 60;
        $laps[] = sprintf('%02d:%02d', $hours, $minutes);
    }

    return $laps;
}

function makeLitteralDate($dateString) {
    global $monthsList;
    $date = DateTime::createFromFormat('Y-m-d', $dateString);
    
    if (!$date) {
        return "Invalid date";
    }

    $day = str_pad($date->format('d'), 2, '0', STR_PAD_LEFT);
    $monthIndex = (int)$date->format('m');
    $year = $date->format('Y');

    return "{$day} {$monthsList[$monthIndex]} of {$year}";
}

function requiredData($fields) {
    if ($fields === null) {
        echo "Undefined fields to check.";
        return false;
    }
    $missingFields = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        echo "Some required fields are missing: " . implode(', ', $missingFields);
        return false;
    }
    
    return true;
}

?>