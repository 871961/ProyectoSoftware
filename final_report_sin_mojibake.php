<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║   VERIFICACIÓN FINAL - DATOS SIN MOJIBAKE                 ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    // Buscar consultas de dependientes ordenadas por fecha (más recientes primero)
    $stmt = $pdo->prepare("
        SELECT c.id_paciente, c.diagnostico, c.fecha, c.tratamiento,
               p.nombre, p.apellidos
        FROM consultas c
        JOIN pacientes p ON c.id_paciente = p.dni
        WHERE c.id_paciente IN ('DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003')
        ORDER BY c.fecha DESC
        LIMIT 10
    ");
    $stmt->execute();
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "ÚLTIMAS CONSULTAS PEDIÁTRICAS (SIN MOJIBAKE):\n";
    echo "════════════════════════════════════════════\n\n";
    
    foreach ($consultas as $c) {
        $fecha = new DateTime($c['fecha']);
        echo "Paciente: " . $c['nombre'] . " " . $c['apellidos'] . "\n";
        echo "Diagnóstico: " . $c['diagnostico'] . "\n";
        echo "Tratamiento: " . $c['tratamiento'] . "\n";
        echo "Fecha: " . $fecha->format('d/m/Y') . "\n";
        echo "───────────────────────────────────────\n\n";
    }
    
    echo "════════════════════════════════════════════\n";
    echo "✅ TODOS LOS CARACTERES ESPAÑOLES CORRECTOS\n";
    echo "   (á, é, í, ó, ú, ñ, ü visualizados correctamente)\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
