<?php
// Script para probar la generación de sitios manualmente

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/functions.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== INICIANDO GENERACIÓN DE SITIOS ===\n\n";

$functions = new functions();
$result = $functions->uploadsites();

echo "=== RESULTADO ===\n";
echo "Message: " . ($result['message'] ?? 'No message') . "\n";
echo "HTML: " . ($result['html'] ?? 'No HTML') . "\n";
echo "Sites: " . (isset($result['sites']) ? count($result['sites']) : 0) . "\n\n";

if (isset($result['sites']) && is_array($result['sites'])) {
    foreach ($result['sites'] as $site) {
        echo "Site: " . ($site['folderName'] ?? 'Unknown') . "\n";
    }
}

echo "\n=== VERIFICANDO CARPETAS CREADAS ===\n";
if (is_dir('./activos')) {
    $folders = array_filter(glob('./activos/*'), 'is_dir');
    echo "Total de carpetas en activos: " . count($folders) . "\n";
    foreach ($folders as $folder) {
        $folderName = basename($folder);
        $itemCount = count(scandir($folder)) - 2; // Excluir . y ..
        echo "  - " . $folderName . " (" . $itemCount . " items)\n";
    }
} else {
    echo "La carpeta activos no existe\n";
}

echo "\n=== PROCESO COMPLETADO ===\n";
?>
