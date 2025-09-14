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
        $copyJson = $json;
        $fileName = $json['header']['imagen']['filename'];
        $copyJson['header']['imagen'] = $fileName;
        $pathPortada = "./images/background/".$fileName;
        file_put_contents($pathPortada, file_get_contents($json['header']['imagen']['src']));
        $fileName = $json['title']['opengraph']['filename'];
        $copyJson['title']['opengraph'] = $fileName;
        $pathOpengrah = "./".$fileName;
        file_put_contents($pathOpengrah, file_get_contents($json['title']['opengraph']['src']));
        unset($copyJson['cars']);
        $cars = [];
        foreach ($json['cars'] as $car) {
            $cleanCarName = strtr( $car['name'], $unwanted_array );
            $lowerName = strtolower($cleanCarName);
            $carName = str_replace('ford ', '', $lowerName);
            $car['id'] = str_replace(' ', '_', $carName);
            $imagePath = "./images/cars/".$car['image'];
            file_put_contents($imagePath, file_get_contents($car['imageSrc']));
            $cars[] = $car;
        }
        $copyJson['cars'] = $cars;
        file_put_contents($jsonFilePath, json_encode($copyJson));
        $json = file_get_contents($path);
    }
    return json_decode($json);
}

/******** Sections ********/

//title
function get_title() {
    $json = read_json();
    $title = $json->title;
    return $title;
}
// header
function get_header() {
    $json = read_json();
    $header = $json->header;
    return $header;
}
// asesor 
function get_asesor() {
    $json = read_json();
    $asesor = $json->asesor;
    return $asesor;
}
// Cars 
function get_summary_cars() {
    $json = read_json();
    $cars =  $json->cars;
    return $cars;
}

// map 
function get_map() {
    $json = read_json();
    $map =  $json->map;
    return $map;
}
// map 
function get_terms() {
    $json = read_json();
    $terms =  $json->terms;
    return $terms;
}

//mails
function getEmails($path = './json/ford.json') {
    $json = read_json($path);
    $emails = $json->email;
    return $emails;
}

function get_whats() {
    $json = read_json();
    $whatsapp =  $json->whatsapp;
    return $whatsapp;
}

function get_face() {
    $json = read_json();
    $facebook =  $json->facebook;
    return $facebook;
}

function get_summary_promotion() {
    $json = read_json();
    if (isset($json->promos)) {
        return $json->promos;
    }
    // Si la propiedad 'promos' no existe, retorna un array vacío para evitar el error
    return [];
}

// Mapurl
function get_urlmap() {
    $json = read_json();
    $urlmap =  $json->urlmap;
    return $urlmap;
}

// waze 
function get_waze() {
    $json = read_json();
    $waze =  $json->waze;
    return $waze;
}
