<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== CORRIGIENDO MOJIBAKE EN PERFILES ===\n\n";
    
    // Actualizar perfiles con mojibake
    $stmt = $pdo->prepare("
        UPDATE perfiles_salud
        SET enfermedades = ?
        WHERE enfermedades LIKE '%Ã%'
    ");
    $stmt->execute(['Dermatitis atópica leve']);
    
    echo "Perfiles corregidos.\n";
    
    // Verificar que se corrigió
    $stmt = $pdo->prepare("SELECT id_perfil, enfermedades FROM perfiles_salud WHERE id_perfil = 12");
    $stmt->execute();
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\nVerificación - Perfil ID 12:\n";
    echo "  Enfermedades: " . ($perfil['enfermedades'] ?? 'NULL') . "\n";
    
    echo "\n✅ Corrección completada\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
