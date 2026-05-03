-- =============================================================================
-- MÉDICOS: 50 TOTAL
-- 15 Generales + 10 Pediatras + 25 Especialistas
-- Contraseña: test123
-- Hash: $2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i
-- =============================================================================

-- ─────────────────────────────────────────────────────────────────────────────
-- MÉDICOS GENERALES (15)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico, activo)
VALUES
    ('Juan', 'García López', 'juan.garcia@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234567', 'CMED001', 'general', TRUE),
    ('María', 'Rodríguez Martín', 'maria.rodriguez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234568', 'CMED002', 'general', TRUE),
    ('Carlos', 'Fernández Pérez', 'carlos.fernandez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234569', 'CMED003', 'general', TRUE),
    ('Ana', 'Sánchez González', 'ana.sanchez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234570', 'CMED004', 'general', TRUE),
    ('Miguel', 'López Ramírez', 'miguel.lopez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234571', 'CMED005', 'general', TRUE),
    ('Elena', 'Martínez Ruiz', 'elena.martinez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234572', 'CMED006', 'general', TRUE),
    ('Pedro', 'González Moreno', 'pedro.gonzalez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234573', 'CMED007', 'general', TRUE),
    ('Isabel', 'Díaz Flores', 'isabel.diaz@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234574', 'CMED008', 'general', TRUE),
    ('Francisco', 'Herrera Jiménez', 'francisco.herrera@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234575', 'CMED009', 'general', TRUE),
    ('Rocío', 'Castillo Vega', 'rocio.castillo@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234576', 'CMED010', 'general', TRUE),
    ('David', 'Vargas Cruz', 'david.vargas@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234577', 'CMED011', 'general', TRUE),
    ('Marta', 'Romero Domínguez', 'marta.romero@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234578', 'CMED012', 'general', TRUE),
    ('Roberto', 'Ortiz Navarro', 'roberto.ortiz@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234579', 'CMED013', 'general', TRUE),
    ('Sofía', 'Guerrero Molina', 'sofia.guerrero@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234580', 'CMED014', 'general', TRUE),
    ('Javier', 'Morales Gómez', 'javier.morales@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234581', 'CMED015', 'general', TRUE);

-- Insertar en medicos_generales
INSERT INTO medicos_generales (id_medico, pacientes_asignados)
SELECT id_medico, 0 FROM medicos WHERE tipo_medico = 'general' AND id_medico <= 15;

-- ─────────────────────────────────────────────────────────────────────────────
-- MÉDICOS ESPECIALISTAS: PEDIATRAS (10)
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico, activo)
VALUES
    ('Amparo', 'Ruiz García', 'amparo.ruiz@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234582', 'CMED016', 'especialista', TRUE),
    ('Inmaculada', 'Soto López', 'inmaculada.soto@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234583', 'CMED017', 'especialista', TRUE),
    ('Gloria', 'Navarro Álvarez', 'gloria.navarro@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234584', 'CMED018', 'especialista', TRUE),
    ('Montserrat', 'Carrillo Martín', 'montserrat.carrillo@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234585', 'CMED019', 'especialista', TRUE),
    ('Beatriz', 'Villarreal Gómez', 'beatriz.villarreal@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234586', 'CMED020', 'especialista', TRUE),
    ('Pilar', 'Quintero Pérez', 'pilar.quintero@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234587', 'CMED021', 'especialista', TRUE),
    ('Dolores', 'Cervantes Ríos', 'dolores.cervantes@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234588', 'CMED022', 'especialista', TRUE),
    ('Consuelo', 'Rosas Cabrera', 'consuelo.rosas@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234589', 'CMED023', 'especialista', TRUE),
    ('Antonia', 'Ledesma Sánchez', 'antonia.ledesma@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234590', 'CMED024', 'especialista', TRUE),
    ('Margarita', 'Munguía Torres', 'margarita.munguia@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234591', 'CMED025', 'especialista', TRUE);

-- Insertar en medicos_especialistas como Pediatras
INSERT INTO medicos_especialistas (id_medico, especialidad)
SELECT id_medico, 'Pediatría' FROM medicos WHERE tipo_medico = 'especialista' AND id_medico >= 16 AND id_medico <= 25;

-- ─────────────────────────────────────────────────────────────────────────────
-- MÉDICOS ESPECIALISTAS (25) - DIVERSAS ESPECIALIDADES
-- ─────────────────────────────────────────────────────────────────────────────

INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico, activo)
VALUES
    ('Enrique', 'Campos Araya', 'enrique.campos@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234592', 'CMED026', 'especialista', TRUE),
    ('Guillermo', 'Benítez Vázquez', 'guillermo.benitez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234593', 'CMED027', 'especialista', TRUE),
    ('Andrés', 'Páez Mendoza', 'andres.paez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234594', 'CMED028', 'especialista', TRUE),
    ('Álvaro', 'Gallego Iglesias', 'alvaro.gallego@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234595', 'CMED029', 'especialista', TRUE),
    ('Víctor', 'Montoya Reyes', 'victor.montoya@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234596', 'CMED030', 'especialista', TRUE),
    ('Ignacio', 'Bravo Fuentes', 'ignacio.bravo@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234597', 'CMED031', 'especialista', TRUE),
    ('Tomás', 'Delgado Santana', 'tomas.delgado@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234598', 'CMED032', 'especialista', TRUE),
    ('Aurelio', 'Fuentes Acosta', 'aurelio.fuentes@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234599', 'CMED033', 'especialista', TRUE),
    ('Julio', 'Calvo Morales', 'julio.calvo@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234600', 'CMED034', 'especialista', TRUE),
    ('Ángel', 'Silva Nunes', 'angel.silva@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234601', 'CMED035', 'especialista', TRUE),
    ('Ramón', 'Jiménez Herrera', 'ramon.jimenez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234602', 'CMED036', 'especialista', TRUE),
    ('Mateo', 'Peña Iglesias', 'mateo.pena@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234603', 'CMED037', 'especialista', TRUE),
    ('Rodrigo', 'Fuentes Ramírez', 'rodrigo.fuentes@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234604', 'CMED038', 'especialista', TRUE),
    ('Salvador', 'Rodríguez Torres', 'salvador.rodriguez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234605', 'CMED039', 'especialista', TRUE),
    ('Esteban', 'Martínez Castillo', 'esteban.martinez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234606', 'CMED040', 'especialista', TRUE),
    ('Benito', 'Flores Núñez', 'benito.flores@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234607', 'CMED041', 'especialista', TRUE),
    ('Pascual', 'García Reyes', 'pascual.garcia@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234608', 'CMED042', 'especialista', TRUE),
    ('Emilio', 'López Vargas', 'emilio.lopez@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234609', 'CMED043', 'especialista', TRUE),
    ('Leopoldo', 'Sáenz Gómez', 'leopoldo.saenz@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234610', 'CMED044', 'especialista', TRUE),
    ('Feliciano', 'Vidal Iglesias', 'feliciano.vidal@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234611', 'CMED045', 'especialista', TRUE),
    ('Gerardo', 'Marín Salazar', 'gerardo.marin@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234612', 'CMED046', 'especialista', TRUE),
    ('Baudilio', 'Cabrera López', 'baudilio.cabrera@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234613', 'CMED047', 'especialista', TRUE),
    ('Marcelo', 'Alarcón García', 'marcelo.alarcon@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234614', 'CMED048', 'especialista', TRUE),
    ('Fabio', 'Zambrano Ramírez', 'fabio.zambrano@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234615', 'CMED049', 'especialista', TRUE),
    ('Cristóbal', 'Córdoba Mendoza', 'cristobal.cordoba@clinica.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '915234616', 'CMED050', 'especialista', TRUE);

-- Insertar especialidades para los 25 especialistas (excluyendo pediatras)
INSERT INTO medicos_especialistas (id_medico, especialidad)
VALUES
    (26, 'Cardiología'),
    (27, 'Dermatología'),
    (28, 'Neurología'),
    (29, 'Oftalmología'),
    (30, 'Otorrinolaringología'),
    (31, 'Traumatología'),
    (32, 'Cirugía General'),
    (33, 'Ginecología'),
    (34, 'Oncología'),
    (35, 'Neumología'),
    (36, 'Gastroenterología'),
    (37, 'Endocrinología'),
    (38, 'Reumatología'),
    (39, 'Nefrología'),
    (40, 'Hematología'),
    (41, 'Psiquiatría'),
    (42, 'Urología'),
    (43, 'Radiología'),
    (44, 'Anestesiología'),
    (45, 'Medicina Intensiva'),
    (46, 'Fisioterapia'),
    (47, 'Nutrición'),
    (48, 'Oftalmología'),
    (49, 'Estética'),
    (50, 'Medicina Deportiva');

COMMIT;
