<?php
/**
 * create_admin.php
 * Uso: php scripts/create_admin.php email nombre apellidos password
 * Si no se pasan args, usará valores por defecto para claudia.mateo@clinica.com
 */

require_once __DIR__ . '/../backend/src/dao/AdministradorDAO.php';
require_once __DIR__ . '/../backend/src/vo/AdministradorVO.php';

$email = $argv[1] ?? 'claudia.mateo@clinica.com';
$nombre = $argv[2] ?? 'Claudia';
$apellidos = $argv[3] ?? 'Mateo';
$password = $argv[4] ?? 'Admin123';

try {
    $dao = new AdministradorDAO();

    // Si ya existe (activo), informar
    $existe = $dao->buscarPorEmail($email);
    if ($existe) {
        echo "Administrador con email $email ya existe (activo).\n";
        exit(0);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $admin = new AdministradorVO([
        'nombre' => $nombre,
        'apellidos' => $apellidos,
        'email' => $email,
        'contrasena_hash' => $hash,
        'activo' => true
    ]);

    $ok = $dao->insertar($admin);

    if ($ok) {
        echo "Administrador creado: $email (contraseña: $password)\n";
        exit(0);
    } else {
        echo "No se pudo crear el administrador.\n";
        exit(1);
    }

} catch (Exception $e) {
    fwrite(STDERR, "ERROR: " . $e->getMessage() . "\n");
    exit(2);
}
