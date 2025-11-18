<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Testing site generation process...\n";

$functions = new functions();
$result = $functions->uploadsites();

echo "Result: " . json_encode($result, JSON_PRETTY_PRINT) . "\n";

if (isset($result['success']) && $result['success']) {
    echo "✅ Site generation completed successfully!\n";
    
    // Verificar que las carpetas se crearon
    if (is_dir('./activos/')) {
        $folders = array_filter(glob('./activos/*'), 'is_dir');
        echo "Created folders: " . count($folders) . "\n";
        foreach ($folders as $folder) {
            $folderName = basename($folder);
            echo "  - " . $folderName . "\n";
            
            // Verificar que el JSON se creó correctamente
            $jsonFile = $folder . '/json/ford.json';
            if (file_exists($jsonFile)) {
                $jsonContent = json_decode(file_get_contents($jsonFile), true);
                if (isset($jsonContent['title']['title'])) {
                    echo "    Title: " . $jsonContent['title']['title'] . "\n";
                } else {
                    echo "    ❌ No title found in JSON\n";
                }
            } else {
                echo "    ❌ JSON file not found\n";
            }
        }
    } else {
        echo "❌ No activos folder created\n";
    }
} else {
    echo "❌ Site generation failed\n";
    if (isset($result['message'])) {
        echo "Error: " . $result['message'] . "\n";
    }
}
?>
