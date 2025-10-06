<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== GENERADOR DE SITIOS ACTIVOS ===\n\n";

$functions = new functions();
$result = $functions->uploadsites();

echo "Estado: " . ($result['success'] ? '✅ ÉXITO' : '❌ ERROR') . "\n";
echo "Mensaje: " . strip_tags($result['message']) . "\n";

if ($result['success']) {
    echo "\n📁 SITIOS GENERADOS:\n";
    if (is_dir('./activos/')) {
        $folders = array_filter(glob('./activos/*'), 'is_dir');
        foreach ($folders as $folder) {
            $folderName = basename($folder);

            // Verificar si tiene archivos básicos
            $hasIndex = file_exists($folder . '/index.php');
            $hasJson = file_exists($folder . '/json/ford.json');
            $hasStyle = is_dir($folder . '/style');
            $hasImages = is_dir($folder . '/images');

            echo "✅ $folderName";
            echo " (index: " . ($hasIndex ? '✅' : '❌') . ")";
            echo " (json: " . ($hasJson ? '✅' : '❌') . ")";
            echo " (style: " . ($hasStyle ? '✅' : '❌') . ")";
            echo " (images: " . ($hasImages ? '✅' : '❌') . ")";
            echo "\n";

            // Mostrar título si existe
            if ($hasJson) {
                $jsonContent = json_decode(file_get_contents($folder . '/json/ford.json'), true);
                if (isset($jsonContent['title']['title'])) {
                    echo "   📝 Título: " . $jsonContent['title']['title'] . "\n";
                }
            }
        }
    }

    echo "\n🎉 ¡Sitios generados exitosamente!\n";
    echo "Puedes acceder a ellos en:\n";
    echo "https://fordlapiedad.ebookford.com\n";
    echo "https://fordcavsamotors.ebookford.com\n";
} else {
    echo "❌ Error: " . $result['message'] . "\n";
    echo "Revisa el archivo error_log para más detalles.\n";
}

echo "\n=== FIN ===";
?>
