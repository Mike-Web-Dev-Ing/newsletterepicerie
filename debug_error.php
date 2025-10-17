<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>Test PHP</h1>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Date: " . date('Y-m-d H:i:s') . "<br>";

// Test basique
try {
    echo "✓ PHP fonctionne<br>";
    
    // Test PDO disponible
    if (class_exists('PDO')) {
        echo "✓ PDO disponible<br>";
    } else {
        echo "❌ PDO non disponible<br>";
    }
    
    // Test MySQL driver
    if (in_array('mysql', PDO::getAvailableDrivers())) {
        echo "✓ Driver MySQL disponible<br>";
    } else {
        echo "❌ Driver MySQL manquant<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
}
?>