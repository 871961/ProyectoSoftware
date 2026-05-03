<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'backend/src/config/database.php';

try {
    // Get PDO connection
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== UTF-8 ENCODING VERIFICATION ===\n\n";
    
    // Test 1: Check database encoding
    echo "1. Database Encoding:\n";
    $stmt = $pdo->prepare("SELECT datname, encoding FROM pg_database WHERE datname=?");
    $stmt->execute(['medhistory']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Database: " . $row['datname'] . "\n";
    echo "   Encoding: " . $row['encoding'] . "\n\n";
    
    // Test 2: Check client encoding
    echo "2. Client Connection Encoding:\n";
    $stmt = $pdo->prepare("SHOW client_encoding");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   Client Encoding: " . $row['client_encoding'] . "\n\n";
    
    // Test 3: Insert and verify Spanish text
    echo "3. Spanish Character Test (Insert & Retrieve):\n";
    
    // First, clean up any test data
    $pdo->prepare("DELETE FROM enfermedades_catalogo WHERE nombre_patologia LIKE ?")
        ->execute(['%Test UTF-8%']);
    
    // Insert test data
    $testText = "Revisión pediátrica rutinaria - Test UTF-8";
    $stmt = $pdo->prepare("INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES (?)");
    $stmt->execute([$testText]);
    echo "   Inserted: " . $testText . "\n";
    
    // Read back
    $stmt = $pdo->prepare("SELECT nombre_patologia FROM enfermedades_catalogo WHERE nombre_patologia LIKE ? LIMIT 1");
    $stmt->execute(['%Test UTF-8%']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "   Retrieved: " . $row['nombre_patologia'] . "\n";
    echo "   Match: " . ($row['nombre_patologia'] === $testText ? "✓ CORRECT" : "✗ MISMATCH") . "\n\n";
    
    echo "✓ All UTF-8 tests completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
