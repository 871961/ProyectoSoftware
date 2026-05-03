<?php
// Script para actualizar contraseñas de médicos
require_once '../backend/src/config/database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Contraseña de prueba: test123
    // Hash bcrypt ya calculado
    $passwordHash = '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO';

    $sql = "UPDATE medicos
            SET contrasena_hash = :hash
            WHERE email LIKE '%clinica.com'";

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':hash', $passwordHash);
    $stmt->execute();

    $rowCount = $stmt->rowCount();

    echo "<pre>";
    echo "✓ Contraseñas de médicos actualizadas correctamente\n";
    echo "Total de médicos actualizados: " . $rowCount . "\n\n";

    // Mostrar lista de médicos disponibles
    $sqlList = "SELECT id_medico, nombre, apellidos, email, tipo_medico
                FROM medicos
                WHERE email LIKE '%clinica.com' AND activo = TRUE
                ORDER BY nombre";

    $stmtList = $db->prepare($sqlList);
    $stmtList->execute();
    $medicos = $stmtList->fetchAll(PDO::FETCH_ASSOC);

    echo "Médicos disponibles para acceder:\n";
    echo "================================\n";
    foreach ($medicos as $medico) {
        echo "\nEmail: " . $medico['email'] . "\n";
        echo "Nombre: " . $medico['nombre'] . " " . $medico['apellidos'] . "\n";
        echo "Tipo: " . ($medico['tipo_medico'] === 'general' ? 'Médico General' : 'Especialista') . "\n";
        echo "---";
    }

    echo "\n\nContraseña para todos: test123\n";
    echo "Rol: Médico\n";
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
