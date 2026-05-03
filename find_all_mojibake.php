<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== BÚSQUEDA DE TODOS LOS MOJIBAKE ===\n\n";
    
    // Buscar en consultas
    $stmt = $pdo->prepare("SELECT id_consulta, diagnostico FROM consultas WHERE diagnostico LIKE '%Ã%' LIMIT 20");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        echo "Encontrados " . count($results) . " diagnósticos con mojibake:\n\n";
        foreach ($results as $r) {
            echo "ID " . $r['id_consulta'] . ": " . $r['diagnostico'] . "\n";
        }
        echo "\n";
    } else {
        echo "✓ No hay mojibake en consultas.\n\n";
    }
    
    // Buscar en perfiles de salud
    $stmt = $pdo->prepare("SELECT id_perfil, alergias, enfermedades FROM perfiles_salud WHERE alergias LIKE '%Ã%' OR enfermedades LIKE '%Ã%' LIMIT 20");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($results) > 0) {
        echo "Encontrados " . count($results) . " perfiles con mojibake:\n\n";
        foreach ($results as $r) {
            echo "ID " . $r['id_perfil'] . ": " . ($r['alergias'] ?? '') . " / " . ($r['enfermedades'] ?? '') . "\n";
        }
    } else {
        echo "✓ No hay mojibake en perfiles de salud.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
