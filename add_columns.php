<?php
// Script simple para agregar columnas area y group
try {
    $db = new PDO('sqlite:database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Verificar si las columnas ya existen
    $result = $db->query("PRAGMA table_info(people)");
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    
    $hasArea = false;
    $hasGroup = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'area') $hasArea = true;
        if ($column['name'] === 'group') $hasGroup = true;
    }
    
    if (!$hasArea) {
        $db->exec("ALTER TABLE people ADD COLUMN area VARCHAR(100)");
        echo "✓ Columna 'area' agregada exitosamente\n";
    } else {
        echo "• Columna 'area' ya existe\n";
    }
    
    if (!$hasGroup) {
        $db->exec("ALTER TABLE people ADD COLUMN `group` VARCHAR(100)");
        echo "✓ Columna 'group' agregada exitosamente\n";
    } else {
        echo "• Columna 'group' ya existe\n";
    }
    
    echo "✓ Script ejecutado correctamente\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>