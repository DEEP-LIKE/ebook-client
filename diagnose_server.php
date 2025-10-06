<?php
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== DIAGNÓSTICO DE CONECTIVIDAD DEL SERVIDOR ===\n\n";

$url = $_ENV['API_URL'] . '/sites/actives';
echo "API URL: " . $url . "\n";
echo "Server: " . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'CLI') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "cURL Version: " . curl_version()['version'] . "\n\n";

// Test 1: Verificar si cURL está habilitado
if (!function_exists('curl_init')) {
    echo "❌ cURL no está disponible en este servidor\n";
    exit(1);
}
echo "✅ cURL está disponible\n";

// Test 2: Verificar conectividad básica
echo "\n=== TEST DE CONECTIVIDAD ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capturar información verbose
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$result = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
$curl_info = curl_getinfo($ch);

// Leer información verbose
rewind($verbose);
$verbose_log = stream_get_contents($verbose);
fclose($verbose);

curl_close($ch);

echo "HTTP Code: " . $http_code . "\n";
echo "cURL Error: " . ($curl_error ?: 'None') . "\n";
echo "Response Length: " . strlen($result) . " bytes\n";
echo "Total Time: " . $curl_info['total_time'] . " seconds\n";
echo "Connect Time: " . $curl_info['connect_time'] . " seconds\n";
echo "DNS Lookup Time: " . $curl_info['namelookup_time'] . " seconds\n";

if ($verbose_log) {
    echo "\n=== VERBOSE LOG ===\n";
    echo $verbose_log . "\n";
}

if ($result === false || $http_code !== 200) {
    echo "\n❌ CONEXIÓN FALLIDA\n";
    echo "Posibles causas:\n";
    echo "1. Firewall del servidor bloqueando conexiones salientes\n";
    echo "2. DNS no puede resolver el dominio del API\n";
    echo "3. SSL/TLS issues\n";
    echo "4. Proxy o configuración de red\n";
    echo "5. El API está caído\n\n";
    
    // Test alternativo sin SSL
    echo "=== TEST SIN SSL ===\n";
    $url_http = str_replace('https://', 'http://', $url);
    echo "Probando: " . $url_http . "\n";
    
    $ch2 = curl_init();
    curl_setopt($ch2, CURLOPT_URL, $url_http);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch2, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch2, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, true);
    
    $result2 = curl_exec($ch2);
    $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    $curl_error2 = curl_error($ch2);
    curl_close($ch2);
    
    echo "HTTP Code (HTTP): " . $http_code2 . "\n";
    echo "cURL Error (HTTP): " . ($curl_error2 ?: 'None') . "\n";
    
} else {
    echo "\n✅ CONEXIÓN EXITOSA\n";
    $json = json_decode($result, true);
    if ($json !== null) {
        echo "Sites encontrados: " . count($json) . "\n";
        foreach ($json as $site) {
            echo "  - " . $site['folderName'] . " (" . $site['title'] . ")\n";
        }
    } else {
        echo "❌ Error decodificando JSON\n";
        echo "Respuesta (primeros 200 chars): " . substr($result, 0, 200) . "\n";
    }
}

echo "\n=== DIAGNÓSTICO COMPLETO ===\n";
?>
