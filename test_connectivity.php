<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== CONNECTIVITY TEST ===\n";
echo "API URL: " . $_ENV['API_URL'] . "\n\n";

$url = $_ENV['API_URL'] . '/sites/actives';

// Test with different timeout configurations
$timeouts = [30, 60, 120];

foreach ($timeouts as $timeout) {
    echo "Testing with {$timeout}s timeout...\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    $start = microtime(true);
    $result = curl_exec($ch);
    $end = microtime(true);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    
    curl_close($ch);
    
    $duration = round(($end - $start) * 1000, 2);
    
    echo "  HTTP Code: {$http_code}\n";
    echo "  Duration: {$duration}ms\n";
    echo "  cURL Total Time: {$total_time}s\n";
    echo "  Error: " . ($curl_error ?: 'None') . "\n";
    echo "  Response Length: " . strlen($result) . " bytes\n";
    
    if ($result !== false && $http_code === 200) {
        echo "  ✅ SUCCESS\n";
        $json = json_decode($result, true);
        if ($json !== null) {
            echo "  Sites found: " . count($json) . "\n";
        }
        break; // Exit on first success
    } else {
        echo "  ❌ FAILED\n";
    }
    echo "\n";
}

// Test fallback mode
echo "=== TESTING FALLBACK MODE ===\n";
require_once 'functions.php';

$functions = new functions();
$result = $functions->uploadsites();

echo "Fallback result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";
?>
