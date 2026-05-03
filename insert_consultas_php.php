<?php
require_once 'backend/src/config/database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "=== REINSERTAR CONSULTAS CON UTF-8 VIA PHP ===\n\n";
    
    // Eliminar consultas previas
    $pdo->prepare("DELETE FROM consultas WHERE id_paciente IN (?, ?, ?)")
        ->execute(['DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003']);
    echo "Consultas previas eliminadas.\n";
    
    // Obtener ID del pediatra
    $stmt = $pdo->prepare("SELECT id_medico FROM medicos WHERE email = ? LIMIT 1");
    $stmt->execute(['laura.martinez.pediatra@clinica.com']);
    $medico = $stmt->fetch(PDO::FETCH_ASSOC);
    $pediatra_id = $medico['id_medico'];
    
    echo "Pediatra encontrado: ID " . $pediatra_id . "\n\n";
    
    // Datos de consultas correctamente codificados en UTF-8
    $consultas = [
        // Santiago (DEP-MARIA-001)
        [
            'id_paciente' => 'DEP-MARIA-001',
            'diagnostico' => 'Revisión pediátrica rutinaria',
            'tratamiento' => 'Sin tratamiento necesario',
            'observaciones' => 'Niño sano, desarrollo normal',
            'dias' => 45
        ],
        [
            'id_paciente' => 'DEP-MARIA-001',
            'diagnostico' => 'Otitis media aguda',
            'tratamiento' => 'Amoxicilina 250mg cada 8 horas por 7 días',
            'observaciones' => 'Oído derecho afectado',
            'dias' => 120
        ],
        [
            'id_paciente' => 'DEP-MARIA-001',
            'diagnostico' => 'Control de crecimiento y desarrollo',
            'tratamiento' => 'Suplemento vitamina D 400 UI diarias',
            'observaciones' => 'Desarrollo normal para su edad',
            'dias' => 200
        ],
        // Lucia (DEP-MARIA-002)
        [
            'id_paciente' => 'DEP-MARIA-002',
            'diagnostico' => 'Dermatitis atópica leve',
            'tratamiento' => 'Crema emoliente diaria. Corticosteroide tópico si es necesario',
            'observaciones' => 'Mejoría esperada en 2-3 semanas. Evitar irritantes',
            'dias' => 30
        ],
        [
            'id_paciente' => 'DEP-MARIA-002',
            'diagnostico' => 'Revisión anual + vacunas',
            'tratamiento' => 'Vacuna Varicela 2ª dosis',
            'observaciones' => 'Bien tolerada. Próxima cita en 12 meses',
            'dias' => 90
        ],
        [
            'id_paciente' => 'DEP-MARIA-002',
            'diagnostico' => 'Reacción alérgica leve a alimento',
            'tratamiento' => 'Antihistamínico H1 si es necesario. Evitar alimento causante',
            'observaciones' => 'Crisis resuelta',
            'dias' => 180
        ],
        // Miguel (DEP-MARIA-003)
        [
            'id_paciente' => 'DEP-MARIA-003',
            'diagnostico' => 'Revisión pediátrica rutinaria 5 años',
            'tratamiento' => 'Sin tratamiento. Reforzar importancia de higiene bucal',
            'observaciones' => 'Niño sano. Remitir a odontología pediátrica',
            'dias' => 20
        ],
        [
            'id_paciente' => 'DEP-MARIA-003',
            'diagnostico' => 'Gastroenteritis aguda',
            'tratamiento' => 'Rehidratación oral. Dieta blanda. Probióticos',
            'observaciones' => 'Mejoría progresiva en 48 horas',
            'dias' => 100
        ],
        [
            'id_paciente' => 'DEP-MARIA-003',
            'diagnostico' => 'Intolerancia a la lactosa confirmada',
            'tratamiento' => 'Dieta sin lactosa. Lactasa en suplemento si lo necesita',
            'observaciones' => 'Derivación a nutrición pediátrica',
            'dias' => 240
        ]
    ];
    
    // Preparar statement
    $stmt = $pdo->prepare("
        INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones)
        VALUES (?, ?, (NOW() - (? || ' days')::INTERVAL), ?, ?, ?)
    ");
    
    $contador = 0;
    foreach ($consultas as $c) {
        $stmt->execute([
            $c['id_paciente'],
            $pediatra_id,
            $c['dias'],
            $c['diagnostico'],
            $c['tratamiento'],
            $c['observaciones']
        ]);
        $contador++;
    }
    
    echo "Reinsertadas " . $contador . " consultas correctamente.\n\n";
    
    // Verificar que los datos están correctamente guardados
    echo "=== VERIFICACIÓN ===\n\n";
    $stmt = $pdo->prepare("SELECT id_paciente, diagnostico FROM consultas 
                           WHERE id_paciente IN (?, ?, ?)
                           ORDER BY id_paciente, fecha DESC");
    $stmt->execute(['DEP-MARIA-001', 'DEP-MARIA-002', 'DEP-MARIA-003']);
    
    $paciente_actual = '';
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) {
        if ($c['id_paciente'] != $paciente_actual) {
            $paciente_actual = $c['id_paciente'];
            echo "\n$paciente_actual:\n";
        }
        echo "  ✓ " . $c['diagnostico'] . "\n";
    }
    
    echo "\n✅ Inserción completada exitosamente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
?>
