<?php
require __DIR__.'/vendor/autoload.php';
require_once 'functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== SIMULACIÓN DEL SERVIDOR (SIN CONECTIVIDAD AL API) ===\n\n";

// Simular el ambiente del servidor modificando temporalmente la URL del API
$originalApiUrl = $_ENV['API_URL'];
$_ENV['API_URL'] = 'https://api-que-no-existe-para-simular-fallo.com';

echo "🔧 Simulando fallo de conectividad...\n";
echo "API URL simulada: " . $_ENV['API_URL'] . "/sites/actives\n\n";

$functions = new functions();
$result = $functions->uploadsites();

// Restaurar URL original
$_ENV['API_URL'] = $originalApiUrl;

echo "📊 RESULTADOS:\n";
echo "Estado: " . ($result['success'] ? 'ÉXITO' : 'ERROR') . "\n";
echo "Mensaje: " . strip_tags($result['message']) . "\n";

if ($result['success']) {
    echo "Sitios generados: " . count($result['sites']) . "\n";
    echo "Subdominios: " . implode(', ', $result['newSubdomains']) . "\n\n";
    
    echo "✅ VERIFICACIÓN DE CARPETAS:\n";
    if (is_dir('./activos/')) {
        $folders = array_filter(glob('./activos/*'), 'is_dir');
        foreach ($folders as $folder) {
            $folderName = basename($folder);
            echo "📁 " . $folderName . "\n";
            
            // Verificar JSON
            $jsonFile = $folder . '/json/ford.json';
            if (file_exists($jsonFile)) {
                $jsonContent = json_decode(file_get_contents($jsonFile), true);
                if (isset($jsonContent['title']['title'])) {
                    echo "   📝 Título: " . $jsonContent['title']['title'] . "\n";
                } else {
                    echo "   ❌ Sin título en JSON\n";
                }
            } else {
                echo "   ❌ JSON no encontrado\n";
            }
            
            // Verificar estructura básica
            $essentials = ['index.php', 'style', 'js', 'images', 'fonts'];
            foreach ($essentials as $item) {
                $exists = file_exists($folder . '/' . $item) || is_dir($folder . '/' . $item);
                echo "   " . ($exists ? '✅' : '❌') . " " . $item . "\n";
            }
            echo "\n";
        }
    }
    
    echo "🎉 SIMULACIÓN EXITOSA: El sistema funcionará en el servidor incluso sin conectividad al API.\n";
} else {
    echo "❌ SIMULACIÓN FALLIDA: " . $result['message'] . "\n";
}

echo "\n=== FIN DE SIMULACIÓN ===\n";
?>
