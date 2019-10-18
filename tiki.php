
#!/usr/bin/php
<?php
// Preset PHP settings
error_reporting(E_ALL);
ini_set('display_errors', 0);
date_default_timezone_set('UTC');

// Define root directory
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__ . DS);

if (!isset($argv)) {
    die('ERROR: Please run this script in command line.');
}

if (!isset($argv[1])) {
    die('ERROR: Please provide the absolute path to input CSV file.');
}

if (!file_exists($argv[1])) {
    die('ERROR: The input CSV file is not found.');
}

if (!isset($argv[2])) {
    die('ERROR: Please provide the absolute path to output CSV file.');
}

if (!is_writable(dirname($argv[2]))) {
    die('ERROR: The output directory is not writable.');
}

$file = fopen($argv[1], 'r');

if (!$file) {
    die('ERROR: Failed to read the input CSV.');
}

@file_put_contents($argv[2], '');

while (!feof($file)) {
    $data = fgetcsv($file);
    $phone = Phone($data[0]);
    $post = 'full_name=Quyen+Dang&phone_number='.$phone.'&email=dangnquysen%40hotmail.com&password=Cucainho214&gender=male';
    $ret = Curl("https://tiki.vn/api/v2/customers/validate", $post, $http_status);
    
    unset($data[0]);
    if ($http_status == "400") {
        @file_put_contents($argv[2], '"' . $phone . "\"\n", FILE_APPEND);
    }
    
}

fclose($file);
    
function Phone($phone_number){
    $first_c = substr($phone_number,0,1);
    if($first_c == "0"){
        return $phone_number;
    } else {
        $two_c = substr($phone_number,0,2);
        if ($two_c == "84"){
            return '0'.substr($phone_number, -(strlen($phone_number) - 2));
        } else {
            if ($two_c == "+8"){
                return '0'.substr($phone_number, -(strlen($phone_number) - 3));
            } else {
                return $phone_number;
            }
        }
    }
}
function Curl($url, $post_data, &$http_status, &$header = null) {
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    // post_data
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    if (!is_null($header)) {
        curl_setopt($ch, CURLOPT_HEADER, true);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.90 Mobile Safari/537.36'));
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
      
    $body = null;
    // error
    if (!$response) {
        $body = curl_error($ch);
        // HostNotFound, No route to Host, etc  Network related error
        $http_status = -1;
    } else {
       //parsing http status code
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!is_null($header)) {
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
        } else {
            $body = $response;
        }
    }
    curl_close($ch);
    return $body;
}
