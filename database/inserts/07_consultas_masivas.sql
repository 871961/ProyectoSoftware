-- =============================================================================
-- CONSULTAS MÉDICAS MASIVAS (800+ consultas)
-- Distribución realista: 5-15 consultas por paciente
-- Variación: diagnósticos, tratamientos y observaciones
-- =============================================================================

-- Paciente 1: Miguel Fernández Cruz (8 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('12345678A', 1, '2024-01-15 09:30:00', 'Hipertensión arterial', 'Lisinopril 10mg', 'TA: 140/90 mmHg', 'Paciente refiere dolor de cabeza ocasional'),
    ('12345678A', 1, '2024-02-20 10:15:00', 'Seguimiento hipertensión', 'Continuar Lisinopril', 'TA: 135/85 mmHg', 'Mejoría clinicamente'),
    ('12345678A', 1, '2024-04-10 14:45:00', 'Dislipidemia', 'Atorvastatina 20mg', 'Colesterol: 220 mg/dL', 'Necesita dieta hiposódica'),
    ('12345678A', 2, '2024-06-05 11:20:00', 'Revisión periódica', 'Mantener medicación', 'Sin cambios relevantes', 'Próxima cita en 3 meses'),
    ('12345678A', 1, '2024-08-18 09:00:00', 'Cefalea', 'Ibuprofeno 400mg', 'Dolor cede en 1-2 horas', 'Paracetamol no tolerado'),
    ('12345678A', 1, '2024-10-22 15:30:00', 'Control anual', 'Mantener tratamiento', 'Parámetros estables', 'Remitir a cardiólogo'),
    ('12345678A', 3, '2024-11-08 10:00:00', 'Consulta cardiología', 'ECG normal', 'Sin arritmias', 'Continuar actual'),
    ('12345678A', 1, '2025-01-12 13:15:00', 'Seguimiento final', 'Mantener terapia', 'Mejora sustancial', 'Próxima en 6 meses');

-- Paciente 2: Ana García López (10 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('23456789B', 2, '2024-01-10 08:30:00', 'Asma leve intermitente', 'Salbutamol inhalado', 'FEV1: 85% del predicho', 'Síntomas nocturnos ocasionales'),
    ('23456789B', 2, '2024-03-15 09:45:00', 'Seguimiento asma', 'Fluticasona inhalada', 'Sin exacerbaciones', 'Mejoría importante'),
    ('23456789B', 2, '2024-05-20 10:30:00', 'Control asma', 'Mantener tratamiento', 'Asma controlada', 'Educación sobre inhaladores'),
    ('23456789B', 2, '2024-07-08 14:20:00', 'Exacerbación leve', 'Aumentar corticoides', 'Mejora en 48 horas', 'Recomendación: evitar alérgenos'),
    ('23456789B', 3, '2024-09-12 11:00:00', 'Consulta especialista', 'Test alergia', 'Positivo para ácaros', 'Derivar a alergología'),
    ('23456789B', 2, '2024-11-03 09:15:00', 'Revisión post-alergia', 'Mantener anti-histamínicos', 'Mejoría de síntomas', 'Seguimiento cercano'),
    ('23456789B', 2, '2025-01-07 10:45:00', 'Control invernal', 'Reforzar inhaladores', 'Sin infecciones respiratorias', 'Vacunación actualizada'),
    ('23456789B', 4, '2025-02-14 13:30:00', 'Segunda opinión', 'Confirmar diagnóstico', 'Asma persistente leve', 'Concordancia con colega'),
    ('23456789B', 2, '2025-03-20 11:20:00', 'Seguimiento trimestral', 'Mantener pautas', 'Control adecuado', 'Educación continua'),
    ('23456789B', 2, '2025-04-18 14:00:00', 'Evaluación final', 'Optimizado tratamiento', 'Buena adherencia', 'Cita anual próxima');

-- Paciente 3: Carlos Rodríguez Martín (12 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('34567890C', 3, '2024-01-22 09:00:00', 'Hipertensión estadío 1', 'Amlodipina 5mg', 'TA: 150/95 mmHg', 'Riesgo cardiovascular moderado'),
    ('34567890C', 3, '2024-02-25 10:15:00', 'Seguimiento TA', 'Aumentar amlodipina a 10mg', 'TA: 145/92 mmHg', 'Añadir Metoprolol'),
    ('34567890C', 3, '2024-04-18 14:30:00', 'Control tension', 'Mantener combinación', 'TA: 138/88 mmHg', 'Adherencia buena'),
    ('34567890C', 5, '2024-06-10 11:45:00', 'Evaluación comorbilidades', 'Solicitar analítica completa', 'Glicemia normal', 'Derivar a nutrición'),
    ('34567890C', 3, '2024-08-05 09:30:00', 'Revisión post-nutrición', 'Dieta hiposódica iniciada', 'Pérdida peso: 3kg', 'Cambios positivos'),
    ('34567890C', 3, '2024-10-12 13:00:00', 'Evaluación cardiovascular', 'ECG solicitado', 'ECG normal', 'Sin cambios isquémicos'),
    ('34567890C', 1, '2024-12-01 10:20:00', 'Segunda consulta general', 'Confirmar diagnóstico', 'Concordancia terapéutica', 'Continuar protocolo'),
    ('34567890C', 3, '2025-01-15 14:45:00', 'Control anual', 'Mantener terapia', 'TA: 135/85 mmHg', 'Muy buena evolución'),
    ('34567890C', 6, '2025-02-20 09:00:00', 'Evaluación cardiológica', 'Ecocardiograma solicitado', 'Normal', 'Función sistólica preservada'),
    ('34567890C', 3, '2025-03-28 11:30:00', 'Seguimiento final', 'Optimización farmacológica', 'Control óptimo', 'Próxima cita en 6 meses'),
    ('34567890C', 3, '2025-04-25 10:15:00', 'Revisión medicación', 'Refinarreglo terapéutico', 'Sin efectos secundarios', 'Muy satisfecho paciente'),
    ('34567890C', 3, '2025-05-15 13:45:00', 'Última revisión trimestral', 'Mantener actual', 'Cifras estables', 'Seguimiento cada 3 meses');

-- Paciente 4: Elena Sánchez González (7 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('45678901D', 4, '2024-02-14 10:30:00', 'Revisión de rutina', 'Sin hallazgos relevantes', 'Parámetros normales', 'Buena salud general'),
    ('45678901D', 4, '2024-04-20 11:00:00', 'Seguimiento anual', 'Mantener estilo vida', 'Sin patologías', 'Preventivo'),
    ('45678901D', 4, '2024-06-15 14:20:00', 'Control presión arterial', 'TA normal', '120/80 mmHg', 'Sin medicación requerida'),
    ('45678901D', 5, '2024-08-10 09:45:00', 'Screening de salud', 'Analítica solicitada', 'Todos normales', 'Buen estado'),
    ('45678901D', 4, '2024-10-05 10:15:00', 'Revisión post-analítica', 'Sin anomalías', 'Colesterol normal', 'Continuidad preventiva'),
    ('45678901D', 4, '2024-12-20 13:30:00', 'Evaluación invernal', 'Vacunación influenza', 'Aplicada sin incidencias', 'Protección adecuada'),
    ('45678901D', 4, '2025-02-18 11:45:00', 'Consulta final del período', 'Mantener hábitos saludables', 'Excelente estado', 'Próxima anual en 12 meses');

-- Paciente 5: Juan López Ramírez (9 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('56789012E', 5, '2024-01-08 08:00:00', 'Diabetes tipo 2 inicial', 'Metformina 500mg', 'Glucosa: 180 mg/dL', 'Sobrepeso significativo'),
    ('56789012E', 5, '2024-03-12 09:30:00', 'Control glucémico', 'Aumentar metformina a 1000mg', 'Glucosa: 150 mg/dL', 'Mejoría notable'),
    ('56789012E', 5, '2024-05-18 10:45:00', 'Seguimiento diabetes', 'Añadir glipizida', 'HbA1c: 8.5%', 'Requiere optimización'),
    ('56789012E', 1, '2024-07-22 14:00:00', 'Evaluación comorbilidades', 'Revisar perfil lipídico', 'Colesterol elevado', 'Posible síndrome metabólico'),
    ('56789012E', 5, '2024-09-14 11:15:00', 'Tratamiento dislipidemia', 'Atorvastatina iniciada', 'Colesterol: 200 mg/dL', 'Mejoría esperada'),
    ('56789012E', 5, '2024-11-10 09:00:00', 'Control trimestral', 'Mantener tratamiento', 'HbA1c: 8.0%', 'Progreso lento'),
    ('56789012E', 6, '2025-01-20 13:20:00', 'Segunda opinión endocrinología', 'Considerar insulina', 'Evaluación completa', 'Derivar a especialista'),
    ('56789012E', 5, '2025-03-08 10:30:00', 'Seguimiento especialista', 'Insulina lispro iniciada', 'Glucosa: 130 mg/dL', 'Mejora significativa'),
    ('56789012E', 5, '2025-05-05 11:45:00', 'Evaluación final trimestre', 'Optimizar dosis insulina', 'HbA1c: 7.5%', 'Objetivo casi alcanzado');

-- Paciente 6: Sofía Martínez Ruiz (6 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('67890123F', 6, '2024-02-10 10:00:00', 'Hipotiroidismo subclínico', 'Levotiroxina 50mcg', 'TSH: 6.2 mIU/L', 'Síntomas leves de fatiga'),
    ('67890123F', 6, '2024-04-15 11:30:00', 'Seguimiento hormonal', 'Mantener levotiroxina', 'TSH: 4.8 mIU/L', 'Mejoría de síntomas'),
    ('67890123F', 6, '2024-06-20 09:45:00', 'Control funcional tiroideo', 'TSH normalizado', 'TSH: 2.5 mIU/L', 'Excelente respuesta'),
    ('67890123F', 6, '2024-09-18 14:15:00', 'Revisión semestral', 'Mantener dosis', 'Sin síntomas', 'Buen estado'),
    ('67890123F', 6, '2024-12-10 10:20:00', 'Control invernal', 'Revisión anual', 'Parámetros óptimos', 'Sin cambios requeridos'),
    ('67890123F', 6, '2025-05-08 13:00:00', 'Seguimiento annual', 'Continuar tratamiento', 'Eutiroidismo', 'Próxima en 12 meses');

-- Paciente 7: Pedro González Moreno (11 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('78901234G', 7, '2024-01-05 08:30:00', 'Hipertensión estadío 2', 'Losartán 50mg', 'TA: 160/100 mmHg', 'Riesgo muy elevado'),
    ('78901234G', 7, '2024-02-18 09:15:00', 'Urgencia hipertensiva', 'Aumentar losartán a 100mg', 'TA: 155/95 mmHg', 'Añadir amlodipina'),
    ('78901234G', 7, '2024-04-10 10:45:00', 'Evaluación de urgencia', 'Solicitar TAC cerebro', 'TAC normal', 'Derivar a urgencias'),
    ('78901234G', 7, '2024-05-20 14:30:00', 'Post-urgencias', 'Mantener combinación', 'TA: 148/92 mmHg', 'Control más frecuente'),
    ('78901234G', 1, '2024-07-08 11:00:00', 'Segunda opinión', 'Confirmar diagnóstico', 'Hipertensión refractaria', 'Considerar tercera línea'),
    ('78901234G', 7, '2024-08-25 09:30:00', 'Intensificación terapéutica', 'Añadir clortalidona', 'TA: 145/88 mmHg', 'Respuesta lenta'),
    ('78901234G', 3, '2024-10-12 13:20:00', 'Evaluación cardiológica', 'ECG muestra LVI', 'Hipertrofia VI', 'Riesgo aumentado'),
    ('78901234G', 7, '2024-11-28 10:15:00', 'Reunión multidisciplinaria', 'Protocolo completo', 'Múltiples morbilidades', 'Seguimiento cercano'),
    ('78901234G', 7, '2025-01-10 14:45:00', 'Evaluación trimestral', 'Ajuste medicación', 'TA: 142/86 mmHg', 'Control subóptimo'),
    ('78901234G', 7, '2025-03-05 11:30:00', 'Seguimiento intensivo', 'Cita mensual programada', 'Adherencia mejora', 'Educación continua'),
    ('78901234G', 7, '2025-04-18 09:00:00', 'Últimas de trimestre', 'Revisar toda medicación', 'TA: 140/84 mmHg', 'Aproximándose al objetivo');

-- Paciente 8: Marta Díaz Flores (8 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('89012345H', 8, '2024-03-20 10:00:00', 'Revisión anual sana', 'Exploración normal', 'Sin patologías', 'Preventivo'),
    ('89012345H', 8, '2024-05-15 11:30:00', 'Control periódico', 'Mantener estilos', 'Parámetros normales', 'Buena salud'),
    ('89012345H', 8, '2024-07-10 09:45:00', 'Seguimiento preventivo', 'Solicitar analítica', 'Resultados normales', 'Sin patologías'),
    ('89012345H', 8, '2024-09-22 14:15:00', 'Control trimestral', 'Sin hallazgos', 'Examen completo negativo', 'Buen estado'),
    ('89012345H', 8, '2024-11-18 10:30:00', 'Evaluación invernal', 'Vacunación influenza', 'Aplicada sin incidencias', 'Protección'),
    ('89012345H', 9, '2025-01-12 13:00:00', 'Valoración segundo médico', 'Confirmar diagnóstico preventivo', 'Sin anomalías', 'Concordancia'),
    ('89012345H', 8, '2025-03-08 11:15:00', 'Seguimiento post-segundo', 'Mantener vigilancia', 'Óptima salud', 'Continuar preventivo'),
    ('89012345H', 8, '2025-05-05 14:45:00', 'Última revisión semestre', 'Próxima anual en 6 meses', 'Excelente estado', 'Muy satisfecho');

-- Paciente 9: Roberto Herrera Jiménez (10 consultas)
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES
    ('90123456I', 9, '2024-01-18 08:30:00', 'Enfermedad celíaca diagnóstico', 'Dieta sin gluten', 'Biopsia positiva', 'Remisión a gastro'),
    ('90123456I', 9, '2024-03-15 10:15:00', 'Seguimiento dieta sin gluten', 'Mantener dieta estricta', 'Anticuerpos en descenso', 'Buena adherencia'),
    ('90123456I', 9, '2024-05-20 11:45:00', 'Control gastrointestinal', 'Sin síntomas GI', 'Recuperación intestinal', 'Excelente'),
    ('90123456I', 9, '2024-07-18 09:30:00', 'Evaluación nutricional', 'Deficiencias corregidas', 'Hemoglobina normal', 'Peso recuperado'),
    ('90123456I', 10, '2024-09-12 13:00:00', 'Consulta especialista', 'Confirmar seguimiento', 'Enfermedad controlada', 'Derivar a psicología'),
    ('90123456I', 9, '2024-11-10 10:20:00', 'Post-psicología', 'Apoyo psicológico iniciado', 'Mejor calidad vida', 'Adaptación buena'),
    ('90123456I', 9, '2025-01-08 14:30:00', 'Seguimiento trimestral', 'Mantener protocolo', 'Sin complicaciones', 'Control excelente'),
    ('90123456I', 9, '2025-03-05 11:15:00', 'Revisión anual celíaca', 'Anticuerpos negativos', 'Curación mucosa', 'Remisión completa'),
    ('90123456I', 9, '2025-04-20 09:45:00', 'Última consulta trimestre', 'Seguimiento anual ahora', 'Estado óptimo', 'Muy satisfecho'),
    ('90123456I', 9, '2025-05-18 13:20:00', 'Educación final', 'Reforzar recomendaciones', 'Paciente educado', 'Próxima en 12 meses');

-- Añadir 700+ consultas más distribuidas entre los 91 pacientes restantes
-- Utilizar GENERATE_SERIES para crear múltiples consultas por paciente
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones)
SELECT
    p.dni,
    (CASE WHEN p.id_medico_general IS NOT NULL THEN p.id_medico_general ELSE p.id_pediatra END),
    CURRENT_TIMESTAMP - INTERVAL '1 day' * (n * 30 + FLOOR(RANDOM() * 25)::INTEGER),
    CASE (n + FLOOR(RANDOM() * 100)::INTEGER) % 10
        WHEN 0 THEN 'Hipertensión arterial' WHEN 1 THEN 'Diabetes tipo 2' WHEN 2 THEN 'Asma'
        WHEN 3 THEN 'Dislipidemia' WHEN 4 THEN 'Hipotiroidismo' WHEN 5 THEN 'Migraña'
        WHEN 6 THEN 'Revisión periódica' WHEN 7 THEN 'Control preventivo'
        WHEN 8 THEN 'Seguimiento crónica' ELSE 'Evaluación general'
    END,
    CASE (n * 7 + FLOOR(RANDOM() * 100)::INTEGER) % 8
        WHEN 0 THEN 'Lisinopril 10mg' WHEN 1 THEN 'Metformina 500mg' WHEN 2 THEN 'Salbutamol'
        WHEN 3 THEN 'Atorvastatina 20mg' WHEN 4 THEN 'Levotiroxina 50mcg' WHEN 5 THEN 'Ibuprofeno'
        WHEN 6 THEN 'Paracetamol 500mg' ELSE 'Mantener actual'
    END,
    CASE (n * 3 + FLOOR(RANDOM() * 100)::INTEGER) % 6
        WHEN 0 THEN 'Parámetros normales' WHEN 1 THEN 'Cifras controladas' WHEN 2 THEN 'Sin hallazgos'
        WHEN 3 THEN 'Mejoría esperada' WHEN 4 THEN 'Requiere seguimiento' ELSE 'Pendiente análisis'
    END,
    CASE (n * 5 + FLOOR(RANDOM() * 100)::INTEGER) % 5
        WHEN 0 THEN 'Paciente adherente' WHEN 1 THEN 'Educar sobre medicación' WHEN 2 THEN 'Próxima cita 1 mes'
        WHEN 3 THEN 'Seguimiento telefónico' ELSE 'Muy satisfecho'
    END
FROM pacientes p, GENERATE_SERIES(1, 12) AS n
WHERE p.dni NOT IN ('12345678A', '23456789B', '34567890C', '45678901D', '56789012E', '67890123F', '78901234G', '89012345H', '90123456I');

COMMIT;
