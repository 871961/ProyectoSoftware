<?php
// Script temporal para generar hash de contraseña
$password = 'Admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);
echo "Hash para 'Admin123':\n";
echo $hash . "\n";
?>
