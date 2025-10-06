<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TESTING FINAL SIMPLIFIED FLOW ===\n\n";

$functions = new functions();

// Test the complete flow
echo "1. Testing complete site generation flow...\n";
$result = $functions->uploadsites();

echo "Result: " . ($result['success'] ? 'SUCCESS' : 'FAILED') . "\n";
echo "Message: " . strip_tags($result['message']) . "\n";
echo "Sites generated: " . count($result['sites']) . "\n\n";

if ($result['success']) {
    // Verify folders were created
    echo "2. Verifying folder structure...\n";
    
    if (is_dir('./activos/')) {
        $folders = array_filter(glob('./activos/*'), 'is_dir');
        echo "Total folders created: " . count($folders) . "\n";
        
        foreach ($folders as $folder) {
            $folderName = basename($folder);
            echo "\n📁 Folder: " . $folderName . "\n";
            
            // Check essential files/folders
            $essentials = [
                'index.php' => 'file',
                'json/ford.json' => 'file',
                'images' => 'dir',
                'style' => 'dir',
                'js' => 'dir',
                'fonts' => 'dir'
            ];
            
            foreach ($essentials as $item => $type) {
                $path = $folder . '/' . $item;
                $exists = ($type === 'dir') ? is_dir($path) : file_exists($path);
                echo "  " . ($exists ? '✅' : '❌') . " " . $item . "\n";
                
                // Check JSON content for title
                if ($item === 'json/ford.json' && $exists) {
                    $jsonContent = json_decode(file_get_contents($path), true);
                    if (isset($jsonContent['title']['title'])) {
                        echo "    📝 Title: " . $jsonContent['title']['title'] . "\n";
                    }
                }
            }
        }
    } else {
        echo "❌ No activos folder found\n";
    }
} else {
    echo "❌ Site generation failed, cannot verify structure\n";
}

echo "\n=== FLOW TEST COMPLETE ===\n";
?>
