<?php
require_once __DIR__.'/config.inc.php';

date_default_timezone_set("Europe/Berlin");
ignore_user_abort(true);
ob_start();

// read sensor ID ('esp8266-'+ChipID)
$headers = [];
$headers['Sensor'] = $_SERVER['HTTP_SENSOR'] ?? $_SERVER['HTTP_X_SENSOR'] ?? '';

$json = file_get_contents('php://input');
$results = json_decode($json, true);

header_remove();

$now = strftime("%Y/%m/%d %H:%M:%S");
$today = strftime("%Y-%m-%d");
$values = [];
// copy sensor data values to values array
foreach ($results["sensordatavalues"] as $sensordatavalues) {
    $values[$sensordatavalues["value_type"]] = $sensordatavalues["value"];
}

// print transmitted values
echo "Sensor: " . $headers['Sensor'] . "\r\n";
header('Connection: close');
header('Content-Length: '.ob_get_length());
ob_end_flush();
ob_flush();
flush();
fastcgi_finish_request();


// check if data dir exists, create if not
if (!file_exists('data')) {
    mkdir('data', 0755, true);
}

// save data values to CSV (one per day)
$datafile = __DIR__ . "/data/data-" . $headers['Sensor'] . "-" . $today . ".csv"                                                                                                                                                             ;
if (!file_exists($datafile)) {
    $outfile = fopen($datafile, "a");
    fputcsv($outfile,
        [
            "Time",
            "durP1",
            "ratioP1",
            "P1",
            "durP2",
            "ratioP2",
            "P2",
            "SDS_P1",
            "SDS_P2",
            "Temp",
            "Humidity",
            "BMP_temperature",
            "BMP_pressure",
            "BME280_temperature",
            "BME280_humidity",
            "BME280_pressure",
            "Samples",
            "Min_cycle",
            "Max_cycle",
            "Signal"
        ],
        ';'
    );
    fclose($outfile);
}

$values["durP1"] = $values["durP1"] ?? "";
$values["ratioP1"] = $values["ratioP1"] ?? "";
$values["P1"] = $values["P1"] ?? "";
$values["durP2"] = $values["durP2"] ?? "";
$values["ratioP2"] = $values["ratioP2"] ?? "";
$values["P2"] = $values["P2"] ?? "";
$values["SDS_P1"] = $values["SDS_P1"] ?? "";
$values["SDS_P2"] = $values["SDS_P2"] ?? "";
$values["temperature"] = $values["temperature"] ?? "";
$values["humidity"] = $values["humidity"] ?? "";
$values["BMP_temperature"] = $values["BMP_temperature"] ?? "";
$values["BMP_pressure"] = $values["BMP_pressure"] ?? "";
$values["BME280_temperature"] = $values["BME280_temperature"] ?? "";
$values["BME280_humidity"] = $values["BME280_humidity"] ?? "";
$values["BME280_pressure"] = $values["BME280_pressure"] ?? "";
$values["samples"] = $values["samples"] ?? "";
$values["min_micro"] = $values["min_micro"] ?? "";
$values["max_micro"] = $values["max_micro"] ?? "";
$values["signal"] = isset($values["signal"]) ? substr($values["signal"], 0, -4)                                                                                                                                                              : '';

$outfile = fopen($datafile, "a");
fputcsv($outfile,
    [
        $now,
        $values["durP1"],
        $values["ratioP1"],
        $values["P1"],
        $values["durP2"],
        $values["ratioP2"],
        $values["P2"],
        $values["SDS_P1"],
        $values["SDS_P2"],
        $values["temperature"],
        $values["humidity"],
        $values["BMP_temperature"],
        $values["BMP_pressure"],
        $values["BME280_temperature"],
        $values["BME280_humidity"],
        $values["BME280_pressure"],
        $values["samples"],
        $values["min_micro"],
        $values["max_micro"],
        $values["signal"]
    ],
    ';');
fclose($outfile);

$url = 'http://api.openweathermap.org/data/3.0/measurements?appid=' . API_KEY;

$date = new \DateTime();

$data = [
    [
        'station_id' => STATION_ID,
        'dt' => (int)$date->format('U'),
        'temperature' => (float)$values["BME280_temperature"],
        'pressure' => (float)$values["BME280_pressure"] / 100,
        'humidity' => (float)$values["BME280_humidity"]
    ]
];

postJSON($url, $data);
exit();


function postJSON($url, $data)
{
    $ch = curl_init($url);

    # Setup request to send json via POST.
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'))                                                                                                                                                             ;

    # Return response instead of printing.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    # Send request.
    $result = curl_exec($ch);
    curl_close($ch);

    # Print response to log due connection is closed.
    error_log(json_encode($result));
}
