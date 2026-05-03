-- =============================================================================
-- REPORTE: INFORMACIÓN DE DEPENDIENTES/MENORES
-- Muestra los 30 menores con su información de tutor y pediatra asignado
-- =============================================================================

\echo '════════════════════════════════════════════════════════════════════════════'
\echo 'DEPENDIENTES/MENORES REGISTRADOS (30 total)'
\echo '════════════════════════════════════════════════════════════════════════════'
\echo ''

SELECT
    p.dni AS "DNI Menor",
    (p.nombre || ' ' || p.apellidos) AS "Nombre Completo",
    p.email AS "Email",
    p.fecha_nacimiento AS "Fecha Nacimiento",
    p.dni_tutor AS "DNI Tutor",
    (pat.nombre || ' ' || pat.apellidos) AS "Nombre Tutor",
    m.nombre || ' ' || m.apellidos AS "Pediatra Asignado",
    m.email AS "Email Pediatra"
FROM pacientes p
LEFT JOIN pacientes pat ON p.dni_tutor = pat.dni
LEFT JOIN medicos m ON p.id_pediatra = m.id_medico
WHERE p.es_dependiente = TRUE
ORDER BY p.dni;

\echo ''
\echo '════════════════════════════════════════════════════════════════════════════'
\echo ''

-- Resumen por pediatra
\echo 'DISTRIBUCIÓN POR PEDIATRA'
\echo '════════════════════════════════════════════════════════════════════════════'
\echo ''

SELECT
    m.id_medico,
    m.nombre || ' ' || m.apellidos AS "Pediatra",
    COUNT(p.dni) AS "Menores Asignados"
FROM medicos m
LEFT JOIN pacientes p ON m.id_medico = p.id_pediatra AND p.es_dependiente = TRUE
WHERE m.tipo_medico = 'especialista'
GROUP BY m.id_medico, m.nombre, m.apellidos
HAVING COUNT(p.dni) > 0
ORDER BY m.id_medico;

\echo ''
\echo '════════════════════════════════════════════════════════════════════════════'
