<?php
// Verificar estado de médicos en la base de datos
require_once '../../backend/src/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Verificar un médico específico
    $email = 'elena.fernandez@clinica.com';

    $sql = "SELECT id_medico, nombre, apellidos, email, contrasena_hash, activo FROM medicos WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "<pre>";
    echo "Médico encontrado: " . ($medico ? "SÍ" : "NO") . "\n";

    if ($medico) {
        echo "\nID: " . $medico['id_medico'] . "\n";
        echo "Nombre: " . $medico['nombre'] . " " . $medico['apellidos'] . "\n";
        echo "Email: " . $medico['email'] . "\n";
        echo "Activo: " . ($medico['activo'] ? "SÍ" : "NO") . "\n";
        echo "Hash almacenado: " . substr($medico['contrasena_hash'], 0, 20) . "...\n";

        // Probar password_verify con test123
        $testPassword = 'test123';
        $isValid = password_verify($testPassword, $medico['contrasena_hash']);
        echo "\nVerificación de 'test123': " . ($isValid ? "✓ VÁLIDO" : "✗ INVÁLIDO") . "\n";

        // Mostrar los primeros caracteres para depuración
        echo "\nHash completo: " . $medico['contrasena_hash'] . "\n";
    }

    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
