<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing API connection...\n";
echo "API URL: " . $_ENV['API_URL'] . "\n";

// Test individual site APIs for both sites
$folderNames = ['fordlapiedad', 'fordcavsamotors'];

foreach ($folderNames as $folderName) {
    $individualUrl = $_ENV['API_URL'] . '/sites/by_folder_name/' . $folderName;
    echo "Testing individual site API: " . $individualUrl . "\n";

    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $individualUrl);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
    $individualResult = curl_exec($ch2);
    $individualHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    echo "Individual API HTTP Code: " . $individualHttpCode . "\n";
    if ($individualResult !== false && $individualHttpCode === 200) {
        $individualJson = json_decode($individualResult, true);
        if ($individualJson !== null) {
            echo "Individual API title: " . (isset($individualJson['title']) ? json_encode($individualJson['title']) : 'N/A') . "\n";
            echo "Individual API title type: " . (isset($individualJson['title']) ? gettype($individualJson['title']) : 'N/A') . "\n";
            echo "Raw response (first 200 chars): " . substr($individualResult, 0, 200) . "\n\n";
        }
    }
}

$url = $_ENV['API_URL'] . '/sites/actives';
echo "Full URL: " . $url . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Code: " . $http_code . "\n";
echo "cURL Error: " . $curl_error . "\n";
echo "Response length: " . strlen($result) . " characters\n\n";

if ($result !== false && $http_code === 200) {
    $json = json_decode($result, true);
    if ($json !== null) {
        echo "JSON decoded successfully\n";
        echo "Number of sites: " . count($json) . "\n\n";
        
        foreach ($json as $index => $site) {
            echo "Site " . ($index + 1) . ":\n";
            echo "  - ID: " . (isset($site['id']) ? $site['id'] : 'N/A') . "\n";
            echo "  - Folder Name: " . (isset($site['folderName']) ? $site['folderName'] : 'N/A') . "\n";
            echo "  - Title: " . (isset($site['title']) ? $site['title'] : 'N/A') . "\n";
            echo "  - Title type: " . (isset($site['title']) ? gettype($site['title']) : 'N/A') . "\n";
            if (isset($site['title'])) {
                echo "  - Title raw: " . json_encode($site['title']) . "\n";
            }
            echo "  - URL: " . (isset($site['url']) ? $site['url'] : 'N/A') . "\n";
            echo "  - Images: " . (isset($site['images']) ? count($site['images']) : 0) . "\n";
            echo "\n";
        }
    } else {
        echo "Error decoding JSON\n";
        echo "Raw response (first 500 chars): " . substr($result, 0, 500) . "\n";
    }
} else {
    echo "API request failed\n";
}
?>
