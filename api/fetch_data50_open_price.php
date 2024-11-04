<?php
$ch = curl_init('https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%2050');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    error_log("Error: " . curl_error($ch));
} else {
    // Process the response
    echo $response;
}

curl_close($ch);
