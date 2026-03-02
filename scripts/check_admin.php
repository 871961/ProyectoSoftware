<?php
/**
 * check_admin.php
 * Diagnóstico: verifica admin en BD y contraseña
 */

require_once __DIR__ . '/../backend/src/config/database.php';

$email = $argv[1] ?? 'claudia.mateo@clinica.com';
$password = $argv[2] ?? 'Admin123';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== DIAGNÓSTICO ADMINISTRADOR ===\n\n";
    echo "Buscando: $email\n\n";
    
    // Buscar admin (incluyendo inactivos)
    $stmt = $db->prepare('SELECT id_admin, nombre, apellidos, email, contrasena_hash, activo FROM administradores WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "❌ NO EXISTE administrador con ese email en la base de datos.\n";
        echo "Ejecuta: php scripts/create_admin.php\n";
        exit(1);
    }
    
    echo "✓ Administrador encontrado:\n";
    echo "  ID: " . $admin['id_admin'] . "\n";
    echo "  Nombre: " . $admin['nombre'] . " " . $admin['apellidos'] . "\n";
    echo "  Email: " . $admin['email'] . "\n";
    echo "  Activo: " . ($admin['activo'] ? 'SÍ' : 'NO') . "\n";
    echo "  Hash almacenado: " . substr($admin['contrasena_hash'], 0, 30) . "...\n\n";
    
    if (!$admin['activo']) {
        echo "⚠️  PROBLEMA: El administrador está INACTIVO (activo=false)\n";
        echo "   No puede iniciar sesión. Reactívalo desde la BD.\n\n";
    }
    
    // Verificar contraseña
    echo "Verificando contraseña: '$password'\n";
    $ok = password_verify($password, $admin['contrasena_hash']);
    
    if ($ok) {
        echo "✓ CONTRASEÑA CORRECTA\n\n";
        
        if ($admin['activo']) {
            echo "=== RESULTADO ===\n";
            echo "✓ Todo correcto. Deberías poder iniciar sesión con:\n";
            echo "  Email: $email\n";
            echo "  Password: $password\n\n";
            echo "Si aún falla, revisa:\n";
            echo "1. URL del backend en admin.js\n";
            echo "2. Consola del navegador (F12) para ver errores\n";
        } else {
            echo "=== PROBLEMA ===\n";
            echo "Administrador inactivo. No puede iniciar sesión.\n";
        }
    } else {
        echo "❌ CONTRASEÑA INCORRECTA\n\n";
        echo "=== SOLUCIÓN ===\n";
        echo "Restablece la contraseña ejecutando:\n";
        echo "php scripts/create_admin.php $email Claudia Mateo Admin123\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(2);
}
