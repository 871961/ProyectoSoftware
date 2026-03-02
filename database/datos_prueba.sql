-- Archivo: datos_prueba.sql
-- Descripción: Datos de prueba para el sistema médico
-- Fecha: Marzo 2026
-- Autoras: Yousra y Claudia

-- =============================================================================
-- IMPORTANTE: Ejecutar SOLO DESPUÉS de haber creado las tablas con schema.sql
-- =============================================================================

-- Limpiar datos existentes (opcional)
-- DELETE FROM auditoria_logs;
-- DELETE FROM recordatorios;
-- DELETE FROM consultas;
-- DELETE FROM antecedentes_familiares;
-- DELETE FROM perfiles_salud;
-- DELETE FROM pacientes;
-- DELETE FROM medicos;
-- DELETE FROM administradores;
-- DELETE FROM enfermedades_catalogo;

-- ========================================
-- 1. ADMINISTRADORES (Para el panel admin)
-- ========================================
INSERT INTO administradores (nombre, apellidos, email, contrasena_hash) VALUES 
('Claudia', 'Mateo', 'claudia.mateo@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'), -- password: password
('Yousra', 'Jebari', 'yousra.jebari@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

-- ========================================
-- 2. CATÁLOGO DE ENFERMEDADES
-- ========================================
INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES 
('Diabetes Mellitus Tipo 2'),
('Hipertensión Arterial'),
('Cardiopatía Isquémica'),
('Cáncer de Mama'),
('Cáncer de Próstata'),
('Asma Brónquica'),
('Artritis Reumatoide'),
('Osteoporosis'),
('Depresión Mayor'),
('Alzheimer'),
('Enfermedad Renal Crónica'),
('Fibromialgia'),
('Migraña'),
('Epilepsia'),
('Tiroides (Hipotiroidismo)');

-- ========================================
-- 3. MÉDICOS (Registrados por el admin)
-- ========================================
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, especialidad) VALUES 
('Elena', 'Fernández Sánchez', 'elena.fernandez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '612345678', 'Calle Mayor 15, Madrid', 'MD001234', 'Medicina General'),
('Miguel', 'Ángel Rodríguez', 'miguel.rodriguez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '623456789', 'Avenida de la Paz 32, Madrid', 'CD005678', 'Cardiología'),
('Sofía', 'López Martín', 'sofia.lopez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '634567890', 'Plaza de España 8, Madrid', 'DM009012', 'Dermatología'),
('Javier', 'González Peña', 'javier.gonzalez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '645678901', 'Calle Serrano 125, Madrid', 'PD003456', 'Pediatría'),
('Carmen', 'Jinete Muñoz', 'carmen.jimenez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '656789012', 'Gran Vía 44, Madrid', 'GY007890', 'Ginecología');

-- ========================================
-- 4. PACIENTES
-- ========================================
INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social) VALUES 
('12345678A', 'María', 'Pérez García', 'maria.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '667890123', 'Calle Alcalá 73, Madrid', '1985-03-15', '280315123456'),
('87654321B', 'José', 'Martín López', 'jose.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '678901234', 'Paseo del Prado 150, Madrid', '1978-07-22', '220778234567'),
('23456789C', 'Laura', 'Ruiz Herrera', 'laura.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '689012345', 'Calle Goya 89, Madrid', '1992-11-08', '081192345678'),
('98765432D', 'Antonio', 'Sánchez Verde', 'antonio.sanchez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '690123456', 'Avenida América 200, Madrid', '1965-05-12', '120565456789'),
('34567890E', 'Patricia', 'Moreno Castro', 'patricia.moreno@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '601234567', 'Calle Bravo Murillo 75, Madrid', '1989-09-30', '300989567890');

-- ========================================
-- 5. PERFILES DE SALUD
-- ========================================
INSERT INTO perfiles_salud (id_paciente, peso, altura, alergias, actividad_fisica, consumo_tabaco) VALUES 
('12345678A', 65.5, 1.68, 'Alergia a pen', 'Ejercicio moderado 3 veces por semana', 'No fumador'),
('87654321B', 82.3, 1.75, 'Ninguna conocida', 'Sedentario', 'Ex-fumador (dejó hace 5 años)'),
('23456789C', 58.0, 1.62, 'Alergia a ácaros, polen', 'Running diario', 'No fumador'),
('98765432D', 95.2, 1.80, 'Intolerancia a la lactosa', 'Caminar ocasionalmente', 'Fumador (10 cigarrillos/día)'),
('34567890E', 70.8, 1.65, 'Alergia a mariscos', 'Yoga 2 veces por semana', 'No fumador');

-- ========================================
-- 6. ANTECEDENTES FAMILIARES
-- ========================================
INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, observaciones) VALUES 
('12345678A', 1, 'Madre', 'Diagnosticada a los 55 años'),
('12345678A', 2, 'Padre', 'Hipertensión controlada con medicación'),
('87654321B', 3, 'Padre', 'Infarto de miocardio a los 65 años'),
('87654321B', 2, 'Madre', 'Hipertensión desde los 50 años'),
('23456789C', 6, 'Hermana', 'Asma desde la infancia'),
('98765432D', 5, 'Abuelo paterno', 'Cáncer de próstata a los 70 años'),
('34567890E', 4, 'Abuela materna', 'Cáncer de mama a los 62 años');

-- ========================================
-- 7. CONSULTAS (Historial médico)
-- ========================================
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES 
('12345678A', 1, '2026-02-15 09:30:00', 'Revisión general anual', 'Mantener hábitos saludables, control analítico en 6 meses', 'Analítica normal, tensión arterial 120/80', 'Paciente en buen estado general'),
('12345678A', 3, '2026-01-20 11:15:00', 'Dermatitis atópica', 'Crema hidratante y corticoide tópico', 'Mejora significativa tras 2 semanas', 'Evitar tejidos sintéticos'),
('87654321B', 2, '2026-02-28 16:45:00', 'Hipertensión arterial leve', 'Dieta hiplosódica, ejercicio, control en 1 mes', 'Tensión 145/95, necesita seguimiento', 'Antecedentes familiares de cardiopatía'),
('23456789C', 4, '2026-02-10 10:00:00', 'Revisión ginecológica anual', 'Continuar controles anuales', 'Exploración normal, citología negativa', 'Sin hallazgos patológicos'),
('98765432D', 1, '2026-01-30 14:20:00', 'Bronquitis aguda', 'Antibiótico, expectorante, reposo', 'Mejora tras 1 semana de tratamiento', 'Relacionado con hábito tabáquico'),
('34567890E', 5, '2026-02-25 12:30:00', 'Control prenatal (semana 20)', 'Suplementos de hierro y ácido fólico', 'Ecografía normal, desarrollo fetal adecuado', 'Embarazo de curso normal');

-- ========================================
-- 8. RECORDATORIOS
-- ========================================
INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES 
(1, '2026-08-15 09:00:00', 'Control', 'Control analítico semestral', 'Pendiente'),
(2, '2026-03-28 16:45:00', 'Cita', 'Control tensión arterial', 'Pendiente'),
(3, '2027-02-10 10:00:00', 'Control', 'Revisión ginecológica anual', 'Pendiente'),
(4, '2026-04-30 14:00:00', 'Control', 'Seguimiento bronquitis y cesación tabáquica', 'Pendiente'),
(5, '2026-04-25 12:30:00', 'Cita', 'Control prenatal (semana 28)', 'Pendiente');

-- ========================================
-- 9. LOGS DE AUDITORÍA (Ejemplos)
-- ========================================
INSERT INTO auditoria_logs (id_admin, accion, tabla_afectada, registro_id, detalles) VALUES 
(1, 'CREAR_MEDICO', 'medicos', 1, '{"accion": "CREAR_MEDICO", "timestamp": "2026-02-01 08:00:00"}'),
(1, 'CREAR_MEDICO', 'medicos', 2, '{"accion": "CREAR_MEDICO", "timestamp": "2026-02-01 08:30:00"}'),
(1, 'CREAR_PACIENTE', 'pacientes', '12345678A', '{"accion": "CREAR_PACIENTE", "timestamp": "2026-02-05 10:15:00"}');

INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles) VALUES 
(1, 'CREAR_CONSULTA', 'consultas', 1, '{"accion": "CREAR_CONSULTA", "timestamp": "2026-02-15 09:35:00"}'),
(2, 'CREAR_CONSULTA', 'consultas', 3, '{"accion": "CREAR_CONSULTA", "timestamp": "2026-02-28 17:00:00"}');

-- =============================================================================
-- CONSULTAS DE VERIFICACIÓN (Ejecutar para comprobar que todo está correcto)
-- =============================================================================

-- Verificar pacientes activos
SELECT 'Pacientes Activos:' as verificacion, COUNT(*) as total FROM pacientes WHERE activo = TRUE;

-- Verificar médicos activos
SELECT 'Médicos Activos:' as verificacion, COUNT(*) as total FROM medicos WHERE activo = TRUE;

-- Verificar consultas registradas
SELECT 'Consultas Registradas:' as verificacion, COUNT(*) as total FROM consultas;

-- Verificar especialidades disponibles
SELECT 'Especialidades Disponibles:' as info, especialidad, COUNT(*) as num_medicos 
FROM medicos 
WHERE activo = TRUE 
GROUP BY especialidad 
ORDER BY especialidad;

-- Verificar administradores
SELECT 'Administradores:' as verificacion, nombre, apellidos, email FROM administradores WHERE activo = TRUE;

-- =============================================================================
-- CREDENCIALES DE ACCESO PARA PRUEBAS:
-- =============================================================================
-- 
-- PANEL DE ADMINISTRADOR:
-- Email: admin@clinica.com
-- Contraseña: password
--
-- MÉDICOS:
-- Elena Fernández: elena.fernandez@clinica.com / password
-- Miguel Rodríguez: miguel.rodriguez@clinica.com / password
-- etc.
--
-- PACIENTES:
-- María Pérez: maria.perez@email.com / password
-- José Martín: jose.martin@email.com / password
-- etc.
-- =============================================================================