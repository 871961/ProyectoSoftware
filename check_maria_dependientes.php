<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== PACIENTES Y DEPENDIENTES ===\n\n";
    
    // Buscar pacientes principales (no dependientes)
    $stmt = $pdo->prepare("SELECT dni, nombre, apellidos FROM pacientes WHERE dni NOT LIKE 'DEP-%' LIMIT 10");
    $stmt->execute();
    $pacientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Pacientes Principales:\n";
    foreach ($pacientes as $p) {
        echo "  - " . $p['nombre'] . " " . $p['apellidos'] . " (DNI: " . $p['dni'] . ")\n";
    }
    
    echo "\n=== BUSCANDO MARÍA ===\n";
    $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE nombre ILIKE '%Maria%' AND dni NOT LIKE 'DEP-%'");
    $stmt->execute();
    $maria = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($maria) {
        echo "Encontrada: " . $maria['nombre'] . " " . $maria['apellidos'] . "\n";
        echo "  DNI: " . $maria['dni'] . "\n";
        echo "  Email: " . $maria['email'] . "\n\n";
        
        // Buscar dependientes de María
        echo "=== DEPENDIENTES DE MARÍA ===\n";
        $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE dni_tutor = ? OR (dni LIKE 'DEP-%' AND dni LIKE CONCAT('%', ?, '%'))");
        $stmt->execute([$maria['dni'], substr($maria['dni'], 0, 3)]);
        $dependientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($dependientes) > 0) {
            echo "Encontrados " . count($dependientes) . " dependiente(s):\n";
            foreach ($dependientes as $d) {
                echo "  - " . $d['nombre'] . " " . $d['apellidos'] . " (DNI: " . $d['dni'] . ")\n";
            }
        } else {
            echo "⚠️ NO HAY DEPENDIENTES REGISTRADOS\n";
            
            // Ver qué dependientes existen en general
            echo "\n=== TODOS LOS DEPENDIENTES EN LA BD ===\n";
            $stmt = $pdo->prepare("SELECT dni, nombre, apellidos, dni_tutor FROM pacientes WHERE dni LIKE 'DEP-%' LIMIT 5");
            $stmt->execute();
            $todos_dep = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($todos_dep) > 0) {
                foreach ($todos_dep as $d) {
                    echo "  - " . $d['nombre'] . " " . $d['apellidos'] . " (Tutor DNI: " . ($d['dni_tutor'] ?? 'NULL') . ")\n";
                }
            } else {
                echo "  (No hay dependientes en la base de datos)\n";
            }
        }
    } else {
        echo "❌ No se encontró a María en la base de datos\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
