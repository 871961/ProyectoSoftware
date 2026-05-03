<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== VERIFICACIÓN DE DEPENDIENTES DE MARÍA ===\n\n";
    
    // Buscar a María
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE nombre ILIKE '%Maria%' AND es_dependiente = FALSE LIMIT 1");
    $stmt->execute();
    $maria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($maria) {
        echo "✓ María encontrada:\n";
        echo "  Nombre: " . $maria['nombre'] . " " . $maria['apellidos'] . "\n";
        echo "  DNI: " . $maria['dni'] . "\n\n";
        
        // Buscar dependientes de María
        echo "=== DEPENDIENTES DE MARÍA ===\n";
        $stmt = $pdo->prepare("SELECT nombre, apellidos, dni, fecha_nacimiento, grupo_sanguineo, alergias FROM pacientes WHERE dni_tutor = ? ORDER BY fecha_nacimiento DESC");
        $stmt->execute([$maria['dni']]);
        $dependientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($dependientes) > 0) {
            echo "Total: " . count($dependientes) . " dependiente(s)\n\n";
            foreach ($dependientes as $i => $d) {
                $fecha = new DateTime($d['fecha_nacimiento']);
                $edad = (new DateTime())->diff($fecha)->y;
                echo ($i+1) . ". " . $d['nombre'] . " " . $d['apellidos'] . "\n";
                echo "   DNI: " . $d['dni'] . "\n";
                echo "   Edad: " . $edad . " años\n";
                echo "   Grupo sanguíneo: " . ($d['grupo_sanguineo'] ?? 'No especificado') . "\n";
                echo "   Alergias: " . ($d['alergias'] ?? 'Ninguna') . "\n\n";
            }
        } else {
            echo "⚠️ María no tiene dependientes registrados\n";
        }
    } else {
        echo "❌ María no encontrada en la base de datos\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
