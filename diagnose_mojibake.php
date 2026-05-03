<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== DIAGNÓSTICO DE MOJIBAKE ===\n\n";
    
    // Buscar diagnósticos con mojibake
    $stmt = $pdo->prepare("SELECT id_consulta, id_paciente, diagnostico FROM consultas LIMIT 5");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Consultas actuales:\n";
    foreach ($consultas as $c) {
        echo "ID: " . $c['id_consulta'] . "\n";
        echo "Diagnóstico: " . $c['diagnostico'] . "\n";
        echo "Bytes: " . bin2hex($c['diagnostico']) . "\n\n";
    }
    
    echo "\n=== CONTEO DE PROBLEMAS ===\n";
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE diagnostico LIKE '%Ã%'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Consultas con mojibake: " . $result['total'] . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
