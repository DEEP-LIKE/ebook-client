<?php

// Habilita el informe de errores para la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carga la configuración
// Se asume que este archivo existe en /app/config/constants.php
require_once __DIR__ . '/config/constants.php';

// Carga de dependencias de Composer
require VENDOR_PATH . 'autoload.php';

// Carga las variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Incluye el archivo de funciones
require_once BASE_PATH . 'functions.php';

// Determina el nombre del campo de entrada para la subida de archivos
$inputFileName = 'zip_file';

// -------------------------------------------------------------------------
// LÓGICA DE REGENERACIÓN DE SITIOS (BACKEND)
// Se ejecuta si es una solicitud POST (regenerar sitios desde API)
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suprimir warnings para evitar que interfieran con la respuesta JSON
    error_reporting(E_ERROR | E_PARSE);
    
    // Iniciar buffer de salida para capturar cualquier output no deseado
    ob_start();
    
    header('Content-Type: application/json');

    // Validación de seguridad mejorada
    $isAuthorized = false;
    $currentHost = $_SERVER['HTTP_HOST'];
    
    // 1. Permitir desde localhost
    if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        $isAuthorized = true;
        error_log("Access granted: localhost");
    }
    
    // 2. Permitir desde EasyPanel
    if (isset($_SERVER['HTTP_X_EASYPANEL_REQUEST'])) {
        $isAuthorized = true;
        error_log("Access granted: EasyPanel");
    }
    
    // 3. Permitir desde dominios autorizados (MAIN_DOMAINS)
    if (defined('MAIN_DOMAINS') && in_array($currentHost, MAIN_DOMAINS)) {
        $isAuthorized = true;
        error_log("Access granted: authorized domain " . $currentHost);
    }
    
    // 4. Permitir desde subdominios de ebookford.com
    if (strpos($currentHost, '.ebookford.com') !== false || $currentHost === 'ebookford.com') {
        $isAuthorized = true;
        error_log("Access granted: ebookford.com domain " . $currentHost);
    }
    
    // 5. Verificar token CSRF básico
    $expectedToken = md5(date('Y-m-d-H') . $currentHost);
    $providedToken = isset($_POST['security_token']) ? $_POST['security_token'] : '';
    $hasValidToken = ($providedToken === $expectedToken);
    
    if (!$hasValidToken) {
        error_log("Invalid security token from: " . $currentHost . ". Expected: " . $expectedToken . ", Got: " . $providedToken);
        echo json_encode([
            'success' => false,
            'message' => 'Token de seguridad inválido. Recarga la página.'
        ]);
        exit;
    }
    
    if (!$isAuthorized) {
        error_log("Access denied from: " . $currentHost . " (IP: " . $_SERVER['REMOTE_ADDR'] . ")");
        echo json_encode([
            'success' => false, 
            'message' => 'Acceso no autorizado desde: ' . $currentHost
        ]);
        exit;
    }

    // Rate limiting básico (opcional)
    $rateLimitFile = sys_get_temp_dir() . '/regenerate_rate_limit.txt';
    $currentTime = time();
    $lastRequest = file_exists($rateLimitFile) ? (int)file_get_contents($rateLimitFile) : 0;
    
    if ($currentTime - $lastRequest < 30) { // 30 segundos entre requests
        echo json_encode([
            'success' => false,
            'message' => 'Demasiadas peticiones. Espera ' . (30 - ($currentTime - $lastRequest)) . ' segundos.'
        ]);
        exit;
    }
    
    file_put_contents($rateLimitFile, $currentTime);

    error_log("Starting site regeneration from authorized source: " . $currentHost);
    
    try {
        $functions = new functions();
        $process = $functions->uploadsites();
        
        // Asegurar que la respuesta sea válida
        if (!isset($process['success'])) {
            $process['success'] = true;
        }
        
        // Limpiar cualquier output no deseado
        ob_clean();
        
        $jsonResponse = json_encode($process);
        error_log("Sending JSON response: " . $jsonResponse);
        echo $jsonResponse;
        
    } catch (Exception $e) {
        // Limpiar cualquier output no deseado
        ob_clean();
        
        error_log("Error in site regeneration: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error interno: ' . $e->getMessage(),
            'html' => ''
        ]);
    }
    
    ob_end_flush();
    exit();
}

// -------------------------------------------------------------------------
// DETECCIÓN DE SUBDOMINIO Y ENRUTAMIENTO
// -------------------------------------------------------------------------
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];

// Obtener la lista dinámica de subdominios válidos
$validSubdomains = get_valid_subdomains();

// Si es un dominio principal, mostrar la interfaz de administración
if (in_array($host, MAIN_DOMAINS)) {
    display_admin_interface($inputFileName, $validSubdomains);
    exit;
}

// Si es un subdominio válido, servir el sitio correspondiente
if (in_array($subdomain, $validSubdomains)) {
    serve_subdomain_site($subdomain);
    exit;
}

// Si no es ni dominio principal ni subdominio válido
http_response_code(404);
echo "<h1>404 Not Found</h1>";
echo "<p>El subdominio '{$subdomain}' no existe.</p>";
exit;

// -------------------------------------------------------------------------
// FUNCIONES AUXILIARES
// -------------------------------------------------------------------------
/**
 * Obtiene la lista de subdominios válidos leyendo los directorios en ACTIVOS_PATH.
 * @return array
 */
function get_valid_subdomains() {
    $subdomains = [];
    if (is_dir(ACTIVOS_PATH)) {
        $folders = array_filter(glob(ACTIVOS_PATH . '*'), 'is_dir');
        foreach ($folders as $folder) {
            $subdomains[] = basename($folder);
        }
    }
    return $subdomains;
}

function display_admin_interface($inputFileName, $validSubdomains) {
    // HTML para la interfaz de administración
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ford eBook - Administración</title>
    <link rel="stylesheet" href="progress_style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
    <script src="upload_progress.js"></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .container { max-width: 800px; margin: 0 auto; }
        .header { background: #003366; color: white; padding: 20px; border-radius: 5px; }
        .form-container { background: #f5f5f5; padding: 20px; border-radius: 5px; margin-top: 20px; }
        .progress { display: none; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Ford eBook - Panel de Administración</h1>
            <p>Gestión de sitios para concesionarios</p>
        </div>

        <div class="form-container">
            <h2>Regenerar sitios desde API</h2>
            <form method="post" action="#" id="loadFileForm">
                <p>Presiona el botón para regenerar todos los sitios activos desde el API:</p>
                
                <!-- Token de seguridad anti-bot -->
                <input type="hidden" id="security_token" value="<?php echo md5(date('Y-m-d-H') . $_SERVER['HTTP_HOST']); ?>" />
                
                <br />
                <input type="button" name="submit" value="Regenerar sitios" onclick='regenerate_sites();' />
                
                <!-- Opción adicional de seguridad -->
                <div style="margin-top: 15px; padding: 10px; background: #fff3cd; border-radius: 5px; border-left: 4px solid #ffc107;">
                    <label style="font-size: 12px;">
                        <input type="checkbox" id="require_file_security" checked /> 
                        <strong>Modo seguro (Recomendado):</strong> Requerir archivo para mayor seguridad contra bots
                    </label>
                    <div id="file_upload_section" style="display: block; margin-top: 10px;">
                        <label>Selecciona cualquier archivo pequeño (imagen, txt, etc.):
                            <input type="file" id="security_file" name="<?php echo $inputFileName ?>" accept="*/*" required />
                        </label>
                        <small style="color: #666; display: block; margin-top: 5px;">
                            ⚠️ <strong>Obligatorio:</strong> Este archivo es requerido para verificar que no eres un bot.
                        </small>
                    </div>
                </div>
            </form>
            
            <div style="margin-top: 20px; padding: 15px; background: #f0f8ff; border-radius: 5px; border-left: 4px solid #007cba;">
                <h3 style="margin-top: 0; color: #007cba;">💡 Información</h3>
                <p><strong>Si los sitios muestran "Sitio en construcción":</strong></p>
                <ul>
                    <li>Los sitios se crearon exitosamente pero con contenido básico</li>
                    <li>Esto ocurre cuando el API no está disponible</li>
                    <li>Los sitios aparecen en la lista y son accesibles</li>
                    <li>Intenta regenerar nuevamente cuando el API esté disponible</li>
                </ul>
            </div>

            <div class='progress' id="progress_div">
                <div class='bar' id='bar'></div>
                <div class='percent' id='percent'>0%</div>
            </div>
            <div id='results'></div>
        </div>

        <div style="margin-top: 30px; font-size: 14px; color: #666;">
            <p><strong>Subdominios activos:</strong></p>
            <ul>
                <?php foreach ($validSubdomains as $sub): ?>
                  <li>
                    <a href='https://<?php echo $sub; ?>.ebookford.com' target="_blank">
                        <?php echo $sub; ?>.ebookford.com
                    </a>
                  </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</body>
</html>
    <?php
}

function serve_subdomain_site($subdomain) {
    // USE THE FULL SUBDOMAIN NAME AS THE FOLDER
    $folderName = $subdomain;
    $sitePath = ACTIVOS_PATH . $folderName;

    // Check if the directory exists
    if (!is_dir($sitePath)) {
        http_response_code(404);
        echo "<h1>Sitio no encontrado</h1>";
        echo "<p>El sitio para <strong>{$subdomain}</strong> no está disponible.</p>";
        echo "<p>Ruta buscada: " . htmlspecialchars($sitePath) . "</p>";
        echo "<p>Contacta al administrador si crees que esto es un error.</p>";
        exit;
    }

    // Define the possible index files to check for
    $indexFiles = ['index.php', 'index.html'];
    $foundFile = null;

    foreach ($indexFiles as $file) {
        $filePath = $sitePath . '/' . $file;
        if (file_exists($filePath)) {
            $foundFile = $filePath;
            break;
        }
    }

    // If an index file is found, serve it
    if ($foundFile) {
        // Change to the site's directory to ensure relative paths work
        chdir($sitePath);

        // Include the file to execute the PHP code
        include $foundFile;
        exit;
    } else {
        http_response_code(404);
        echo "<h1>Archivo no encontrado</h1>";
        echo "<p>El archivo principal para <strong>{$subdomain}</strong> no existe.</p>";
        echo "<p>Ruta buscada: " . htmlspecialchars($sitePath) . "</p>";
        echo "<p>Rutas intentadas: " . htmlspecialchars($sitePath . '/index.php') . " y " . htmlspecialchars($sitePath . '/index.html') . "</p>";
        exit;
    }
}
?>
