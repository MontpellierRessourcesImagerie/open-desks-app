
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

/**
 * Generates an array of time laps between two given times.
 * The starting time is included, the ending time is not.
 * Ex: make_laps(14.0, 18.0, 0.5) => ['14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30']
 * The time is formatted as 'HH:MM', as strings.
 * 
 * @param float $start The starting time, with the decimal part representing minutes as a fraction of an hour.
 * @param float $end The ending time, with the decimal part representing minutes as a fraction of an hour.
 * @param float $duration The duration of each time lap, in hours.
 * @return array An array of time laps, formatted as 'HH:MM'.
 */
function make_laps(float $start, float $end, float $duration): array {
    $laps = [];
    
    for ($time = $start; $time < $end; $time += $duration) {
        $hours = floor($time);
        $minutes = ($time - $hours) * 60;
        $laps[] = sprintf('%02d:%02d', $hours, $minutes);
    }

    return $laps;
}

/**
 * Converts a date string from 'Y-m-d' format to a litteral date string.
 * Ex: makeLitteralDate('2021-09-30') => '30 September of 2021'
 * 
 * @param string $dateString The date string to convert.
 * @return string The litteral date string.
 */
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

/**
 * Checks if the required fields are present in the POST request.
 * If not, it echoes an error message and returns false.
 * The error message contains all the missing fields.
 * 
 * @param array $fields The list of fields to check.
 * @return bool True if all fields are present, false otherwise.
 */
function requiredData($fields) {
    if ($fields === null) {
        return false;
    }
    $missingFields = [];

    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            $missingFields[] = $field;
        }
    }

    if (!empty($missingFields)) {
        return false;
    }
    
    return true;
}

/**
 * Checks if it is safe to access a given file for a redirection (the desired redirection is passed by GET).
 * The file must be in the same directory as the "index.php" file.
 * (!!!) This function must be called from a safe file only.
 * 
 * @param string $target The target file to check.
 * @return bool True if the file is safe, false otherwise.
 */
function isSafeFile($target) {
    if ($target === null) {
        return true;
    }
    $r_path = realpath($target);
    $s_path = realpath(".");
    return str_starts_with($r_path, $s_path);
}

function getSafeCharsList() {
    return 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
}

function generateSafeAsciiString($length) {
    $allowedChars = getSafeCharsList();
    $result = '';
    $maxIndex = strlen($allowedChars) - 1;
    for ($i = 0; $i < $length; $i++) {
        $index = random_int(0, $maxIndex);
        $result .= $allowedChars[$index];
    }
    return $result;
}

function isSafeCharChain($inputChain) {
    $allowedChars = getSafeCharsList();
    for ($i = 0; $i < strlen($inputChain); $i++) {
        if (strpos($allowedChars, $inputChain[$i]) === false) {
            return false;
        }
    }
    return true;

}

?>