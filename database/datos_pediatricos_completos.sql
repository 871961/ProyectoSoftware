-- =============================================================================
-- MedHistory - Datos Pediátricos Completos y Dinámicos
-- Datos realistas para niños/dependientes con información médica completa
-- Fecha: Marzo 2026
-- =============================================================================

-- Asegurarse que existen enfermedades en el catálogo
INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES
('Asma infantil'),
('Alergia alimentaria'),
('Dermatitis atópica'),
('Reflujo gastroesofágico'),
('Otitis media recurrente'),
('Bronquiolitis'),
('Gastroenteritis aguda'),
('Varicela'),
('Conjuntivitis alérgica'),
('Faringitis estreptocócica')
ON CONFLICT (nombre_patologia) DO NOTHING;

-- =============================================================================
-- DATOS MÉDICOS COMPLETOS PARA DEPENDIENTES EXISTENTES
-- =============================================================================

DO $$
DECLARE
    pediatra_id INT;
    consulta_id INT;
    enfermedad_id INT;
BEGIN
    -- Obtener ID de pediatra
    SELECT me.id_medico INTO pediatra_id
    FROM medicos_especialistas me
    WHERE me.especialidad = 'Pediatra'
    LIMIT 1;

    IF pediatra_id IS NULL THEN
        SELECT id_medico INTO pediatra_id
        FROM medicos_especialistas
        LIMIT 1;
    END IF;

    -- =========================================================================
    -- SANTIAGO PEREZ GOMEZ (6 años) - DNI: DEP-SAN-180615
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA SANTIAGO
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-SAN-180615', pediatra_id, NOW() - INTERVAL '120 days', 'Otitis media aguda', 'Amoxicilina 500mg cada 8h por 7 días', 'Revisión en 10 días. Oído derecho afectado'),
    ('DEP-SAN-180615', pediatra_id, NOW() - INTERVAL '200 days', 'Control de crecimiento y desarrollo', 'Suplemento vitamina D 400 UI diarias', 'Percentil 75 peso, 80 talla. Desarrollo normal'),
    ('DEP-SAN-180615', pediatra_id, NOW() - INTERVAL '365 days', 'Revisión anual + vacunas', 'Vacuna MMR 2ª dosis', 'Bien tolerada. Próxima cita en 12 meses');

    -- RECORDATORIOS PARA SANTIAGO
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-SAN-180615'
    AND diagnostico LIKE '%Control de crecimiento%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES
        (consulta_id, NOW() + INTERVAL '6 months', 'Control', 'Control semestral de crecimiento', 'Pendiente'),
        (consulta_id, NOW() + INTERVAL '3 months', 'Medicación', 'Continuar vitamina D hasta próxima revisión', 'Pendiente');
    END IF;

    -- VACUNAS ADICIONALES PARA SANTIAGO
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-SAN-180615', 'MMR (Sarampión-Rubeola-Paperas)', '2023-06-15', '2ª dosis', 'Centro Salud Madrid', 'Administrada sin incidencias', 'Administrada'),
    ('DEP-SAN-180615', 'Varicela', '2022-06-15', '1ª dosis', 'Centro Salud Madrid', 'Administrada correctamente', 'Administrada'),
    ('DEP-SAN-180615', 'Meningococo B', '2024-01-15', '1ª dosis', 'Centro Salud Madrid', 'Pendiente 2ª dosis en 2 meses', 'Administrada');

    -- =========================================================================
    -- LUCIA MARTIN (8 años) - DNI: DEP-LUC-160203
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA LUCIA
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-LUC-160203', pediatra_id, NOW() - INTERVAL '15 days', 'Exacerbación de dermatitis atópica', 'Hidrocortisona 1% crema 2 veces al día por 5 días', 'Aplicar después del baño. Evitar jabones perfumados'),
    ('DEP-LUC-160203', pediatra_id, NOW() - INTERVAL '90 days', 'Revisión rutinaria + revisión visual', 'Continuar tratamiento actual', 'Agudeza visual normal. Eccema controlado'),
    ('DEP-LUC-160203', pediatra_id, NOW() - INTERVAL '180 days', 'Consulta por tos nocturna persistente', 'Salbutamol inhalador si precisa', 'Posible componente asmático. Seguimiento');

    -- RECORDATORIOS PARA LUCIA
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-LUC-160203'
    AND diagnostico LIKE '%dermatitis%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES
        (consulta_id, NOW() + INTERVAL '2 weeks', 'Control', 'Revisión evolución de dermatitis', 'Pendiente'),
        (consulta_id, NOW() + INTERVAL '1 month', 'Cita', 'Valoración por dermatólogo pediátrico', 'Pendiente');
    END IF;

    -- VACUNAS ADICIONALES PARA LUCIA
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-LUC-160203', 'HPV (Virus Papiloma Humano)', '2024-02-03', '1ª dosis', 'Centro Salud Madrid', 'Programa vacunal adolescentes', 'Administrada'),
    ('DEP-LUC-160203', 'DTPa (Difteria-Tétanos-Tosferina)', '2022-02-03', 'Refuerzo', 'Centro Salud Madrid', 'Refuerzo a los 6 años', 'Administrada'),
    ('DEP-LUC-160203', 'Gripe estacional 2025', '2025-10-15', 'Anual', 'Centro Salud Madrid', 'Recomendada por dermatitis atópica', 'Administrada');

    -- =========================================================================
    -- MATEO RUIZ (4 años) - DNI: DEP-MAT-201120
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA MATEO
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-MAT-201120', pediatra_id, NOW() - INTERVAL '60 days', 'Gastroenteritis aguda', 'Dieta astringente + SRO', 'Hidratación oral. Resolución en 48h'),
    ('DEP-MAT-201120', pediatra_id, NOW() - INTERVAL '150 days', 'Control alergológico + pruebas cutáneas', 'Evitar huevo y derivados', 'IgE específica elevada. Derivado a alergología'),
    ('DEP-MAT-201120', pediatra_id, NOW() - INTERVAL '300 days', 'Bronquiolitis por VRS', 'Tratamiento sintomático + fisioterapia respiratoria', 'Evolución favorable. Control en 1 semana');

    -- RECORDATORIOS PARA MATEO
    SELECT id_consulta INTO consulta_id
    FROM consultas
    WHERE id_paciente = 'DEP-MAT-201120'
    AND diagnostico LIKE '%alergológico%'
    LIMIT 1;

    IF consulta_id IS NOT NULL THEN
        INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES
        (consulta_id, NOW() + INTERVAL '3 months', 'Cita', 'Revisión con alergólogo pediátrico', 'Pendiente'),
        (consulta_id, NOW() + INTERVAL '1 month', 'Control', 'Control evolución alergia al huevo', 'Pendiente');
    END IF;

    -- VACUNAS ADICIONALES PARA MATEO
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-MAT-201120', 'Rotavirus', '2021-01-20', '1ª dosis', 'Hospital Local', 'Administrada oral', 'Administrada'),
    ('DEP-MAT-201120', 'Neumococo 13-valente', '2021-03-20', '2ª dosis', 'Centro Salud', 'Pauta completa según calendario', 'Administrada'),
    ('DEP-MAT-201120', 'MMR', '2022-11-20', '1ª dosis', 'Centro Salud', 'Administrada a los 12 meses', 'Administrada');

    -- =========================================================================
    -- ELENA SOTO (7 años) - DNI: DEP-ELENA-170709
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA ELENA
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-ELENA-170709', pediatra_id, NOW() - INTERVAL '45 days', 'Faringitis estreptocócica', 'Penicilina V oral 10 días', 'Cultivo positivo para Streptococo A. Buena evolución'),
    ('DEP-ELENA-170709', pediatra_id, NOW() - INTERVAL '160 days', 'Revisión oftalmológica preventiva', 'No tratamiento necesario', 'Agudeza visual normal. Próxima revisión en 2 años'),
    ('DEP-ELENA-170709', pediatra_id, NOW() - INTERVAL '280 days', 'Varicela', 'Tratamiento sintomático', 'Evolución típica. Aislamiento domiciliario 7 días');

    -- VACUNAS ADICIONALES PARA ELENA
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-ELENA-170709', 'DTPa', '2023-07-09', 'Refuerzo 6 años', 'Centro Salud', 'Refuerzo escolar administrado correctamente', 'Administrada'),
    ('DEP-ELENA-170709', 'Polio (IPV)', '2023-07-09', 'Refuerzo', 'Centro Salud', 'Junto con DTPa', 'Administrada'),
    ('DEP-ELENA-170709', 'Varicela', '2023-01-15', '2ª dosis', 'Centro Salud', 'Completar pauta tras varicela natural', 'Administrada');

    -- =========================================================================
    -- HUGO ALVAREZ (5 años) - DNI: DEP-HUGO-20191212
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA HUGO
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-HUGO-20191212', pediatra_id, NOW() - INTERVAL '25 days', 'Seguimiento alergia a proteínas de leche', 'Continuar fórmula hidrolizada', 'Tolerancia progresiva. Introducir lácteos gradualmente'),
    ('DEP-HUGO-20191212', pediatra_id, NOW() - INTERVAL '85 days', 'Bronquitis aguda', 'Salbutamol inhalador + corticoide tópico', 'Evolución favorable en 5 días'),
    ('DEP-HUGO-20191212', pediatra_id, NOW() - INTERVAL '200 days', 'Control de peso y crecimiento', 'Dieta variada + suplemento hierro', 'Percentil 50 peso. Leve tendencia anémica');

    -- VACUNAS ADICIONALES PARA HUGO
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-HUGO-20191212', 'MMR', '2021-12-12', '1ª dosis', 'Centro Salud', 'Administrada a los 12 meses', 'Administrada'),
    ('DEP-HUGO-20191212', 'Varicela', '2023-12-12', '1ª dosis', 'Centro Salud', 'Administrada a los 15 meses', 'Administrada'),
    ('DEP-HUGO-20191212', 'Gripe estacional 2025', '2025-11-01', 'Anual', 'Centro Salud', 'Recomendada por alergia', 'Administrada');

    -- =========================================================================
    -- ABRIL MORENO (4 años) - DNI: DEP-ABRIL-20200405
    -- =========================================================================

    -- CONSULTAS ADICIONALES PARA ABRIL
    INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
    ('DEP-ABRIL-20200405', pediatra_id, NOW() - INTERVAL '35 days', 'Conjuntivitis alérgica', 'Colirio antihistamínico', 'Mejoría en 3 días. Relacionado con polen primaveral'),
    ('DEP-ABRIL-20200405', pediatra_id, NOW() - INTERVAL '120 days', 'Revisión del habla - retraso leve', 'Estimulación logopédica', 'Derivada a logopeda. Control en 3 meses'),
    ('DEP-ABRIL-20200405', pediatra_id, NOW() - INTERVAL '240 days', 'Infección urinaria', 'Amoxicilina-clavulánico 7 días', 'Urocultivo negativo a las 48h. Resolución completa');

    -- VACUNAS ADICIONALES PARA ABRIL
    INSERT INTO cartilla_vacunas (id_paciente, nombre_vacuna, fecha_administracion, dosis, centro, observaciones, estado) VALUES
    ('DEP-ABRIL-20200405', 'MMR', '2021-04-05', '1ª dosis', 'Hospital Local', 'Administrada a los 12 meses sin incidencias', 'Administrada'),
    ('DEP-ABRIL-20200405', 'Varicela', '2021-04-05', '1ª dosis', 'Hospital Local', 'Junto con MMR', 'Administrada'),
    ('DEP-ABRIL-20200405', 'Meningococo ACWY', '2024-04-05', '1ª dosis', 'Centro Salud', 'Nueva en calendario vacunal', 'Administrada');

END $$;

-- =============================================================================
-- ANTECEDENTES FAMILIARES ADICIONALES PARA MAYOR REALISMO
-- =============================================================================

DO $$
DECLARE
    enf_id INT;
BEGIN
    -- Antecedentes adicionales más detallados para completar historiales

    -- Santiago - Antecedentes paternos adicionales
    SELECT id_enfermedad INTO enf_id FROM enfermedades_catalogo WHERE nombre_patologia = 'Asma infantil' LIMIT 1;
    IF enf_id IS NOT NULL THEN
        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES ('DEP-SAN-180615', enf_id, 'tio_paterno', 'paterno', 12, 'Tío con asma desde la infancia, controlado con inhaladores');
    END IF;

    -- Lucia - Antecedentes maternos adicionales
    SELECT id_enfermedad INTO enf_id FROM enfermedades_catalogo WHERE nombre_patologia = 'Dermatitis atópica' LIMIT 1;
    IF enf_id IS NOT NULL THEN
        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES ('DEP-LUC-160203', enf_id, 'abuela_materna', 'materno', 25, 'Abuela con eccema en la juventud');
    END IF;

    -- Mateo - Antecedentes de alergia alimentaria
    SELECT id_enfermedad INTO enf_id FROM enfermedades_catalogo WHERE nombre_patologia = 'Alergia alimentaria' LIMIT 1;
    IF enf_id IS NOT NULL THEN
        INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales)
        VALUES ('DEP-MAT-201120', enf_id, 'prima_materna', 'materno', 5, 'Prima con alergia múltiple (huevo, frutos secos)');
    END IF;

END $$;

-- =============================================================================
-- ACTUALIZAR PERFILES DE SALUD CON DATOS MÁS REALISTAS
-- =============================================================================

-- Actualizar con datos más específicos y realistas para cada edad
UPDATE perfiles_salud SET
    peso_kg = 22.5,
    altura_cm = 118.0,
    actividad_fisica = 'Alta - juegos al aire libre diarios',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Antecedente de otitis media recurrente'
WHERE id_paciente = 'DEP-SAN-180615';

UPDATE perfiles_salud SET
    peso_kg = 28.0,
    altura_cm = 126.0,
    actividad_fisica = 'Moderada - natación 2 veces/semana',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Dermatitis atópica activa, posible asma'
WHERE id_paciente = 'DEP-LUC-160203';

UPDATE perfiles_salud SET
    peso_kg = 16.5,
    altura_cm = 98.0,
    actividad_fisica = 'Alta - parque y triciclo',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Alergia IgE mediada a huevo, antecedente de bronquiolitis'
WHERE id_paciente = 'DEP-MAT-201120';

UPDATE perfiles_salud SET
    peso_kg = 21.0,
    altura_cm = 115.0,
    actividad_fisica = 'Moderada - baile y juegos',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Antecedente de varicela, faringitis recurrentes'
WHERE id_paciente = 'DEP-ELENA-170709';

UPDATE perfiles_salud SET
    peso_kg = 17.0,
    altura_cm = 102.0,
    actividad_fisica = 'Alta - fútbol y bicicleta',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Alergia a proteínas de leche de vaca, tendencia anémica'
WHERE id_paciente = 'DEP-HUGO-20191212';

UPDATE perfiles_salud SET
    peso_kg = 14.5,
    altura_cm = 95.0,
    actividad_fisica = 'Moderada - juegos en casa y parque',
    consumo_tabaco = 'No aplica - menor',
    consumo_alcohol = 'No aplica - menor',
    enfermedades = 'Retraso leve del lenguaje, conjuntivitis alérgica estacional'
WHERE id_paciente = 'DEP-ABRIL-20200405';

-- =============================================================================
-- COMENTARIOS FINALES
-- =============================================================================
-- Este script proporciona datos pediátricos completos y realistas:
-- ✅ Consultas médicas detalladas con pediatras
-- ✅ Perfiles de salud apropiados para cada edad
-- ✅ Antecedentes familiares relevantes
-- ✅ Cartilla de vacunación actualizada
-- ✅ Recordatorios de seguimiento pediátrico
-- ✅ Diagnósticos y tratamientos típicos de pediatría
-- =============================================================================