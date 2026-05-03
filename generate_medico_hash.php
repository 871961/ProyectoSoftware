<?php
// Generar un hash bcrypt válido para medico123
$password = 'medico123';
$valid_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

echo "Hash válido generado para 'medico123':\n";
echo $valid_hash . "\n\n";

// Verificación
$verify = password_verify($password, $valid_hash);
echo "Verificación: " . ($verify ? "✓ VÁLIDO" : "✗ INVÁLIDO") . "\n";
?>
