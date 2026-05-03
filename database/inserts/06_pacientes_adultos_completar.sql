-- =============================================================================
-- PACIENTES ADULTOS ADICIONALES (10 más para completar 70 total)
-- Completar la serie de adultos para tener exactamente 70
-- =============================================================================

INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general, grupo_sanguineo, alergias, activo)
VALUES
    ('32345670KK', 'Marisa', 'Fuentes López', 'marisa.fuentes@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123516', 'Calle Novela 61, Madrid', '1984-06-15', '12 3456789012405', 1, 'O+', 'Iodo', TRUE),
    ('43456781LL', 'Venancio', 'Rosales García', 'venancio.rosales@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123517', 'Calle Realejo 62, Granada', '1991-08-03', '12 3456789012406', 2, 'A+', NULL, TRUE),
    ('54567892MM', 'Dora', 'Montaño Jiménez', 'dora.montano@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123518', 'Calle Agua 63, Granada', '1980-10-27', '12 3456789012407', 3, 'B-', 'Cefalosporinas', TRUE),
    ('65678903NN', 'Timoteo', 'Ayala Ramón', 'timoteo.ayala@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123519', 'Calle Navas 64, Granada', '1989-01-12', '12 3456789012408', 4, 'AB+', NULL, TRUE),
    ('76789014OO', 'Luz', 'Núñez Camacho', 'luz.nunez@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123520', 'Calle Elvira 65, Granada', '1987-04-19', '12 3456789012409', 5, 'O-', 'Acetaminofén', TRUE),
    ('87890125PP', 'Aquilino', 'Ibáñez Baez', 'aquilino.ibanez@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123521', 'Calle Zacatín 66, Granada', '1993-07-05', '12 3456789012410', 6, 'A-', NULL, TRUE),
    ('98901236QQ', 'Filomena', 'Estrada Sánchez', 'filomena.estrada@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123522', 'Calle Comercio 67, Jaén', '1982-09-14', '12 3456789012411', 7, 'B+', 'Nitrofurantoína', TRUE),
    ('09012347RR', 'Braulio', 'Sáenz Moreno', 'braulio.saenz@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123523', 'Calle Maestra 68, Jaén', '1988-11-22', '12 3456789012412', 8, 'AB-', NULL, TRUE),
    ('10123458SS', 'Cesilia', 'Lara Gómez', 'cesilia.lara@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123524', 'Calle Nueva 69, Jaén', '1990-02-09', '12 3456789012413', 9, 'O+', 'Terbinafina', TRUE),
    ('21234569TT', 'Diamantino', 'Vázquez Castro', 'diamantino.vazquez@gmail.com', '$2y$10$31kbV2ggSTYTzaCKwt6IIuZOmvKcG4BkQpB2lZn47V.wDB/krms2i', '689123525', 'Calle Rastro 70, Jaén', '1986-05-30', '12 3456789012414', 10, 'A+', NULL, TRUE);

-- Insertar perfiles de salud para los 10 adultos adicionales
INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades, actividad_fisica, consumo_tabaco, consumo_alcohol)
VALUES
    ('32345670KK', 74.0, 173, 'Iodo', 'Hipotiroidismo', 'Moderada', 'No fuma', 'Ocasional'),
    ('43456781LL', 69.8, 170, NULL, NULL, 'Alta', 'No fuma', 'Nunca'),
    ('54567892MM', 84.2, 179, 'Cefalosporinas', 'Asma', 'Baja', 'Fumador activo', 'Moderado'),
    ('65678903NN', 71.5, 172, NULL, 'Migraña', 'Alta', 'No fuma', 'Ocasional'),
    ('76789014OO', 78.9, 175, 'Acetaminofén', 'Dislipidemia', 'Moderada', 'Exfumador', 'Nunca'),
    ('87890125PP', 67.3, 168, NULL, 'Asma', 'Alta', 'No fuma', 'Moderado'),
    ('98901236QQ', 86.5, 181, 'Nitrofurantoína', 'Diabetes tipo 2', 'Baja', 'Fumador activo', 'Ocasional'),
    ('09012347RR', 72.8, 174, NULL, 'Hipertensión arterial', 'Moderada', 'No fuma', 'Nunca'),
    ('10123458SS', 65.1, 166, 'Terbinafina', NULL, 'Alta', 'No fuma', 'Moderado'),
    ('21234569TT', 81.4, 177, NULL, 'Alergia', 'Baja', 'Exfumador', 'Ocasional');

COMMIT;
