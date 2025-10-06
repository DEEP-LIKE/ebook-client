<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TESTING DYNAMIC CACHE SYSTEM ===\n\n";

$functions = new functions();

// Test 1: Normal operation (should cache data)
echo "1. Testing normal API operation...\n";
$result1 = $functions->uploadsites();
echo "Result: " . ($result1['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . strip_tags($result1['message']) . "\n";
echo "Using cache: " . ($result1['usingCache'] ? 'YES' : 'NO') . "\n";
echo "Sites: " . count($result1['sites']) . "\n\n";

// Test 2: Check cache file
$cacheFile = sys_get_temp_dir() . '/ford_api_cache.json';
echo "2. Checking cache file...\n";
echo "Cache file: " . $cacheFile . "\n";
if (file_exists($cacheFile)) {
    $cacheData = json_decode(file_get_contents($cacheFile), true);
    echo "Cache exists: YES\n";
    echo "Cache timestamp: " . date('Y-m-d H:i:s', $cacheData['timestamp']) . "\n";
    echo "Cached sites: " . count($cacheData['data']) . "\n";
    
    // Show cached site names
    echo "Cached site folders: ";
    $folders = array_map(function($site) { return $site['folderName']; }, $cacheData['data']);
    echo implode(', ', $folders) . "\n";
} else {
    echo "Cache exists: NO\n";
}
echo "\n";

// Test 3: Simulate API failure by temporarily changing API URL
echo "3. Testing fallback mode (simulating API failure)...\n";

// Create a class that extends functions to test cache fallback
class TestFunctionsWithFailure extends functions {
    protected function processSites(){
        // Temporarily change API URL to simulate failure
        $originalUrl = $_ENV['API_URL'];
        $_ENV['API_URL'] = 'https://fake-api-that-will-fail.com';
        
        $result = parent::processSites();
        
        // Restore original URL
        $_ENV['API_URL'] = $originalUrl;
        
        return $result;
    }
}

$testFunctions = new TestFunctionsWithFailure();
$result2 = $testFunctions->uploadsites();

echo "Result: " . ($result2['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . strip_tags($result2['message']) . "\n";
echo "Using cache: " . ($result2['usingCache'] ? 'YES' : 'NO') . "\n";
echo "Sites: " . count($result2['sites']) . "\n\n";

// Test 4: Clear cache
echo "4. Testing cache clearing...\n";
$cleared = functions::clearCache();
echo "Cache cleared: " . ($cleared ? 'YES' : 'NO') . "\n";
echo "Cache file exists after clear: " . (file_exists($cacheFile) ? 'YES' : 'NO') . "\n\n";

// Test 5: Test without cache (should fail)
echo "5. Testing without cache and API failure...\n";
$result3 = $testFunctions->uploadsites();
echo "Result: " . ($result3['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . strip_tags($result3['message']) . "\n\n";

echo "=== CACHE SYSTEM TEST COMPLETE ===\n";
?>
