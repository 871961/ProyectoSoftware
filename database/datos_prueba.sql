-- Datos de Prueba - MedHistory
-- Ejecutar despues de reinstalar_completo.sql o schema.sql

-- 1. ADMINISTR ADORES
INSERT INTO administradores (nombre, apellidos, email, contrasena_hash, telefono) VALUES
('Claudia', 'Mateo', 'claudia.mateo@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '600000001');

-- 2. MEDICOS GENERALES
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico) VALUES
('Elena', 'Fernandez Sanchez', 'elena.fernandez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '612-345-678', 'MG-001234', 'general'),
('Carlos', 'Lopez Jimenez', 'carlos.lopez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '613-456-789', 'MG-002345', 'general'),
('Ana', 'Martinez Ruiz', 'ana.martinez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '614-567-890', 'MG-003456', 'general');

INSERT INTO medicos_generales (id_medico, pacientes_asignados) VALUES
(1, 0), (2, 0), (3, 0);

-- 3. MEDICOS ESPECIALISTAS
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, num_colegiado, tipo_medico) VALUES
('Miguel', 'Rodriguez', 'miguel.rodriguez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '623-456-789', 'CD-005678', 'especialista'),
('Laura', 'Garcia Santos', 'laura.garcia@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '624-567-890', 'DM-006789', 'especialista'),
('Pedro', 'Sanchez Moreno', 'pedro.sanchez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '625-678-901', 'TR-007890', 'especialista'),
('Isabel', 'Diaz Fernandez', 'isabel.diaz@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '626-789-012', 'GN-008901', 'especialista');

INSERT INTO medicos_especialistas (id_medico, especialidad) VALUES
(4, 'Cardiologia'),
(5, 'Dermatologia'),
(6, 'Traumatologia'),
(7, 'Ginecologia');

-- 4. PACIENTES
INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general) VALUES
('12345678A', 'Maria', 'Perez Garcia', 'maria.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '667-890-123', 'Calle Alcala 73, Madrid', '1985-03-15', '280315123456', 1),
('23456789B', 'Jose', 'Martin Lopez', 'jose.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '678-901-234', 'Paseo del Prado 150, Madrid', '1978-07-22', '220778234567', 2),
('34567890C', 'Laura', 'Ruiz Herrera', 'laura.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '689-012-345', 'Calle Goya 89, Madrid', '1992-11-08', '081192345678', 3),
('45678901D', 'Antonio', 'Sanchez Verde', 'antonio.sanchez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '690-123-456', 'Avenida America 200, Madrid', '1965-05-12', '120565456789', 1),
('56789012E', 'Patricia', 'Moreno Castro', 'patricia.moreno@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '601-234-567', 'Calle Bravo Murillo 75, Madrid', '1989-09-30', '300989567890', 2);

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
('Tiroides Hipotiroidismo');

-- 6. PERFILES DE SALUD
INSERT INTO perfiles_salud (id_paciente, peso, altura, alergias, actividad_fisica, consumo_tabaco) VALUES 
('12345678A', 65.5, 1.68, 'Alergia a penicilina', 'Ejercicio moderado 3 veces por semana', 'No fumador'),
('23456789B', 82.3, 1.75, 'Ninguna conocida', 'Sedentario', 'Ex-fumador hace 5 anos'),
('34567890C', 58.0, 1.62, 'Alergia a acaros, polen', 'Running diario', 'No fumador'),
('45678901D', 78.0, 1.80, 'Alergia a ibuprofeno', 'Caminar 30 min diarios', 'No fumador'),
('56789012E', 62.0, 1.65, 'Ninguna conocida', 'Ejercicio moderado', 'No fumador');

-- 7. ANTECEDENTES FAMILIARES
INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, observaciones) VALUES
('12345678A', 1, 'Padre', 'Diagnosticado a los 55 anos'),
('12345678A', 2, 'Madre', 'En tratamiento desde hace 10 anos'),
('23456789B', 3, 'Padre', 'Infarto a los 60 anos'),
('34567890C', 6, 'Hermano', 'Asma desde la infancia'),
('45678901D', 10, 'Abuela paterna', 'Diagnosticada a los 70 anos');

-- 8. CONSULTAS MEDICAS
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, observaciones) VALUES
('12345678A', 1, '2026-02-15 10:30:00', 'Resfriado comun', 'Paracetamol 1g cada 8 horas por 5 dias', 'Paciente presenta sintomas leves. Reposo recomendado.'),
('23456789B', 2, '2026-02-20 11:00:00', 'Control de hipertension', 'Continuar con Enalapril 10mg/dia', 'Tension arterial controlada. Proximo control en 3 meses.'),
('34567890C', 3, '2026-02-25 09:15:00', 'Revision anual', 'Ninguno especifico', 'Estado general bueno. Analisis de sangre solicitado.'),
('12345678A', 4, '2026-03-01 16:00:00', 'Valoracion cardiologica por antecedentes familiares', 'Electrocardiograma. Control anual preventivo.', 'Corazon sano. Sin signos de patologia.'),
('23456789B', 6, '2026-03-02 10:30:00', 'Dolor lumbar cronico', 'Fisioterapia 2 sesiones/semana. Ibuprofeno 600mg si dolor.', 'Derivado desde Medicina General. Radiografia sin alteraciones oseas.');

-- 9. RECORDATORIOS
INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES
(1, '2026-02-20 10:30:00', 'Medicacion', 'Finalizar tratamiento con Paracetamol', 'Completado'),
(2, '2026-05-20 11:00:00', 'Control', 'Control de tension arterial - seguimiento hipertension', 'Pendiente'),
(3, '2026-03-10 09:00:00', 'Cita', 'Recoger resultados de analisis de sangre', 'Pendiente'),
(4, '2027-03-01 16:00:00', 'Control', 'Revision cardiologica anual preventiva', 'Pendiente'),
(5, '2026-03-09 10:30:00', 'Cita', 'Segunda sesion de fisioterapia', 'Pendiente');

-- 10. AUDITORIA
INSERT INTO auditoria_logs (id_admin, accion, tabla_afectada, registro_id, detalles) VALUES
(1, 'CREAR_MEDICO', 'medicos', '1', 'Creacion de medico general: Elena Fernandez Sanchez'),
(2, 'CREAR_MEDICO', 'medicos', '4', 'Creacion de medico especialista Cardiologia: Miguel Rodriguez');

INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles) VALUES
(1, 'CREAR_CONSULTA', 'consultas', '1', 'Nueva consulta con paciente 12345678A - Resfriado comun'),
(4, 'CREAR_CONSULTA', 'consultas', '4', 'Valoracion cardiologica preventiva - paciente 12345678A');

INSERT INTO auditoria_logs (id_paciente, accion, tabla_afectada, registro_id, detalles) VALUES
('12345678A', 'ACTUALIZAR_PERFIL', 'perfiles_salud', '1', 'Actualizacion de datos de perfil de salud');

-- FIN
