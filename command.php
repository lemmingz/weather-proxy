<?php

require_once __DIR__ . '/config.inc.php';

date_default_timezone_set("Europe/Berlin");

if (empty(SENSOR_ID)
    || empty(API_KEY)
    || empty(STATION['external_id'])
    || empty(STATION['latitude'])
    || empty(STATION['longitude'])) {

    die('Please fill in all parameters' . PHP_EOL);
}

if (isset($argv[1]) && 'install' == $argv[1]) {

    $url = 'http://api.openweathermap.org/data/3.0/stations?appid=' . API_KEY;
    $stationData = postJSON($url, STATION);

    print_r($stationData);

    exit();
}

die('Please execute php ' . __FILE__ . ' install' . PHP_EOL);

function postJSON($url, $data)
{
    $ch = curl_init($url);

    # Setup request to send json via POST.
    $payload = json_encode($data);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    # Return response instead of printing.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    # Send request.
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
