-- Datos de Prueba - MedHistory
-- Ejecutar despues de reinstalar_completo.sql o schema.sql
-- Fecha: 7 de marzo de 2026

-- 1. ADMINISTRADORES (2)
INSERT INTO administradores (nombre, apellidos, email, contrasena_hash) VALUES
('Claudia', 'Mateo', 'claudia.mateo@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Yousra', 'Jebari', 'yousra.jebari@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 2. MEDICOS GENERALES (3)
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico) VALUES
('Elena', 'Fernandez Sanchez', 'elena.fernandez@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '612-345-678', 'MG-001234', 'general'),
('Carlos', 'Lopez Jimenez', 'carlos.lopez@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '613-456-789', 'MG-002345', 'general'),
('Ana', 'Martinez Ruiz', 'ana.martinez@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '614-567-890', 'MG-003456', 'general');

INSERT INTO medicos_generales (id_medico, pacientes_asignados) VALUES
(1, 0), (2, 0), (3, 0);

-- 3. MEDICOS ESPECIALISTAS (5)
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico) VALUES
('Miguel', 'Rodriguez Gomez', 'miguel.rodriguez@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '623-456-789', 'CD-005678', 'especialista'),
('Laura', 'Garcia Santos', 'laura.garcia@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '624-567-890', 'DM-006789', 'especialista'),
('Pedro', 'Sanchez Moreno', 'pedro.sanchez@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '625-678-901', 'TR-007890', 'especialista'),
('Isabel', 'Diaz Fernandez', 'isabel.diaz@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '626-789-012', 'GN-008901', 'especialista'),
('Roberto', 'Navarro Torres', 'roberto.navarro@clinica.com', '$2y$10$Joc080WMq2rrLGPLGwUQ2O7wKUgjmmgTavdOT/xYLD44c43mXWvta', '627-890-123', 'NR-009012', 'especialista');

INSERT INTO medicos_especialistas (id_medico, especialidad) VALUES
(4, 'Cardiologia'),
(5, 'Dermatologia'),
(6, 'Traumatologia'),
(7, 'Ginecologia'),
(8, 'Neurologia');

-- 4. PACIENTES (10)
INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general) VALUES
('12345678A', 'Maria', 'Perez Garcia', 'maria.perez@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '667-890-123', 'Calle Alcala 73, Madrid', '1985-03-15', '280315123456', 1),
('23456789B', 'Jose', 'Martin Lopez', 'jose.martin@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '678-901-234', 'Paseo del Prado 150, Madrid', '1978-07-22', '220778234567', 2),
('34567890C', 'Laura', 'Ruiz Herrera', 'laura.ruiz@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '689-012-345', 'Calle Goya 89, Madrid', '1992-11-08', '081192345678', 3),
('45678901D', 'Antonio', 'Sanchez Verde', 'antonio.sanchez@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '690-123-456', 'Avenida America 200, Madrid', '1965-05-12', '120565456789', 1),
('56789012E', 'Patricia', 'Moreno Castro', 'patricia.moreno@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '601-234-567', 'Calle Bravo Murillo 75, Madrid', '1989-09-30', '300989567890', 2),
('67890123F', 'Francisco', 'Jimenez Ortiz', 'francisco.jimenez@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '602-345-678', 'Calle Serrano 120, Madrid', '1995-06-18', '180695678901', 3),
('78901234G', 'Carmen', 'Ramirez Blanco', 'carmen.ramirez@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '603-456-789', 'Plaza Mayor 8, Madrid', '1980-12-03', '031280789012', 1),
('89012345H', 'David', 'Torres Vega', 'david.torres@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '604-567-890', 'Calle Velazquez 45, Madrid', '1988-04-25', '250488890123', 2),
('90123456I', 'Sofia', 'Romero Iglesias', 'sofia.romero@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '605-678-901', 'Gran Via 28, Madrid', '1993-11-14', '141193901234', 3),
('01234567J', 'Miguel', 'Fernandez Cruz', 'miguel.fernandez@email.com', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO', '606-789-012', 'Calle Princesa 67, Madrid', '1976-08-09', '090876012345', 1);

UPDATE medicos_generales SET pacientes_asignados = (
    SELECT COUNT(*) FROM pacientes WHERE pacientes.id_medico_general = medicos_generales.id_medico AND pacientes.activo = TRUE
);

-- 5. CATALOGO DE ENFERMEDADES
INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES
('Diabetes Mellitus Tipo 2'),
('Hipertension Arterial'),
('Cardiopatia Isquemica'),
('Cancer de Mama'),
('Cancer de Prostata'),
('Asma Bronquica'),
('Artritis Reumatoide'),
('Osteoporosis'),
('Depresion Mayor'),
('Alzheimer'),
('Enfermedad Renal Cronica'),
('Fibromialgia'),
('Migrana'),
('Epilepsia'),
('Tiroides Hipotiroidismo'),
('Cancer de Colon'),
('Obesidad'),
('Anemia Ferropenica');

-- 6. PERFILES DE SALUD (10 pacientes)
INSERT INTO perfiles_salud (id_paciente, peso, altura, alergias, actividad_fisica, consumo_tabaco) VALUES 
('12345678A', 65.5, 1.68, 'Alergia a penicilina', 'Ejercicio moderado 3 veces por semana', 'No fumador'),
('23456789B', 82.3, 1.75, 'Ninguna conocida', 'Sedentario', 'Ex-fumador hace 5 anos'),
('34567890C', 58.0, 1.62, 'Alergia a acaros, polen', 'Running diario', 'No fumador'),
('45678901D', 78.0, 1.80, 'Alergia a ibuprofeno', 'Caminar 30 min diarios', 'No fumador'),
('56789012E', 62.0, 1.65, 'Ninguna conocida', 'Ejercicio moderado', 'No fumador'),
('67890123F', 75.5, 1.77, 'Alergia a mariscos', 'Gimnasio 4 veces por semana', 'No fumador'),
('78901234G', 69.0, 1.70, 'Alergia a lactosa', 'Yoga 2 veces por semana', 'No fumador'),
('89012345H', 85.0, 1.83, 'Ninguna conocida', 'Futbol los fines de semana', 'Fumador'),
('90123456I', 55.0, 1.60, 'Alergia a aspirina', 'Natacion 3 veces por semana', 'No fumador'),
('01234567J', 90.0, 1.78, 'Ninguna conocida', 'Sedentario', 'Ex-fumador hace 2 anos');

-- 7. ANTECEDENTES FAMILIARES (30 registros - 3 por paciente en promedio)
INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, lado_familiar, edad_diagnostico, notas_adicionales, activo) VALUES
-- Paciente 12345678A (Maria)
('12345678A', 1, 'padre', 'paterno', 55, 'Diagnosticado en chequeo rutinario', TRUE),
('12345678A', 2, 'madre', 'materno', 48, 'En tratamiento desde hace 10 años', TRUE),
('12345678A', 4, 'abuela_materna', 'materno', 62, 'Cancer de mama detectado en estadio 2', TRUE),
-- Paciente 23456789B (Jose)
('23456789B', 3, 'padre', 'paterno', 60, 'Infarto agudo de miocardio', TRUE),
('23456789B', 2, 'madre', 'materno', 58, 'Hipertension controlada con medicacion', TRUE),
('23456789B', 10, 'abuelo_paterno', 'paterno', 78, 'Alzheimer diagnosticado a edad avanzada', TRUE),
-- Paciente 34567890C (Laura)
('34567890C', 6, 'hermano', NULL, 8, 'Asma desde la infancia', TRUE),
('34567890C', 13, 'madre', 'materno', 35, 'Migranas cronicas', TRUE),
('34567890C', 7, 'abuela_paterna', 'paterno', 65, 'Artritis reumatoide', TRUE),
-- Paciente 45678901D (Antonio)
('45678901D', 5, 'padre', 'paterno', 68, 'Cancer de prostata tratado exitosamente', TRUE),
('45678901D', 2, 'padre', 'paterno', 52, 'Tambien hipertension arterial', TRUE),
('45678901D', 11, 'madre', 'materno', 70, 'Enfermedad renal cronica', TRUE),
-- Paciente 56789012E (Patricia)
('56789012E', 1, 'padre', 'paterno', 50, 'Diabetes tipo 2 controlada con dieta', TRUE),
('56789012E', 8, 'abuela_materna', 'materno', 72, 'Osteoporosis severa', TRUE),
('56789012E', 9, 'madre', 'materno', 45, 'Depresion mayor tratada', TRUE),
-- Paciente 67890123F (Francisco)
('67890123F', 2, 'padre', 'paterno', 53, 'Hipertension desde los 50 años', TRUE),
('67890123F', 16, 'abuelo_paterno', 'paterno', 65, 'Cancer de colon detectado temprano', TRUE),
('67890123F', 1, 'tio', 'paterno', 48, 'Diabetes tipo 2', TRUE),
-- Paciente 78901234G (Carmen)
('78901234G', 4, 'madre', 'materno', 55, 'Cancer de mama tratado con quimioterapia', TRUE),
('78901234G', 12, 'hermana', NULL, 42, 'Fibromialgia diagnosticada hace 5 años', TRUE),
('78901234G', 7, 'madre', 'materno', 60, 'Artritis reumatoide', TRUE),
-- Paciente 89012345H (David)
('89012345H', 3, 'padre', 'paterno', 62, 'Cardiopatia isquemica', TRUE),
('89012345H', 2, 'padre', 'paterno', 55, 'Hipertension arterial', TRUE),
('89012345H', 17, 'abuelo_materno', 'materno', 70, 'Obesidad morbida', TRUE),
-- Paciente 90123456I (Sofia)
('90123456I', 14, 'hermano', NULL, 15, 'Epilepsia controlada con medicacion', TRUE),
('90123456I', 6, 'hermana', NULL, 10, 'Asma bronquica desde niña', TRUE),
('90123456I', 13, 'madre', 'materno', 40, 'Migranas frecuentes', TRUE),
-- Paciente 01234567J (Miguel)
('01234567J', 5, 'abuelo_paterno', 'paterno', 75, 'Cancer de prostata en etapa avanzada', TRUE),
('01234567J', 1, 'padre', 'paterno', 58, 'Diabetes tipo 2 no controlada', TRUE),
('01234567J', 18, 'madre', 'materno', 50, 'Anemia ferropenica cronica', TRUE);

-- 8. CONSULTAS MEDICAS
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
('12345678A', 1, '2026-02-15 10:30:00', 'Resfriado comun', 'Paracetamol 1g cada 8 horas por 5 dias', 'Paciente presenta sintomas leves. Reposo recomendado.'),
('23456789B', 2, '2026-02-20 11:00:00', 'Control de hipertension', 'Continuar con Enalapril 10mg/dia', 'Tension arterial controlada. Proximo control en 3 meses.'),
('34567890C', 3, '2026-02-25 09:15:00', 'Revision anual', 'Ninguno especifico', 'Estado general bueno. Analisis de sangre solicitado.'),
('45678901D', 4, '2026-03-01 16:00:00', 'Valoracion cardiologica por antecedentes familiares', 'Electrocardiograma. Control anual preventivo.', 'Corazon sano. Sin signos de patologia.'),
('56789012E', 6, '2026-03-02 10:30:00', 'Dolor lumbar cronico', 'Fisioterapia 2 sesiones/semana. Ibuprofeno 600mg si dolor.', 'Derivado desde Medicina General.'),
('67890123F', 1, '2026-03-03 12:00:00', 'Chequeo preventivo', 'Analisis de sangre y orina', 'Paciente joven sin sintomas.'),
('78901234G', 2, '2026-03-04 15:30:00', 'Control de peso', 'Dieta balanceada y ejercicio', 'IMC ligeramente elevado.'),
('89012345H', 3, '2026-03-05 09:00:00', 'Bronquitis aguda', 'Amoxicilina 500mg cada 8 horas', 'Fumador activo, se recomienda dejar el tabaco.'),
('90123456I', 5, '2026-03-06 14:00:00', 'Dermatitis atopica', 'Crema hidratante y corticoides topicos', 'Brote leve, tratamiento durante 2 semanas.'),
('01234567J', 1, '2026-03-07 11:30:00', 'Prediabetes', 'Dieta hipocalorica y ejercicio diario', 'Glucosa en ayunas 115 mg/dl. Control en 3 meses.');

-- 9. RECORDATORIOS
INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES
(1, '2026-02-20 10:30:00', 'Medicacion', 'Finalizar tratamiento con Paracetamol', 'Completado'),
(2, '2026-05-20 11:00:00', 'Control', 'Control de tension arterial - seguimiento hipertension', 'Pendiente'),
(3, '2026-03-10 09:00:00', 'Cita', 'Recoger resultados de analisis de sangre', 'Pendiente'),
(4, '2027-03-01 16:00:00', 'Control', 'Revision cardiologica anual preventiva', 'Pendiente'),
(5, '2026-03-15 10:30:00', 'Cita', 'Sesion de fisioterapia programada', 'Pendiente'),
(6, '2026-06-03 12:00:00', 'Control', 'Revision de analisis preventivos', 'Pendiente'),
(7, '2026-06-04 15:30:00', 'Control', 'Seguimiento de peso e IMC', 'Pendiente'),
(8, '2026-03-12 09:00:00', 'Medicacion', 'Finalizar antibiotico', 'Pendiente'),
(9, '2026-03-20 14:00:00', 'Control', 'Revision de dermatitis', 'Pendiente'),
(10, '2026-06-07 11:30:00', 'Control', 'Control de glucosa en ayunas', 'Pendiente');

-- 10. AUDITORIA
INSERT INTO auditoria_logs (id_admin, accion, tabla_afectada, registro_id, detalles) VALUES
(1, 'CREAR_MEDICO', 'medicos', '1', 'Creacion de medico general: Elena Fernandez Sanchez'),
(1, 'CREAR_MEDICO', 'medicos', '4', 'Creacion de medico especialista Cardiologia: Miguel Rodriguez'),
(1, 'CREAR_MEDICO', 'medicos', '8', 'Creacion de medico especialista Neurologia: Roberto Navarro');

INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles) VALUES
(1, 'CREAR_CONSULTA', 'consultas', '1', 'Nueva consulta con paciente 12345678A - Resfriado comun'),
(4, 'CREAR_CONSULTA', 'consultas', '4', 'Valoracion cardiologica preventiva - paciente 45678901D'),
(2, 'CREAR_CONSULTA', 'consultas', '2', 'Control hipertension paciente 23456789B');

INSERT INTO auditoria_logs (id_paciente, accion, tabla_afectada, registro_id, detalles) VALUES
('12345678A', 'ACTUALIZAR_PERFIL', 'perfiles_salud', '1', 'Actualizacion de datos de perfil de salud'),
('23456789B', 'ACTUALIZAR_PERFIL', 'perfiles_salud', '2', 'Actualizacion de actividad fisica'),
('90123456I', 'CREAR_PERFIL', 'perfiles_salud', '9', 'Creacion de perfil de salud inicial');

-- FIN




