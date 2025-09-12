<?php

// Lista de subdominios válidos.
// Estos subdominios serán los que el enrutador PHP reconozca.
define('VALID_SUBDOMAINS', [
    'fordandradelaviga',
    'fordandradetepepan', 
    'fordandradezaragoza',
    'fordatasa',
    'fordautopasionmexicali',
    'fordbajacai',
    'fordcaborca',
    'fordcavsamotors',
    'fordcever',
    'fordcoatzacoalcos',
    'fordcolima',
    'fordculiacan',
    'fordgema',
    'fordlapiedad',
    'fordmylsaqueretaro',
    'fordobregon',
    'fordpicacho',
    'fordsumanbc',
    'fordsumanlavilla',
    'fordsumanmexico',
    'fordvistahermosa'
]);

// Dominios principales que servirán el panel de administración.
// Incluye el dominio original de EasyPanel para pruebas.
define('MAIN_DOMAINS', [
    'ebookford.com',
    'www.ebookford.com',
    'fordebookadmin-ebook-client.ppm09i.easypanel.host'
]);

// Configuración de rutas de directorios.
// __DIR__ se refiere a la ubicación del archivo constants.php.
// BASE_PATH es la raíz de tu proyecto (/app/).
define('BASE_PATH', __DIR__ . '/../');
define('ACTIVOS_PATH', BASE_PATH . 'activos/');
define('VENDOR_PATH', BASE_PATH . 'vendor/');
