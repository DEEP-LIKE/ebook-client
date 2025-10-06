<?php
require_once 'functions.php';

echo "Testing copy functionality...\n";

// Crear una instancia de functions para acceder a métodos protegidos
class TestFunctions extends functions {
    public function testCloneBaseFolder($folderName) {
        return parent::cloneBaseFolder($folderName);
    }
    
    public function testRecurseCopy($src, $dst) {
        return parent::recurseCopy($src, $dst);
    }
}

$test = new TestFunctions();

// Verificar que basesite existe
if (!is_dir('./basesite')) {
    echo "❌ basesite directory not found\n";
    exit(1);
}

echo "✅ basesite directory exists\n";

// Verificar permisos de escritura en activos
if (!is_dir('./activos')) {
    if (!mkdir('./activos', 0755, true)) {
        echo "❌ Cannot create activos directory\n";
        exit(1);
    }
}

echo "✅ activos directory exists/created\n";

// Probar copia directa
$testFolder = 'test_' . time();
echo "Testing copy to: " . $testFolder . "\n";

$result = $test->testCloneBaseFolder($testFolder);

if ($result) {
    echo "✅ Copy successful!\n";
    
    // Verificar que se creó
    if (is_dir('./activos/' . $testFolder)) {
        echo "✅ Folder created: ./activos/" . $testFolder . "\n";
        
        // Listar contenido
        $files = scandir('./activos/' . $testFolder);
        echo "Contents: " . implode(', ', array_filter($files, function($f) { return $f !== '.' && $f !== '..'; })) . "\n";
        
        // Limpiar
        function deleteTestDir($dir) {
            if (!is_dir($dir)) return;
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    deleteTestDir($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
        
        deleteTestDir('./activos/' . $testFolder);
        echo "✅ Test folder cleaned up\n";
    } else {
        echo "❌ Folder not found after copy\n";
    }
} else {
    echo "❌ Copy failed!\n";
}
?>
