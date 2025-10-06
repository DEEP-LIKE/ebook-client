<?php
// Este archivo debe subirse al servidor del API (ford-api-ford-api.ppm09i.easypanel.host)
// para que actúe como puente/proxy

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Solo permitir desde ebookford.com
$allowedOrigins = [
    'https://ebookford.com',
    'https://www.ebookford.com',
    'http://ebookford.com',
    'http://www.ebookford.com'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
$isAllowed = false;

foreach ($allowedOrigins as $allowed) {
    if (strpos($origin, $allowed) === 0) {
        $isAllowed = true;
        break;
    }
}

if (!$isAllowed && !empty($origin)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Obtener datos del API local
$localApiUrl = 'http://localhost/sites/actives'; // URL local en el mismo servidor
$result = @file_get_contents($localApiUrl);

if ($result === false) {
    // Fallback a la URL original si localhost no funciona
    $localApiUrl = 'https://ford-api-ford-api.ppm09i.easypanel.host/sites/actives';
    $result = @file_get_contents($localApiUrl);
}

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'API not available']);
    exit;
}

// Validar JSON
$json = json_decode($result, true);
if ($json === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid JSON response']);
    exit;
}

// Devolver los datos
echo $result;
?>
