<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TESTING CHUNKED PROCESSING ===\n\n";

$functions = new functions();

// Test chunk processing
echo "1. Testing first chunk (offset 0)...\n";
$result1 = $functions->processSitesChunked(0, 2);

echo "Result: " . ($result1['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . strip_tags($result1['message']) . "\n";
echo "Total sites: " . ($result1['totalSites'] ?? 0) . "\n";
echo "Processed sites: " . ($result1['processedSites'] ?? 0) . "\n";
echo "Has more: " . ($result1['hasMore'] ? 'YES' : 'NO') . "\n";
echo "Next offset: " . ($result1['nextOffset'] ?? 'N/A') . "\n\n";

if ($result1['success'] && $result1['hasMore']) {
    echo "2. Testing second chunk (offset " . $result1['nextOffset'] . ")...\n";
    $result2 = $functions->processSitesChunked($result1['nextOffset'], 2);
    
    echo "Result: " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "\n";
    echo "Message: " . strip_tags($result2['message']) . "\n";
    echo "Processed sites: " . ($result2['processedSites'] ?? 0) . "\n";
    echo "Has more: " . ($result2['hasMore'] ? 'YES' : 'NO') . "\n\n";
}

// Check final result
echo "3. Checking final folder structure...\n";
if (is_dir('./activos/')) {
    $folders = array_filter(glob('./activos/*'), 'is_dir');
    echo "Total folders created: " . count($folders) . "\n";
    
    foreach ($folders as $folder) {
        $folderName = basename($folder);
        echo "  📁 " . $folderName . "\n";
        
        // Check JSON
        $jsonFile = $folder . '/json/ford.json';
        if (file_exists($jsonFile)) {
            $jsonContent = json_decode(file_get_contents($jsonFile), true);
            if (isset($jsonContent['title']['title'])) {
                echo "    📝 Title: " . $jsonContent['title']['title'] . "\n";
            }
        }
    }
} else {
    echo "❌ No activos folder found\n";
}

echo "\n=== CHUNKED TEST COMPLETE ===\n";
?>
