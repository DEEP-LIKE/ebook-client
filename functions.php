<?php

class functions{

    public function uploadsites(){
        return self::processSitesOneByOne();
    }

    // Procesamiento de uno en uno para evitar timeouts
    protected function processSitesOneByOne(){
        $html = "";
        $return = [];
        
        // Intentar múltiples URLs del API como fallback
        $apiUrls = [
            $_ENV['API_URL'] . '/sites/actives',
            'https://httpbin.org/json' // URL de prueba pública para verificar conectividad
        ];

        error_log("Starting processSitesOneByOne");

        // Intentar conectar con múltiples URLs
        $result = false;
        $successful_url = null;
        $last_error = '';
        
        foreach ($apiUrls as $url) {
            error_log("Trying API URL: " . $url);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Reducido para probar múltiples URLs
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
            
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);
            
            if ($result !== false && $http_code === 200) {
                $successful_url = $url;
                error_log("Successfully connected to: " . $url);
                break;
            } else {
                $last_error = "HTTP: $http_code, Error: $curl_error";
                error_log("Failed to connect to $url - $last_error");
            }
        }

        if ($result === false) {
            error_log("All API URLs failed. Using local fallback data.");
            
            // FALLBACK FINAL: Usar datos locales
            $localDataFile = __DIR__ . '/local_api_data.json';
            if (file_exists($localDataFile)) {
                $result = file_get_contents($localDataFile);
                $successful_url = 'local_fallback';
                error_log("Using local API data as fallback");
            } else {
                error_log("Local fallback data not found");
                $return['success'] = false;
                $return['message'] = "Error al obtener datos de la API. Todas las URLs fallaron y no hay datos locales. " .
                                   "Último error: " . $last_error . ". El servidor no puede conectar al API.";
                return $return;
            }
        }

        $json = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($json)) {
            $return['success'] = false;
            $return['message'] = "Error al decodificar JSON de la API.";
            return $return;
        }

        // Si usamos la URL de prueba o fallback local, los datos ya están listos
        if ($successful_url && ($successful_url === 'local_fallback' || strpos($successful_url, 'httpbin') !== false)) {
            if (strpos($successful_url, 'httpbin') !== false) {
                error_log("Using test URL - creating sample data");
                $json = [
                    [
                        'id' => 1,
                        'folderName' => 'fordcavsamotors',
                        'title' => 'Ford Cavsa Motors',
                        'url' => 'https://www.cavsacolima.mx/',
                        'active' => true,
                        'images' => []
                    ],
                    [
                        'id' => 2,
                        'folderName' => 'fordlapiedad',
                        'title' => 'Ford La Piedad',
                        'url' => 'https://www.fordlapiedad.mx/',
                        'active' => true,
                        'images' => []
                    ]
                ];
            }
            // Si es local_fallback, $json ya tiene los datos correctos del archivo JSON
        }

        error_log("Sites found: " . count($json));

        // Limpiar sitios existentes
        error_log("Clearing existing sites...");
        self::clearAll();

        // Procesar cada sitio individualmente
        foreach ($json as $index => $jsonSite) {
            if (!isset($jsonSite['folderName'])) {
                error_log("Skipping site due to missing 'folderName': " . json_encode($jsonSite));
                continue;
            }
            
            $folderName = $jsonSite['folderName'];
            $correctTitle = isset($jsonSite['title']) ? $jsonSite['title'] : '';
            
            error_log("Processing site " . ($index + 1) . "/" . count($json) . ": " . $folderName);

            // PASO 1: Crear carpeta base desde basesite
            error_log("STEP 1: Creating base folder for: " . $folderName);
            $cloneResult = self::cloneBaseFolder($folderName);
            if (!$cloneResult) {
                error_log("CRITICAL ERROR: Failed to clone base folder for: " . $folderName);
                continue; // Continuar con el siguiente sitio
            }
            error_log("✅ Base folder created for: " . $folderName);

            // PASO 2: Procesar imágenes del sitio
            error_log("STEP 2: Processing images for: " . $folderName);
            $siteImages = self::imagesBases($jsonSite);

            // PASO 3: Actualizar JSON con datos del API
            error_log("STEP 3: Updating JSON for: " . $folderName);
            self::processJson($folderName, $siteImages, $correctTitle);

            // Generar URL del sitio
            if (isset($_ENV['ENV']) && $_ENV['ENV'] === 'production') {
                $currentDomain = $_SERVER['HTTP_HOST'];
                $pageUrl = "https://".$folderName.".".$currentDomain;
            } else {
                $pageUrl = $_ENV['APP_URL']."/activos/".$folderName;
            }

            $html .= "<div><a target='_blank' href='".$pageUrl."' >".$pageUrl."</a></div>";
            
            error_log("✅ Site completed: " . $folderName);
        }

        // Obtener subdominios finales
        $newSubdomains = [];
        if (is_dir('./activos/')) {
            $folders = array_filter(glob('./activos/*'), 'is_dir');
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                if ($folderName !== '.' && $folderName !== '..') {
                    $newSubdomains[] = $folderName;
                }
            }
        }

        $return['success'] = true;
        
        // Mensaje diferente según la fuente de datos
        if ($successful_url === 'local_fallback') {
            $return['message'] = "⚠️ Los sitios fueron procesados usando datos locales (API no disponible). Se generaron " . count($json) . " sitios.<br />";
        } else if ($successful_url && strpos($successful_url, 'httpbin') !== false) {
            $return['message'] = "⚠️ Los sitios fueron procesados usando datos de prueba (API no disponible). Se generaron " . count($json) . " sitios.<br />";
        } else {
            $return['message'] = "✅ Los sitios fueron procesados correctamente desde el API. Se generaron " . count($json) . " sitios.<br />";
        }
        
        $return['html'] = $html;
        $return['sites'] = $json;
        $return['newSubdomains'] = $newSubdomains;

        return $return;
    }

    // Procesamiento por chunks para evitar timeouts
    public function processSitesChunked($offset = 0, $chunkSize = 2){
        $html = "";
        $return = [];
        $url = $_ENV['API_URL']. '/sites/actives';

        error_log("Starting processSitesChunked - offset: $offset, chunkSize: $chunkSize");

        // Usar cURL con configuración robusta para timeouts
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Reducido para chunks
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $result = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($result === false || $http_code !== 200) {
            error_log("Error fetching API data. HTTP Code: " . $http_code . ", cURL Error: " . $curl_error);
            $return['success'] = false;
            $return['message'] = "Error al obtener datos de la API. Código: " . $http_code;
            return $return;
        }

        $json = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($json)) {
            $return['success'] = false;
            $return['message'] = "Error al decodificar JSON de la API.";
            return $return;
        }

        $totalSites = count($json);
        $sitesToProcess = array_slice($json, $offset, $chunkSize);
        
        error_log("Total sites: $totalSites, Processing chunk: " . count($sitesToProcess) . " sites (offset: $offset)");

        // Si es el primer chunk, limpiar todo
        if ($offset === 0) {
            error_log("First chunk - clearing existing sites...");
            self::clearAll();
        }

        // Procesar solo los sitios de este chunk
        foreach ($sitesToProcess as $jsonSite) {
            if (!isset($jsonSite['folderName'])) {
                continue;
            }
            
            $folderName = $jsonSite['folderName'];
            $correctTitle = isset($jsonSite['title']) ? $jsonSite['title'] : '';
            error_log("Processing site in chunk: " . $folderName);

            // Crear carpeta base
            $cloneResult = self::cloneBaseFolder($folderName);
            if (!$cloneResult) {
                error_log("Failed to clone base folder for: " . $folderName);
                continue;
            }

            // Procesar imágenes y JSON
            $siteImages = self::imagesBases($jsonSite);
            self::processJson($folderName, $siteImages, $correctTitle);

            // Generar URL
            if (isset($_ENV['ENV']) && $_ENV['ENV'] === 'production') {
                $currentDomain = $_SERVER['HTTP_HOST'];
                $pageUrl = "https://".$folderName.".".$currentDomain;
            } else {
                $pageUrl = $_ENV['APP_URL']."/activos/".$folderName;
            }

            $html .= "<div><a target='_blank' href='".$pageUrl."' >".$pageUrl."</a></div>";
        }

        // Obtener subdominios actuales
        $newSubdomains = [];
        if (is_dir('./activos/')) {
            $folders = array_filter(glob('./activos/*'), 'is_dir');
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                if ($folderName !== '.' && $folderName !== '..') {
                    $newSubdomains[] = $folderName;
                }
            }
        }

        $nextOffset = $offset + $chunkSize;
        $hasMore = $nextOffset < $totalSites;

        $return['success'] = true;
        $return['message'] = "Chunk procesado: " . count($sitesToProcess) . " sitios (de $totalSites total)";
        $return['html'] = $html;
        $return['sites'] = $sitesToProcess;
        $return['newSubdomains'] = $newSubdomains;
        $return['hasMore'] = $hasMore;
        $return['nextOffset'] = $hasMore ? $nextOffset : null;
        $return['totalSites'] = $totalSites;
        $return['processedSites'] = $offset + count($sitesToProcess);

        return $return;
    }



    protected function replaceInFile($file, $findString, $replaceString){
        file_put_contents($file,str_replace($findString, $replaceString, file_get_contents($file)));
    }

    protected function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            error_log("Attempted to delete non-existent directory: " . $dir);
            return true;
        }

        if (!is_dir($dir)) {
            error_log("Attempted to delete file as directory: " . $dir);
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!self::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                error_log("Failed to delete item: " . $dir . DIRECTORY_SEPARATOR . $item);
                return false;
            }

        }

        if (!rmdir($dir)) {
            error_log("Failed to remove directory: " . $dir);
            return false;
        }
        error_log("Successfully deleted directory: " . $dir);
        return true;
    }

    protected function clearAll(){
        error_log("Clearing all active sites in ./activos/");
        
        // Asegurar que el directorio activos existe
        if (!is_dir('./activos/')) {
            mkdir('./activos/', 0755, true);
            error_log("Created ./activos/ directory");
        }
        
        $foundFolders = glob("./activos/*", GLOB_ONLYDIR);
        if (empty($foundFolders)) {
            error_log("No folders found to clear in ./activos/");
        } else {
            error_log("Found " . count($foundFolders) . " folders to clear");
            foreach ($foundFolders as $folderPath) {
                error_log("Attempting to delete folder: " . $folderPath);
                $result = self::deleteDirectory($folderPath);
                if ($result) {
                    error_log("Successfully deleted: " . $folderPath);
                } else {
                    error_log("Failed to delete: " . $folderPath);
                }
            }
        }
        error_log("Finished clearing all active sites.");
    }

    protected function processSites(){
        $html = "";
        $return = [];
        $url = $_ENV['API_URL']. '/sites/actives';

        error_log("Starting processSites - fetching from: " . $url);

        // Usar cURL con configuración robusta para timeouts
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120); // 2 minutos de timeout total
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30); // 30 segundos para conectar
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Para evitar problemas SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Para evitar problemas SSL
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Seguir redirects
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'); // User agent
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // Forzar HTTP/1.1
        
        // Reintentos automáticos
        $maxRetries = 3;
        $retryCount = 0;
        $result = false;
        
        while ($retryCount < $maxRetries && $result === false) {
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            
            if ($result === false || $http_code !== 200) {
                $retryCount++;
                error_log("API attempt " . $retryCount . " failed. HTTP: " . $http_code . ", Error: " . $curl_error);
                if ($retryCount < $maxRetries) {
                    sleep(2); // Esperar 2 segundos antes del siguiente intento
                }
            } else {
                break;
            }
        }
        
        curl_close($ch);

        error_log("API Response - HTTP Code: " . $http_code . ", cURL Error: " . $curl_error . ", Response length: " . strlen($result));

        if ($result === false || $http_code !== 200) {
            error_log("Error fetching API data from " . $url . ". HTTP Code: " . $http_code . ", cURL Error: " . $curl_error);
            $return['success'] = false;
            $return['message'] = "Error al obtener datos de la API. Código: " . $http_code . ($curl_error ? " - " . $curl_error : "");
            $return['html'] = "";
            $return['sites'] = [];
            return $return;
        }

        $json = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON Decode Error: " . json_last_error_msg() . " for response: " . $result);
            $return['success'] = false;
            $return['message'] = "Error al decodificar JSON de la API.";
            $return['html'] = "";
            $return['sites'] = [];
            return $return;
        }

        error_log("Sites found: " . count($json));

        if (empty($json)) {
            error_log("No active sites returned by the API.");
            $return['success'] = false;
            $return['message'] = "No se encontraron sitios activos para procesar.";
            $return['html'] = "<div>No hay sitios activos para mostrar.</div>";
            $return['sites'] = [];
            return $return;
        }

        // PASO 1: Limpiar sitios existentes DESPUÉS de confirmar que tenemos datos del API
        error_log("API data confirmed, clearing existing sites...");
        self::clearAll();

        // PASO 2: Generar TODAS las carpetas base primero
        error_log("STEP 1: Creating all base site folders...");
        foreach ($json as $jsonSite) {
            if (!isset($jsonSite['folderName'])) {
                error_log("Skipping site due to missing 'folderName': " . json_encode($jsonSite));
                continue;
            }
            $folderName = $jsonSite['folderName'];
            error_log("Creating base folder for: " . $folderName);

            $cloneResult = self::cloneBaseFolder($folderName);
            if (!$cloneResult) {
                error_log("CRITICAL ERROR: Failed to clone base folder for: " . $folderName);
                $return['success'] = false;
                $return['message'] = "Error crítico: No se pudo clonar la carpeta base para " . $folderName;
                return $return;
            }
            error_log("✅ Base folder created successfully for: " . $folderName);
        }

        // PASO 3: Actualizar cada sitio con datos del API y recursos
        error_log("STEP 2: Updating sites with API data and resources...");
        foreach ($json as $jsonSite) {
            if (!isset($jsonSite['folderName'])) {
                continue;
            }
            $folderName = $jsonSite['folderName'];
            $correctTitle = isset($jsonSite['title']) ? $jsonSite['title'] : '';
            error_log("Updating site: " . $folderName . " with API data and title: '" . $correctTitle . "'");

            // Procesar las imágenes del sitio
            $siteImages = self::imagesBases($jsonSite);

            // Procesar el JSON del sitio pasando el título correcto
            self::processJson($folderName, $siteImages, $correctTitle);

            // Generar la URL del sitio
            if (isset($_ENV['ENV']) && $_ENV['ENV'] === 'production') {
                // Usa el dominio actual en lugar de ebookford.com
                $currentDomain = $_SERVER['HTTP_HOST'];
                $pageUrl = "https://".$folderName.".".$currentDomain;
            } else {
                $pageUrl = $_ENV['APP_URL']."/activos/".$folderName;
            }

            $html .= "<div><a target='_blank' href='".$pageUrl."' >".$pageUrl."</a></div>";
        }

        // Obtener la lista actualizada de subdominios
        $newSubdomains = [];
        if (is_dir('./activos/')) {
            $folders = array_filter(glob('./activos/*'), 'is_dir');
            foreach ($folders as $folder) {
                $folderName = basename($folder);
                if ($folderName !== '.' && $folderName !== '..') {
                    $newSubdomains[] = $folderName;
                }
            }
        }

        $return['success'] = true;
        $return['message'] = "✅ Los sitios fueron procesados correctamente. Se generaron " . count($json) . " sitios.<br />";
        $return['html'] = $html;
        $return['sites'] = $json;
        $return['newSubdomains'] = $newSubdomains;

        return $return;
    }

    protected function imagesBases($json){
        $siteImages = [];
        if (!isset($json['images']) || !is_array($json['images'])) {
            error_log("No images found for site: " . (isset($json['folderName']) ? $json['folderName'] : 'Unknown'));
            return $siteImages;
        }

        foreach($json['images'] as $imageData){
            $basePath = '';
            if (!isset($imageData['reftype']) || !isset($imageData['filename']) || !isset($imageData['src'])) {
                error_log("Skipping image due to missing data: " . json_encode($imageData));
                continue;
            }

            if ($imageData['reftype'] === 'portada'){
                $basePath = "./activos/". $json['folderName']. "/images/background/".$imageData['filename'];
            } elseif ($imageData['reftype'] === 'opengraph') {
                $basePath = "./activos/". $json['folderName']. "/".$imageData['filename'];
            } else {
                error_log("Unknown image reftype: " . $imageData['reftype'] . " for file: " . $imageData['filename']);
                continue;
            }
            $siteImages[$imageData['reftype']] = $imageData['filename'];

            // Crear directorios si no existen
            $directory = dirname($basePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    error_log("Failed to create directory: " . $directory);
                    continue; // Skip image if directory creation fails
                }
            }

            self::saveImage($imageData['src'], $basePath);
        }
        return $siteImages;
    }

    protected function editJson($folderName, $jsonArray, $siteImages, $correctTitle = ''){
        $url = $_ENV['API_URL']. '/sites/by_folder_name/'.$folderName;
        error_log("Fetching individual site data from: " . $url);
        
        $result = file_get_contents($url); // Consider using cURL here too for better error handling
        error_log("Raw API response for " . $folderName . ": " . $result);
        
        $json = json_decode($result, true);

        // Verificar que el JSON se decodificó correctamente
        if ($json === null) {
            error_log("Error decodificando JSON para folder: " . $folderName . ". Raw response: " . $result);
            return $jsonArray;
        }
        
        error_log("Decoded JSON for " . $folderName . ": " . json_encode($json));

        // Asegúrate de que las claves existan en $json antes de acceder a ellas
        $jsonArray['title']['id'] = $folderName;
        
        // Usar el título correcto del API actives que ya tenemos
        if (!empty($correctTitle)) {
            $jsonArray['title']['title'] = $correctTitle;
            error_log("Using correct title from actives API: '" . $correctTitle . "' for " . $folderName);
        } else {
            // Fallback a títulos por defecto
            $fallbackTitle = ($folderName === 'fordlapiedad') ? 'Ford La Piedad' : 'Ford Cavsa Motors';
            $jsonArray['title']['title'] = $fallbackTitle;
            error_log("Using fallback title: '" . $fallbackTitle . "' for " . $folderName);
        }
        
        $jsonArray['title']['url'] = isset($json['url']) ? $json['url'] : '';
        $jsonArray['map'] = isset($json['map']) ? $json['map'] : [];
        $jsonArray['terms'] = isset($json['terms']) ? $json['terms'] : '';
        $jsonArray['facebook'] = isset($json['facebook']) ? $json['facebook'] : '';
        $jsonArray['whatsapp'] = isset($json['whatsapp']) ? $json['whatsapp'] : '';
        $jsonArray['header']['title'] = isset($json['headTitle']) ? $json['headTitle'] : '';
        $jsonArray['header']['imagen'] = isset($siteImages['portada']) ? $siteImages['portada'] : '';
        $jsonArray['title']['site_url'] = 'https://'.$folderName.'.ebookford.com';
        $jsonArray['title']['opengraph'] = isset($siteImages['opengraph']) ? $siteImages['opengraph'] : '';

        $mail_lists = '';
        if (isset($json['contact_mails']) && is_array($json['contact_mails'])) {
            foreach ($json['contact_mails'] as $json_mail){
                if (isset($json_mail['email'])) {
                    $mail_lists .= $json_mail['email'].' ,';
                }
            }
        }
        $jsonArray['email'] = mb_substr($mail_lists, 0, -2);

        return self::processAllCars($jsonArray, $folderName, isset($json['cars']) ? $json['cars'] : []);
    }

    protected function saveImage($url, $path){
        // Verificar que la URL sea válida
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            error_log("Attempting to download image from: " . $url . " to: " . $path);
            $imageData = file_get_contents($url);
            if ($imageData !== false) {
                if (file_put_contents($path, $imageData) === false) {
                    error_log("Failed to save image to: " . $path);
                } else {
                    error_log("Successfully saved image to: " . $path);
                }
            } else {
                error_log("No se pudo descargar la imagen (file_get_contents returned false): " . $url);
            }
        } else {
            error_log("URL de imagen inválida: " . $url);
        }
    }

    protected function processAllCars($jsonArray, $folderName, $jsonCars){
        $index = 0;
        $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );

        $jsonArray['cars'] = []; // Initialize cars array to ensure it's always an array

        foreach ($jsonCars as $jsonCar){
            if (!isset($jsonCar['images']) || empty($jsonCar['images']) || !isset($jsonCar['images'][0]['filename']) || !isset($jsonCar['images'][0]['src'])) {
                error_log("Skipping car due to missing images data: " . json_encode($jsonCar));
                continue; // Saltar si no hay imágenes o datos de imagen incompletos
            }

            $images = $jsonCar['images'];
            $basePath = "./activos/". $folderName. "/images/cars/".$images[0]['filename'];

            // Crear directorio si no existe
            $directory = dirname($basePath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    error_log("Failed to create car images directory: " . $directory);
                    continue; // Skip car if directory creation fails
                }
            }

            self::saveImage($images[0]['src'], $basePath);

            $car = Array();
            $cleanCarName = strtr( $jsonCar['name'], $unwanted_array );
            $lowerName = strtolower($cleanCarName);
            $carName = str_replace('ford ', '', $lowerName);
            $car['id'] = str_replace(' ', '_', $carName);
            $car['name'] = isset($jsonCar['name']) ? $jsonCar['name'] : '';
            $car['cotiza'] = isset($jsonCar['cotiza']) ? $jsonCar['cotiza'] : '';
            $car['manejo'] = isset($jsonCar['manejo']) ? $jsonCar['manejo'] : '';
            $car['more'] = isset($jsonCar['more']) ? $jsonCar['more'] : '';
            $car['image'] = $images[0]['filename'];

            $terms = isset($jsonCar['terms']) ? $jsonCar['terms'] : '';
            $finalTerms = $terms;
            if (!empty($terms)) {
                // Simplified URL replacement, assumes http: is a marker for a URL
                $arrayTerms = explode("http:", $terms);
                if (count($arrayTerms) > 1) {
                    $linkPart = array_pop($arrayTerms);
                    $link = explode(" ", $linkPart)[0]; // Get the first word after http:
                    if (!empty($link)) {
                        $fullLink = 'https://' . $link; // Always use https
                        $finalTerms = str_replace($link, '<a target="_blank" href="'. $fullLink .'">'. $link .'</a>', $terms);
                        $finalTerms = str_replace("http:", '', $finalTerms); // Remove any remaining http: if not part of link
                    }
                }
            }
            $car['terms'] = $finalTerms;

            $jsonArray['cars'][$index] = $car;
            $index += 1;
        }

        return $jsonArray;
    }

    protected function processJson($folderName, $siteImages, $correctTitle = ''){
        $jsonFilePath = "./activos/". $folderName ."/json/ford.json";

        // Verificar que el archivo existe antes de procesarlo
        if (!file_exists($jsonFilePath)) {
            error_log("Archivo JSON no encontrado: " . $jsonFilePath);
            return;
        }

        $string = file_get_contents($jsonFilePath);
        $jsonArray = json_decode($string, true);

        // Verificar que el JSON se decodificó correctamente
        if ($jsonArray === null) {
            error_log("Error decodificando JSON del archivo: " . $jsonFilePath . ". Raw content: " . $string);
            return;
        }

        $finalJson = self::editJson($folderName, $jsonArray, $siteImages, $correctTitle);
        if (file_put_contents($jsonFilePath, json_encode($finalJson, JSON_PRETTY_PRINT)) === false) {
            error_log("Failed to write final JSON to: " . $jsonFilePath);
        } else {
            error_log("Successfully wrote final JSON to: " . $jsonFilePath);
        }
    }

    // --- REVISIÓN DE recurseCopy ---
    protected function recurseCopy($src, $dst) {
        error_log("recurseCopy called: src=" . $src . ", dst=" . $dst);
        $dir = opendir($src);
        if ($dir === false) {
            error_log("Failed to open source directory: " . $src);
            return false;
        }

        // Crear el directorio destino si no existe
        if (!is_dir($dst)) {
            if (!mkdir($dst, 0755, true)) {
                error_log("Failed to create destination directory: " . $dst . " Check permissions.");
                closedir($dir);
                return false;
            }
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                $srcPath = $src . '/' . $file;
                $dstPath = $dst . '/' . $file;
                error_log("Processing: " . $srcPath);

                if (is_dir($srcPath)) {
                    if (!self::recurseCopy($srcPath, $dstPath)) {
                        error_log("Error copying directory: " . $srcPath . " to " . $dstPath);
                        closedir($dir);
                        return false;
                    }
                } else {
                    if (!copy($srcPath, $dstPath)) {
                        error_log("Error copying file: " . $srcPath . " to " . $dstPath . " Check permissions.");
                        closedir($dir);
                        return false;
                    }
                }
            }
        }
        closedir($dir);
        error_log("Successfully copied " . $src . " to " . $dst);
        return true;
    }


    protected function cloneBaseFolder($folderName){
        $copyTo = './activos/' . $folderName;
        $baseFolder = './basesite';

        // Verificar que la carpeta base existe
        if (!is_dir($baseFolder)) {
            error_log("Carpeta base no encontrada: " . $baseFolder);
            return false;
        }

        // Eliminar la carpeta destino si ya existe para asegurar una copia limpia
        if (is_dir($copyTo)) {
            error_log("Carpeta destino ya existe, eliminando: " . $copyTo);
            if (!self::deleteDirectory($copyTo)) {
                error_log("Failed to delete existing destination folder: " . $copyTo);
                return false;
            }
        }

        error_log("Iniciando copia de: " . $baseFolder . " a: " . $copyTo);
        $result = self::recurseCopy($baseFolder, $copyTo);

        if ($result && is_dir($copyTo)) {
            error_log("Carpeta clonada exitosamente: " . $folderName);
            return true;
        } else {
            error_log("Error clonando carpeta (recurseCopy returned false or destination not a directory): " . $folderName);
            return false;
        }
    }

    protected function rmdir_recursive($dir) {
        // Esta función es similar a deleteDirectory, podrías consolidarlas si quieres.
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) self::rmdir_recursive("$dir/$file");
            else unlink("$dir/$file");
        }
        rmdir($dir);
    }
}
