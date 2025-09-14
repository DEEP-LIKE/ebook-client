<?php
require '../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable('./');
$dotenv->load();

function read_json($path = './json/ford.json') {
    $json = file_get_contents($path);
    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );

    if (!$json) {
        $pathArray = explode('/', dirname(__FILE__));
        $folderName = $pathArray[count($pathArray)-2];
        $jsonFilePath = "./json/ford.json";
        $url = $_ENV['API_URL']. '/sites/json/'.$folderName;
        $result = file_get_contents($url);
        $json = json_decode($result, true);

        if ($json === null) {
            error_log("Error decodificando JSON. No se puede crear el archivo.");
            return (object)[]; // Devolver un objeto vacío para evitar errores
        }

        $copyJson = $json;

        // Asegúrate de que las propiedades del título existen antes de usarlas
        $titleData = isset($json['title']) ? $json['title'] : [];
        $headerData = isset($json['header']) ? $json['header'] : [];

        $fileName = isset($headerData['imagen']['filename']) ? $headerData['imagen']['filename'] : '';
        if ($fileName) {
            $copyJson['header']['imagen'] = $fileName;
            $pathPortada = "./images/background/".$fileName;
            file_put_contents($pathPortada, file_get_contents($headerData['imagen']['src']));
        }

        $fileName = isset($titleData['opengraph']['filename']) ? $titleData['opengraph']['filename'] : '';
        if ($fileName) {
            $copyJson['title']['opengraph'] = $fileName;
            $pathOpengrah = "./".$fileName;
            file_put_contents($pathOpengrah, file_get_contents($titleData['opengraph']['src']));
        }

        unset($copyJson['cars']);
        $cars = [];
        // Asegúrate de que los carros existen antes de iterar
        $jsonCars = isset($json['cars']) ? $json['cars'] : [];
        foreach ($jsonCars as $car) {
            $cleanCarName = strtr( $car['name'], $unwanted_array );
            $lowerName = strtolower($cleanCarName);
            $carName = str_replace('ford ', '', $lowerName);
            $car['id'] = str_replace(' ', '_', $carName);
            $imagePath = "./images/cars/".$car['image'];
            file_put_contents($imagePath, file_get_contents($car['imageSrc']));
            $cars[] = $car;
        }
        $copyJson['cars'] = $cars;

        // AÑADE ESTA LÍNEA PARA INCLUIR LA PROPIEDAD site_url
        $copyJson['title']['site_url'] = 'https://' . $folderName . '.ebookford.com';

        file_put_contents($jsonFilePath, json_encode($copyJson));
        $json = file_get_contents($path);
    }
    return json_decode($json);
}

/******** Sections ********/

//title
function get_title() {
    $json = read_json();
    return isset($json->title) ? $json->title : (object) ['title' => ''];
}
// header
function get_header() {
    $json = read_json();
    return isset($json->header) ? $json->header : (object) ['title' => '', 'imagen' => ''];
}
// asesor
function get_asesor() {
    $json = read_json();
    return isset($json->asesor) ? $json->asesor : (object) ['name' => '', 'picture' => '', 'tel' => '', 'whats' => '', 'textw' => '', 'avalible' => false];
}
// Cars
function get_summary_cars() {
    $json = read_json();
    return isset($json->cars) ? $json->cars : [];
}

// map
function get_map() {
    $json = read_json();
    return isset($json->map) ? $json->map : '';
}
// terms
function get_terms() {
    $json = read_json();
    return isset($json->terms) ? $json->terms : '';
}

//mails
function getEmails($path = './json/ford.json') {
    $json = read_json($path);
    return isset($json->email) ? $json->email : [];
}

function get_whats() {
    $json = read_json();
    return isset($json->whatsapp) ? $json->whatsapp : '';
}

function get_face() {
    $json = read_json();
    return isset($json->facebook) ? $json->facebook : '';
}

// Promotion
function get_summary_promotion() {
    $json = read_json();
    return isset($json->promos) ? $json->promos : [];
}

// Mapurl
function get_urlmap() {
    $json = read_json();
    return isset($json->urlmap) ? $json->urlmap : '';
}

// waze
function get_waze() {
    $json = read_json();
    return isset($json->waze) ? $json->waze : '';
}