<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== VERIFICACIÓN POST-CORRECCIÓN ===\n\n";
    
    // Buscar todas las consultas de dependientes
    $stmt = $pdo->prepare("SELECT id_paciente, diagnostico FROM consultas 
                           WHERE id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003')
                           ORDER BY id_paciente, fecha DESC");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Paciente DEP-MARIA-001 (Santiago):\n";
    foreach ($consultas as $c) {
        if ($c['id_paciente'] == 'DEP-MARIA-001') {
            echo "  • " . $c['diagnostico'] . "\n";
        }
    }
    
    echo "\nPaciente DEP-MARIA-002 (Lucia):\n";
    foreach ($consultas as $c) {
        if ($c['id_paciente'] == 'DEP-MARIA-002') {
            echo "  • " . $c['diagnostico'] . "\n";
        }
    }
    
    echo "\nPaciente DEP-MARIA-003 (Miguel):\n";
    foreach ($consultas as $c) {
        if ($c['id_paciente'] == 'DEP-MARIA-003') {
            echo "  • " . $c['diagnostico'] . "\n";
        }
    }
    
    // Buscar cualquier mojibake restante
    echo "\n=== BÚSQUEDA DE MOJIBAKE RESTANTE ===\n";
    $stmt = $pdo->prepare("SELECT id_consulta, diagnostico FROM consultas 
                           WHERE diagnostico LIKE '%Ã%' 
                           LIMIT 10");
    $stmt->execute();
    $mojibake = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($mojibake) > 0) {
        echo "Encontrados " . count($mojibake) . " registros con mojibake:\n";
        foreach ($mojibake as $m) {
            echo "  ID " . $m['id_consulta'] . ": " . $m['diagnostico'] . "\n";
        }
    } else {
        echo "No hay mojibake detectado en consultas.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
