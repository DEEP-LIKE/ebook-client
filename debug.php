<?php
// Script de debugging para diagnosticar el problema

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "<h2>Debug Script - Diagnóstico de Copia de Carpetas</h2>";

// 1. Verificar que basesite existe y tiene contenido
echo "<h3>1. Verificando carpeta basesite:</h3>";
if (is_dir('./basesite')) {
    echo "✅ basesite existe<br>";
    $files = scandir('./basesite');
    echo "Contenido de basesite: " . implode(', ', array_filter($files, function($f) { return $f != '.' && $f != '..'; })) . "<br>";
    echo "Total de archivos/carpetas: " . (count($files) - 2) . "<br>";
} else {
    echo "❌ basesite NO existe<br>";
}

// 2. Verificar carpeta activos
echo "<h3>2. Verificando carpeta activos:</h3>";
if (is_dir('./activos')) {
    echo "✅ activos existe<br>";
    $files = scandir('./activos');
    $realFiles = array_filter($files, function($f) { return $f != '.' && $f != '..'; });
    echo "Contenido actual de activos: " . implode(', ', $realFiles) . "<br>";
    echo "Total de carpetas en activos: " . count($realFiles) . "<br>";
} else {
    echo "❌ activos NO existe<br>";
}

// 3. Probar la API
echo "<h3>3. Probando conexión a API:</h3>";
if (isset($_ENV['API_URL'])) {
    $url = $_ENV['API_URL'] . '/sites/actives';
    echo "URL de API: " . $url . "<br>";
    
    $result = @file_get_contents($url);
    if ($result === false) {
        echo "❌ Error conectando a la API<br>";
        $error = error_get_last();
        echo "Error: " . $error['message'] . "<br>";
    } else {
        echo "✅ API responde correctamente<br>";
        $json = json_decode($result, true);
        if ($json === null) {
            echo "❌ Error decodificando JSON de la API<br>";
            echo "Respuesta raw: " . htmlspecialchars(substr($result, 0, 500)) . "<br>";
        } else {
            echo "✅ JSON válido recibido<br>";
            echo "Número de sitios: " . count($json) . "<br>";
            if (count($json) > 0) {
                echo "Primer sitio: " . (isset($json[0]['folderName']) ? $json[0]['folderName'] : 'folderName no encontrado') . "<br>";
                echo "Estructura del primer sitio: " . implode(', ', array_keys($json[0])) . "<br>";
            }
        }
    }
} else {
    echo "❌ API_URL no está configurada en el .env<br>";
}

// 4. Probar copia manual de una carpeta
echo "<h3>4. Probando copia manual:</h3>";
$testFolder = './activos/test_copy';

// Limpiar si existe
if (is_dir($testFolder)) {
    function deleteDir($dirPath) {
        if (!is_dir($dirPath)) {
            return;
        }
        $files = scandir($dirPath);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $dirPath . '/' . $file;
                if (is_dir($filePath)) {
                    deleteDir($filePath);
                } else {
                    unlink($filePath);
                }
            }
        }
        rmdir($dirPath);
    }
    deleteDir($testFolder);
}

// Función de copia simple para probar
function testRecurseCopy($src, $dst) {
    if (!is_dir($src)) {
        return false;
    }
    
    if (!mkdir($dst, 0755, true)) {
        return false;
    }
    
    $files = scandir($src);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            
            if (is_dir($srcPath)) {
                if (!testRecurseCopy($srcPath, $dstPath)) {
                    return false;
                }
            } else {
                if (!copy($srcPath, $dstPath)) {
                    return false;
                }
            }
        }
    }
    return true;
}

if (testRecurseCopy('./basesite', $testFolder)) {
    echo "✅ Copia manual exitosa<br>";
    if (is_dir($testFolder)) {
        $files = scandir($testFolder);
        echo "Archivos copiados: " . implode(', ', array_filter($files, function($f) { return $f != '.' && $f != '..'; })) . "<br>";
    }
} else {
    echo "❌ Error en copia manual<br>";
}

// 5. Verificar variables de entorno
echo "<h3>5. Variables de entorno:</h3>";
echo "API_URL: " . (isset($_ENV['API_URL']) ? $_ENV['API_URL'] : 'NO CONFIGURADA') . "<br>";
echo "ENV: " . (isset($_ENV['ENV']) ? $_ENV['ENV'] : 'NO CONFIGURADA') . "<br>";
echo "LOCAL_URL: " . (isset($_ENV['LOCAL_URL']) ? $_ENV['LOCAL_URL'] : 'NO CONFIGURADA') . "<br>";

echo "<h3>6. Ejecutando functions->uploadsites() para probar:</h3>";

// Incluir la clase y probar
if (file_exists('./functions.php')) {
    include_once './functions.php';
    $functions = new functions();
    
    echo "Ejecutando uploadsites()...<br>";
    $result = $functions->uploadsites();
    echo "Resultado: <pre>" . print_r($result, true) . "</pre>";
    
    // Verificar qué se creó después
    echo "<h4>Contenido de activos después de ejecutar:</h4>";
    if (is_dir('./activos')) {
        $files = scandir('./activos');
        $realFiles = array_filter($files, function($f) { return $f != '.' && $f != '..'; });
        echo "Carpetas creadas: " . implode(', ', $realFiles) . "<br>";
        echo "Total: " . count($realFiles) . "<br>";
    }
} else {
    echo "❌ functions.php no encontrado<br>";
}
?>