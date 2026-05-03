<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== CONSULTAS DE DEPENDIENTES DE MARÍA ===\n\n";
    
    $stmt = $pdo->prepare("SELECT id_consulta, id_paciente, diagnostico, fecha FROM consultas 
                           WHERE id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003')
                           ORDER BY fecha DESC");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($consultas as $c) {
        echo "Paciente: " . $c['id_paciente'] . " | Fecha: " . $c['fecha'] . "\n";
        echo "Diagnóstico: " . $c['diagnostico'] . "\n";
        
        // Verificar si tiene caracteres problemáticos
        $has_mojibake = preg_match('/[Ã¡Ã©Ã¯Ã³Ã»Ã±Ã¼]/', $c['diagnostico']);
        echo "Mojibake detectado: " . ($has_mojibake ? "SÍ" : "NO") . "\n";
        
        // Ver bytes
        $bytes = bin2hex(substr($c['diagnostico'], 0, 30));
        echo "Primeros 30 bytes: " . $bytes . "\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
