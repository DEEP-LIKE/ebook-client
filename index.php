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
// LÓGICA DE SUBIDA DE ARCHIVOS (BACKEND)
// Se ejecuta si es una solicitud POST y se está subiendo un archivo
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES[$inputFileName]["name"])) {
    header('Content-Type: application/json');

    // Validación de seguridad adicional
    if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) &&
        !isset($_SERVER['HTTP_X_EASYPANEL_REQUEST'])) {
        echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
        exit;
    }

    $functions = new functions();
    $process = $functions->uploadSites();
    echo json_encode($process);
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
            <h2>Subir nuevo sitio</h2>
            <form enctype="multipart/form-data" method="post" action="#" id="loadFileForm">
                <label>Selecciona una imagen a subir:
                    <input type="file" name="<?php echo $inputFileName ?>" accept="image/*" />
                </label>
                <br /><br />
                <input type="submit" name="submit" value="Subir archivo" onclick='upload_image();' />
            </form>

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
