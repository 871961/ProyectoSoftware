-- Test query: Verificar que todo está correcto para PacienteXX
SELECT 
    'PACIENTE' as tipo,
    p.dni,
    p.nombre,
    p.apellidos,
    CAST(p.id_medico_general AS TEXT) as extra,
    p.activo
FROM pacientes p
WHERE p.dni = '25209659A'

UNION ALL

SELECT 
    'MEDICO' as tipo,
    CAST(m.id_medico AS VARCHAR),
    m.nombre,
    m.apellidos,
    m.email as extra,
    m.activo
FROM medicos m
WHERE m.id_medico = 8

UNION ALL

SELECT 
    'PERFIL_SALUD' as tipo,
    ps.id_paciente as dni,
    CAST(ps.altura_cm AS VARCHAR) as dato1,
    CAST(ps.peso_kg AS VARCHAR) as dato2,
    ps.alergias as extra,
    TRUE
FROM perfiles_salud ps
WHERE ps.id_paciente = '25209659A'

UNION ALL

SELECT 
    'ANTECEDENTE_' || CAST(af.id_antecedente AS VARCHAR) as tipo,
    af.id_paciente as dni,
    ec.nombre_patologia,
    af.parentesco,
    COALESCE(af.lado_familiar, 'N/A') as extra,
    af.activo
FROM antecedentes_familiares af
JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
WHERE af.id_paciente = '25209659A' AND af.activo = TRUE;
