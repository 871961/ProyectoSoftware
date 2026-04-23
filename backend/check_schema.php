<?php
require_once "c:/web/medHistory/backend/src/config/database.php";
try {
    $db = Database::getInstance()->getConnection();
    $q = $db->query("SELECT column_name FROM information_schema.columns WHERE table_name = ''chat_mensajes''");
    while ($row = $q->fetch()) {
        echo $row["column_name"] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
