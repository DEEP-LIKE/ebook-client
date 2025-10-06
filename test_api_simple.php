<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== TEST SIMPLE DE API ===\n";

$url = $_ENV['API_URL'] . '/sites/actives';
echo "URL: " . $url . "\n";

// Test con la nueva función
require_once 'functions.php';
$functions = new functions();
$result = $functions->uploadsites();

echo "Resultado: " . ($result['success'] ? 'ÉXITO' : 'ERROR') . "\n";
echo "Mensaje: " . $result['message'] . "\n";

if ($result['success']) {
    echo "Sitios encontrados: " . count($result['sites']) . "\n";
} else {
    echo "Revisa los logs del servidor para más detalles.\n";
}
?>
