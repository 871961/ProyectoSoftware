<?php
require_once __DIR__ . '/src/config/database.php';
require_once __DIR__ . '/src/dao/ChatMedicoDAO.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Send a fresh message from medico 13 to medico 11
    echo "=== INSERTING TEST MESSAGE ===\n";
    $sql = "INSERT INTO chat_mensajes 
            (id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo, eliminado_por_emisor, eliminado_por_receptor)
            VALUES 
            (:emisor, :receptor, :mensaje, :nonce, :tag, 'aes-256-gcm', FALSE, FALSE)
            RETURNING id_mensaje";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':emisor' => 13,  // Carlos sends
        ':receptor' => 11,  // to Yousra (ID 11)
        ':mensaje' => 'TEST_ENCRYPTED_MESSAGE_' . time(),
        ':nonce' => substr(md5(time()), 0, 12),
        ':tag' => substr(md5(time() . 'tag'), 0, 16)
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $msgId = $result['id_mensaje'];
    
    echo "Message inserted with ID: $msgId\n";
    echo "From: Medico 13 (Carlos)\n";
    echo "To: Medico 11 (Yousra)\n";
    echo "Status: UNREAD (leido_en is NULL)\n\n";
    
    // Verify it's unread
    echo "=== VERIFICATION ===\n";
    $sql = "SELECT leido_en FROM chat_mensajes WHERE id_mensaje = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $msgId]);
    $msg = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Message " . $msgId . " leido_en value: " . ($msg['leido_en'] ?? 'NULL (UNREAD)') . "\n\n";
    
    // Test the DAO method for medico 11
    echo "=== TESTING DAO METHOD ===\n";
    $dao = new ChatMedicoDAO();
    $unreadCount = $dao->contarNoLeidos(11);
    echo "Medico 11 unread count via DAO: $unreadCount\n\n";
    
    // Now simulate what the API would return
    echo "=== API ENDPOINT TEST ===\n";
    echo "When medico 11 logs in and calls /contar_no_leidos endpoint:\n";
    echo "Response should be: {\"success\": true, \"data\": {\"total_no_leidos\": " . $unreadCount . "}}\n";
    echo "\nThe badge should display: " . $unreadCount . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
