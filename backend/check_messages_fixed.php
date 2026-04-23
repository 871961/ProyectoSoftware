<?php
session_start();
require_once __DIR__ . "/src/config/database.php";

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== CHECKING CHAT_MENSAJES TABLE ===\n\n";
    
    // Show all messages with their status
    $sql = "SELECT m.id_mensaje, m.id_emisor, m.id_receptor, 
                   m.leido_en, m.enviado_en,
                   med_e.nombre as emisor_nombre,
                   med_r.nombre as receptor_nombre
            FROM chat_mensajes m
            LEFT JOIN medicos med_e ON med_e.id_medico = m.id_emisor
            LEFT JOIN medicos med_r ON med_r.id_medico = m.id_receptor
            ORDER BY m.enviado_en DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "TOTAL MESSAGES IN CHAT: " . count($messages) . "\n\n";
    
    foreach ($messages as $msg) {
        $status = $msg["leido_en"] ? "READ at " . $msg["leido_en"] : "UNREAD";
        echo "ID: {$msg["id_mensaje"]} | From: {$msg["emisor_nombre"]} ({$msg["id_emisor"]}) | To: {$msg["receptor_nombre"]} ({$msg["id_receptor"]}) | Status: {$status}\n";
    }
    
    echo "\n=== UNREAD COUNT BY RECIPIENT ===\n";
    $sql = "SELECT id_receptor, 
                   medicos.nombre,
                   COUNT(*) as total,
                   SUM(CASE WHEN leido_en IS NULL THEN 1 ELSE 0 END) as unread
            FROM chat_mensajes
            LEFT JOIN medicos ON medicos.id_medico = chat_mensajes.id_receptor
            GROUP BY id_receptor, medicos.nombre
            ORDER BY id_receptor";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($counts as $row) {
        $name = $row["nombre"] ?? "Unknown";
        echo "{$name} (ID: {$row["id_receptor"]}): {$row["unread"]} unread out of {$row["total"]} total\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
