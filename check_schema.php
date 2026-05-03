<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== ESTRUCTURA DE TABLA PACIENTES ===\n\n";
    
    // Obtener información de las columnas
    $stmt = $pdo->prepare("
        SELECT column_name, data_type, is_nullable 
        FROM information_schema.columns 
        WHERE table_name = 'pacientes'
        ORDER BY ordinal_position
    ");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Columnas en tabla pacientes:\n";
    foreach ($columns as $col) {
        $nullable = $col['is_nullable'] === 'YES' ? '✓' : '✗';
        echo "  - " . $col['column_name'] . " (" . $col['data_type'] . ") [NULL: " . $nullable . "]\n";
    }
    
    echo "\n=== BÚSQUEDA DE DEPENDIENTES ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM pacientes WHERE dni LIKE 'DEP-%'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Registros con DNI tipo DEP-*: " . $result['total'] . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
