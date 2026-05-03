<?php
// Generar un hash bcrypt válido para test123
$password = 'test123';
$valid_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "Hash válido generado para 'test123':\n";
echo $valid_hash . "\n\n";

// SQL para actualizar todos los médicos
echo "SQL para ejecutar:\n";
echo "=================\n";
echo "UPDATE medicos SET contrasena_hash = '" . $valid_hash . "' WHERE email LIKE '%clinica.com';\n\n";

// Verificación
$verify = password_verify($password, $valid_hash);
echo "Verificación: " . ($verify ? "✓ VÁLIDO" : "✗ INVÁLIDO") . "\n";
?>
