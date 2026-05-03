<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "╔════════════════════════════════════════════════════════════╗\n";
    echo "║    DATOS COMPLETOS - MARÍA Y DEPENDIENTES RESTAURADOS    ║\n";
    echo "╚════════════════════════════════════════════════════════════╝\n\n";
    
    // Buscar a María
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE dni = ?");
    $stmt->execute(['12345678A']);
    $maria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "👤 PACIENTE PRINCIPAL:\n";
    echo "   Nombre: " . $maria['nombre'] . " " . $maria['apellidos'] . "\n";
    echo "   DNI: " . $maria['dni'] . "\n";
    echo "   Email: " . $maria['email'] . "\n\n";
    
    // Buscar dependientes
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE dni_tutor = ? ORDER BY fecha_nacimiento DESC");
    $stmt->execute(['12345678A']);
    $dependientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "👨‍👩‍👧‍👦 DEPENDIENTES: " . count($dependientes) . "\n";
    echo "════════════════════════════════════════════\n\n";
    
    foreach ($dependientes as $i => $dep) {
        $fecha = new DateTime($dep['fecha_nacimiento']);
        $edad = (new DateTime())->diff($fecha)->y;
        
        echo ($i+1) . ". " . strtoupper($dep['nombre']) . " " . $dep['apellidos'] . "\n";
        echo "   DNI: " . $dep['dni'] . " | Edad: " . $edad . " años\n";
        echo "   Grupo Sanguíneo: " . ($dep['grupo_sanguineo'] ?? 'No especificado') . "\n";
        echo "   Alergias: " . ($dep['alergias'] ?? 'Ninguna') . "\n\n";
        
        // Consultas de este dependiente
        $stmt_cons = $pdo->prepare("SELECT * FROM consultas WHERE id_paciente = ? ORDER BY fecha DESC");
        $stmt_cons->execute([$dep['dni']]);
        $consultas = $stmt_cons->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($consultas) > 0) {
            echo "   📋 Consultas: " . count($consultas) . "\n";
            foreach ($consultas as $c) {
                $fecha_cons = new DateTime($c['fecha']);
                echo "      • " . $fecha_cons->format('d/m/Y') . ": " . $c['diagnostico'] . "\n";
            }
        } else {
            echo "   📋 Consultas: Ninguna registrada\n";
        }
        
        // Perfil de salud
        $stmt_prof = $pdo->prepare("SELECT * FROM perfiles_salud WHERE id_paciente = ?");
        $stmt_prof->execute([$dep['dni']]);
        $perfil = $stmt_prof->fetch(PDO::FETCH_ASSOC);
        
        if ($perfil) {
            echo "   📊 Perfil de Salud:\n";
            echo "      • Peso: " . $perfil['peso_kg'] . " kg\n";
            echo "      • Altura: " . $perfil['altura_cm'] . " cm\n";
            if ($perfil['enfermedades']) {
                echo "      • Enfermedades: " . $perfil['enfermedades'] . "\n";
            }
        }
        
        echo "\n";
    }
    
    echo "════════════════════════════════════════════\n";
    echo "✅ Restauración completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
