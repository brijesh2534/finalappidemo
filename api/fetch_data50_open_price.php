<?php
// Define the URL for the NSE API
$url = "https://www.nseindia.com/api/equity-stockIndices?index=NIFTY%20500";

// Function to fetch data using cURL
function fetchData($url, $retries = 3) {
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
            "Accept: application/json, text/plain, */*",
            "Referer: https://www.nseindia.com/",
            "X-Requested-With: XMLHttpRequest"
        ]);

        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            curl_close($ch);
            sleep(1);
            continue; // Retry if there's a cURL error
        }

        curl_close($ch);
        $jsonData = json_decode($data, true);
        if ($jsonData !== null && isset($jsonData['data'])) {
            return $jsonData; // Return successful response
        }
        sleep(1); // Wait before retrying
    }
    return null; // Return null if all retries failed
}

$jsonData = fetchData($url);

if ($jsonData === null || !isset($jsonData['data'])) {
    echo json_encode(["error" => "Failed to decode JSON data or missing 'data' field."]);
    exit;
}

// Calculate % change from open for each stock
foreach ($jsonData['data'] as &$stock) {
    $stock['pChangeOpen'] = ($stock['lastPrice'] - $stock['open']) / $stock['open'] * 100;
}

// Sort the array by % change from open in descending order
usort($jsonData['data'], function($a, $b) {
    return $b['pChangeOpen'] <=> $a['pChangeOpen'];
});

// Get the top 10 gainers
$topGainers = array_slice($jsonData['data'], 0, 10);
$lastUpdatedTime = $jsonData['data'][0]['lastUpdateTime'];

echo json_encode([
    "topGainers" => $topGainers,
    "lastUpdatedTime" => $lastUpdatedTime
]);
