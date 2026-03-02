<?php
/**
 * update_admin_password.php
 * Actualiza la contraseña de un administrador existente
 */

require_once __DIR__ . '/../backend/src/config/database.php';

$email = $argv[1] ?? 'claudia.mateo@clinica.com';
$newPassword = $argv[2] ?? 'Admin123';

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar que existe
    $stmt = $db->prepare('SELECT id_admin, nombre, apellidos FROM administradores WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ No existe administrador con email: $email\n";
        exit(1);
    }
    
    echo "Actualizando contraseña para: {$admin['nombre']} {$admin['apellidos']} ($email)\n";
    
    // Generar nuevo hash
    $hash = password_hash($newPassword, PASSWORD_BCRYPT);
    
    // Actualizar
    $stmt = $db->prepare('UPDATE administradores SET contrasena_hash = :hash WHERE email = :email');
    $stmt->execute([':hash' => $hash, ':email' => $email]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Contraseña actualizada correctamente\n";
        echo "  Nueva contraseña: $newPassword\n\n";
        echo "Ahora puedes iniciar sesión con:\n";
        echo "  Email: $email\n";
        echo "  Password: $newPassword\n";
        exit(0);
    } else {
        echo "⚠️  No se modificó ninguna fila (¿hash idéntico?)\n";
        exit(1);
    }
    
} catch (Exception $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(2);
}
