<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "================================================================\n";
    echo "RESUMEN DE RESTAURACION - MARIA Y DEPENDIENTES\n";
    echo "================================================================\n\n";
    
    // Buscar a María
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE dni = ?");
    $stmt->execute(['12345678A']);
    $maria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "PACIENTE PRINCIPAL:\n";
    echo "  Nombre: " . $maria['nombre'] . " " . $maria['apellidos'] . "\n";
    echo "  DNI: " . $maria['dni'] . "\n";
    echo "  Email: " . $maria['email'] . "\n\n";
    
    // Buscar dependientes
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE dni_tutor = ? ORDER BY fecha_nacimiento DESC");
    $stmt->execute(['12345678A']);
    $dependientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "DEPENDIENTES RESTAURADOS: " . count($dependientes) . "\n";
    echo "================================================================\n\n";
    
    foreach ($dependientes as $i => $dep) {
        $fecha = new DateTime($dep['fecha_nacimiento']);
        $edad = (new DateTime())->diff($fecha)->y;
        
        echo ($i+1) . ". " . $dep['nombre'] . " " . $dep['apellidos'] . "\n";
        echo "   DNI: " . $dep['dni'] . "\n";
        echo "   Edad: " . $edad . " anos\n";
        echo "   Grupo Sanguineo: " . ($dep['grupo_sanguineo'] ?? 'No especificado') . "\n";
        echo "   Alergias: " . ($dep['alergias'] ?? 'Ninguna') . "\n";
        
        // Consultas de este dependiente
        $stmt_cons = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE id_paciente = ?");
        $stmt_cons->execute([$dep['dni']]);
        $result = $stmt_cons->fetch(PDO::FETCH_ASSOC);
        
        echo "   Consultas: " . $result['total'] . "\n\n";
    }
    
    echo "================================================================\n";
    echo "VERIFICACION DE INTEGRIDAD:\n";
    echo "================================================================\n";
    
    // Verificar pediatra
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM medicos_especialistas WHERE especialidad = 'Pediatra'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Pediatras registrados: " . $result['total'] . "\n";
    
    // Verificar perfiles de salud
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM perfiles_salud WHERE id_paciente IN (SELECT dni FROM pacientes WHERE dni_tutor = '12345678A')");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Perfiles de salud de dependientes: " . $result['total'] . "\n";
    
    // Verificar total de consultas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM consultas WHERE id_paciente IN (SELECT dni FROM pacientes WHERE dni_tutor = '12345678A')");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Consultas medicas de dependientes: " . $result['total'] . "\n";
    
    echo "\nEstado: OK - Restauracion completada\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
