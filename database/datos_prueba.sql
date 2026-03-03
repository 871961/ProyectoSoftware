-- Archivo: datos_prueba.sql
-- DescripciÃ³n: Datos de prueba para el sistema mÃ©dico
-- Fecha: Marzo 2026
-- Autoras: Yousra y Claudia

-- =============================================================================
-- IMPORTANTE: Ejecutar SOLO DESPUÃ‰S de haber creado las tablas con schema.sql
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
-- 2. CATÃLOGO DE ENFERMEDADES
-- ========================================
INSERT INTO enfermedades_catalogo (nombre_patologia) VALUES 
('Diabetes Mellitus Tipo 2'),
('HipertensiÃ³n Arterial'),
('CardiopatÃ­a IsquÃ©mica'),
('CÃ¡ncer de Mama'),
('CÃ¡ncer de PrÃ³stata'),
('Asma BrÃ³nquica'),
('Artritis Reumatoide'),
('Osteoporosis'),
('DepresiÃ³n Mayor'),
('Alzheimer'),
('Enfermedad Renal CrÃ³nica'),
('Fibromialgia'),
('MigraÃ±a'),
('Epilepsia'),
('Tiroides (Hipotiroidismo)');

-- ========================================
-- 3. MÃ‰DICOS (Registrados por el admin)
-- ========================================
INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, especialidad) VALUES 
('Elena', 'FernÃ¡ndez SÃ¡nchez', 'elena.fernandez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '612345678', 'Calle Mayor 15, Madrid', 'MD001234', 'Medicina General'),
('Miguel', 'Ãngel RodrÃ­guez', 'miguel.rodriguez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '623456789', 'Avenida de la Paz 32, Madrid', 'CD005678', 'CardiologÃ­a'),
('SofÃ­a', 'LÃ³pez MartÃ­n', 'sofia.lopez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '634567890', 'Plaza de EspaÃ±a 8, Madrid', 'DM009012', 'DermatologÃ­a'),
('Javier', 'GonzÃ¡lez PeÃ±a', 'javier.gonzalez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '645678901', 'Calle Serrano 125, Madrid', 'PD003456', 'PediatrÃ­a'),
('Carmen', 'Jinete MuÃ±oz', 'carmen.jimenez@clinica.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '656789012', 'Gran VÃ­a 44, Madrid', 'GY007890', 'GinecologÃ­a');

-- ========================================
-- 4. PACIENTES
-- ========================================
INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social) VALUES 
('12345678A', 'MarÃ­a', 'PÃ©rez GarcÃ­a', 'maria.perez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '667890123', 'Calle AlcalÃ¡ 73, Madrid', '1985-03-15', '280315123456'),
('87654321B', 'JosÃ©', 'MartÃ­n LÃ³pez', 'jose.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '678901234', 'Paseo del Prado 150, Madrid', '1978-07-22', '220778234567'),
('23456789C', 'Laura', 'Ruiz Herrera', 'laura.ruiz@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '689012345', 'Calle Goya 89, Madrid', '1992-11-08', '081192345678'),
('98765432D', 'Antonio', 'SÃ¡nchez Verde', 'antonio.sanchez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '690123456', 'Avenida AmÃ©rica 200, Madrid', '1965-05-12', '120565456789'),
('34567890E', 'Patricia', 'Moreno Castro', 'patricia.moreno@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '601234567', 'Calle Bravo Murillo 75, Madrid', '1989-09-30', '300989567890');

-- ========================================
-- 5. PERFILES DE SALUD
-- ========================================
INSERT INTO perfiles_salud (id_paciente, peso, altura, alergias, actividad_fisica, consumo_tabaco) VALUES 
('12345678A', 65.5, 1.68, 'Alergia a pen', 'Ejercicio moderado 3 veces por semana', 'No fumador'),
('87654321B', 82.3, 1.75, 'Ninguna conocida', 'Sedentario', 'Ex-fumador (dejÃ³ hace 5 aÃ±os)'),
('23456789C', 58.0, 1.62, 'Alergia a Ã¡caros, polen', 'Running diario', 'No fumador'),
('98765432D', 95.2, 1.80, 'Intolerancia a la lactosa', 'Caminar ocasionalmente', 'Fumador (10 cigarrillos/dÃ­a)'),
('34567890E', 70.8, 1.65, 'Alergia a mariscos', 'Yoga 2 veces por semana', 'No fumador');

-- ========================================
-- 6. ANTECEDENTES FAMILIARES
-- ========================================
INSERT INTO antecedentes_familiares (id_paciente, id_enfermedad, parentesco, observaciones) VALUES 
('12345678A', 1, 'Madre', 'Diagnosticada a los 55 aÃ±os'),
('12345678A', 2, 'Padre', 'HipertensiÃ³n controlada con medicaciÃ³n'),
('87654321B', 3, 'Padre', 'Infarto de miocardio a los 65 aÃ±os'),
('87654321B', 2, 'Madre', 'HipertensiÃ³n desde los 50 aÃ±os'),
('23456789C', 6, 'Hermana', 'Asma desde la infancia'),
('98765432D', 5, 'Abuelo paterno', 'CÃ¡ncer de prÃ³stata a los 70 aÃ±os'),
('34567890E', 4, 'Abuela materna', 'CÃ¡ncer de mama a los 62 aÃ±os');

-- ========================================
-- 7. CONSULTAS (Historial mÃ©dico)
-- ========================================
INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) VALUES 
('12345678A', 1, '2026-02-15 09:30:00', 'RevisiÃ³n general anual', 'Mantener hÃ¡bitos saludables, control analÃ­tico en 6 meses', 'AnalÃ­tica normal, tensiÃ³n arterial 120/80', 'Paciente en buen estado general'),
('12345678A', 3, '2026-01-20 11:15:00', 'Dermatitis atÃ³pica', 'Crema hidratante y corticoide tÃ³pico', 'Mejora significativa tras 2 semanas', 'Evitar tejidos sintÃ©ticos'),
('87654321B', 2, '2026-02-28 16:45:00', 'HipertensiÃ³n arterial leve', 'Dieta hiplosÃ³dica, ejercicio, control en 1 mes', 'TensiÃ³n 145/95, necesita seguimiento', 'Antecedentes familiares de cardiopatÃ­a'),
('23456789C', 4, '2026-02-10 10:00:00', 'RevisiÃ³n ginecolÃ³gica anual', 'Continuar controles anuales', 'ExploraciÃ³n normal, citologÃ­a negativa', 'Sin hallazgos patolÃ³gicos'),
('98765432D', 1, '2026-01-30 14:20:00', 'Bronquitis aguda', 'AntibiÃ³tico, expectorante, reposo', 'Mejora tras 1 semana de tratamiento', 'Relacionado con hÃ¡bito tabÃ¡quico'),
('34567890E', 5, '2026-02-25 12:30:00', 'Control prenatal (semana 20)', 'Suplementos de hierro y Ã¡cido fÃ³lico', 'EcografÃ­a normal, desarrollo fetal adecuado', 'Embarazo de curso normal');

-- ========================================
-- 8. RECORDATORIOS
-- ========================================
INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado) VALUES 
(1, '2026-08-15 09:00:00', 'Control', 'Control analÃ­tico semestral', 'Pendiente'),
(2, '2026-03-28 16:45:00', 'Cita', 'Control tensiÃ³n arterial', 'Pendiente'),
(3, '2027-02-10 10:00:00', 'Control', 'RevisiÃ³n ginecolÃ³gica anual', 'Pendiente'),
(4, '2026-04-30 14:00:00', 'Control', 'Seguimiento bronquitis y cesaciÃ³n tabÃ¡quica', 'Pendiente'),
(5, '2026-04-25 12:30:00', 'Cita', 'Control prenatal (semana 28)', 'Pendiente');

-- ========================================
-- 9. LOGS DE AUDITORÃA (Ejemplos)
-- ========================================
INSERT INTO auditoria_logs (id_admin, accion, tabla_afectada, registro_id, detalles) VALUES 
(1, 'CREAR_MEDICO', 'medicos', 1, '{"accion": "CREAR_MEDICO", "timestamp": "2026-02-01 08:00:00"}'),
(1, 'CREAR_MEDICO', 'medicos', 2, '{"accion": "CREAR_MEDICO", "timestamp": "2026-02-01 08:30:00"}'),
(1, 'CREAR_PACIENTE', 'pacientes', '12345678A', '{"accion": "CREAR_PACIENTE", "timestamp": "2026-02-05 10:15:00"}');

INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles) VALUES 
(1, 'CREAR_CONSULTA', 'consultas', 1, '{"accion": "CREAR_CONSULTA", "timestamp": "2026-02-15 09:35:00"}'),
(2, 'CREAR_CONSULTA', 'consultas', 3, '{"accion": "CREAR_CONSULTA", "timestamp": "2026-02-28 17:00:00"}');

-- =============================================================================
-- CONSULTAS DE VERIFICACIÃ“N (Ejecutar para comprobar que todo estÃ¡ correcto)
-- =============================================================================

-- Verificar pacientes activos
SELECT 'Pacientes Activos:' as verificacion, COUNT(*) as total FROM pacientes WHERE activo = TRUE;

-- Verificar mÃ©dicos activos
SELECT 'MÃ©dicos Activos:' as verificacion, COUNT(*) as total FROM medicos WHERE activo = TRUE;

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
-- ContraseÃ±a: password
--
-- MÃ‰DICOS:
-- Elena FernÃ¡ndez: elena.fernandez@clinica.com / password
-- Miguel RodrÃ­guez: miguel.rodriguez@clinica.com / password
-- etc.
--
-- PACIENTES:
-- MarÃ­a PÃ©rez: maria.perez@email.com / password
-- JosÃ© MartÃ­n: jose.martin@email.com / password
-- etc.
-- =============================================================================
