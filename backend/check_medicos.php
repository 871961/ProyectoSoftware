<?php
require_once __DIR__ . '/src/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== MEDICOS IN SYSTEM ===\n\n";
    $sql = "SELECT id_medico, nombre, apellidos, tipo_medico, activo FROM medicos ORDER BY id_medico";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $medicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($medicos as $med) {
        echo "ID: {$med['id_medico']} | {$med['nombre']} {$med['apellidos']} | Type: {$med['tipo_medico']} | Active: " . ($med['activo'] ? 'YES' : 'NO') . "\n";
    }
    
    echo "\n=== UNREAD COUNT PER MEDICO ===\n\n";
    for ($id = 1; $id <= 15; $id++) {
        $sql = "SELECT COUNT(*) as unread FROM chat_mensajes 
                WHERE id_receptor = :id AND leido_en IS NULL AND eliminado_por_receptor = FALSE";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            echo "Medico $id: **$count UNREAD MESSAGES**\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
