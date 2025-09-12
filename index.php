<?php
// Habilita el informe de errores para la depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carga de dependencias de Composer
require './vendor/autoload.php';

// Carga las variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable('./');
$dotenv->load();

// Incluye el archivo de funciones
require_once(dirname(__FILE__).'/functions.php');

// Determina el nombre del campo de entrada para la subida de archivos
$inputFileName = 'zip_file';

// -------------------------------------------------------------------------
// LÓGICA DE SUBIDA DE ARCHIVOS (BACKEND)
// Se ejecuta si es una solicitud POST y se está subiendo un archivo
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES[$inputFileName]["name"])) {
    header('Content-Type: application/json');
    $functions = new functions();
    $process = $functions->uploadSites();
    echo json_encode($process);
    die();
}

// -------------------------------------------------------------------------
// LÓGICA DE VISUALIZACIÓN DE SITIOS (FRONTEND)
// Se ejecuta si no se está subiendo un archivo
// -------------------------------------------------------------------------

// Detecta el subdominio dinámicamente
$host = $_SERVER['HTTP_HOST'];
$subdomain = explode('.', $host)[0];

// Lista de subdominios válidos
$validSubdomains = [
    'colima',
    'jalisco',
    'monterrey',
    'obregon',
    'caborca',
    'culiacan',
    'lapiedad',
    'tepepan',
    'zaragoza',
    'mexicali',
    // Agrega aquí todos tus subdominios nuevos
];

// Si el subdominio no es válido, redirigir al dominio principal o cargar el backend
if (!in_array($subdomain, $validSubdomains)) {
    // Si la solicitud es al dominio principal (ebookford.com o www.ebookford.com),
    // muestra la interfaz de carga de archivos (tu HTML original).
    if ($host === 'ebookford.com' || $host === 'www.ebookford.com' || $host === 'fordebookadmin-ebook-client.ppm09i.easypanel.host') {
        // Incluye aquí el HTML de tu formulario de carga
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Ford ebook backend</title>
  <link rel="stylesheet" type="text/css" href="progress_style.css">
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
  <script type="text/javascript" src="upload_progress.js"></script>
</head>
<body>
<form enctype="multipart/form-data" method="post" action="#" id="loadFileForm">
  <label>Elige el archivo a subir: <input type="file" name="<?php echo $inputFileName ?>" /></label>
  <br />
  <input type="submit" name="submit" value="Upload" onclick='upload_image();' />
</form>
<div class='progress' id="progress_div">
<div class='bar' id='bar'></div>
<div class='percent' id='percent'>0%</div>
</div>
<div id='results'></div>
</body>
</html>
<?php
    } else {
        // Para cualquier otro subdominio no válido, muestra un error 404
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>El subdominio '{$subdomain}' no es válido o no está en la lista.</p>";
    }
    exit;
}

// -------------------------------------------------------------------------
// ENRUTAMIENTO Y SERVICIO DE SITIOS DINÁMICOS
// Se ejecuta si el subdominio es válido
// -------------------------------------------------------------------------

// Ruta al contenido del subdominio
$sitePath = __DIR__ . "/activos/{$subdomain}";

// Verificar si existe el directorio
if (!is_dir($sitePath)) {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>El sitio no fue encontrado para: " . $subdomain . "</p>";
    exit;
}

// Servir el index.html del subdominio
$indexFile = $sitePath . '/index.html';
if (file_exists($indexFile)) {
    readfile($indexFile);
} else {
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1><p>Archivo index.html no encontrado para: " . $subdomain . "</p>";
}
?>
