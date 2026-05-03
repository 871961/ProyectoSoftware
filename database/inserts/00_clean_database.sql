-- =============================================================================
-- SCRIPT DE LIMPIEZA DE BASE DE DATOS
-- Elimina todos los datos (TRUNCATE) para empezar desde cero
-- =============================================================================

-- Deshabilitar verificación de claves foráneas
SET session_replication_role = 'replica';

-- Truncate de todas las tablas en orden inverso a las dependencias
TRUNCATE TABLE auditoria_logs CASCADE;
TRUNCATE TABLE recordatorios CASCADE;
TRUNCATE TABLE cartilla_vacunas CASCADE;
TRUNCATE TABLE chat_mensajes CASCADE;
TRUNCATE TABLE consultas CASCADE;
TRUNCATE TABLE antecedentes_familiares CASCADE;
TRUNCATE TABLE enfermedades_catalogo CASCADE;
TRUNCATE TABLE perfiles_salud CASCADE;
TRUNCATE TABLE pacientes CASCADE;
TRUNCATE TABLE medicos_especialistas CASCADE;
TRUNCATE TABLE medicos_generales CASCADE;
TRUNCATE TABLE medicos CASCADE;
TRUNCATE TABLE administradores CASCADE;

-- Reabilitar verificación de claves foráneas
SET session_replication_role = 'default';

-- Resetear secuencias (auto-increment)
ALTER SEQUENCE administradores_id_admin_seq RESTART WITH 1;
ALTER SEQUENCE medicos_id_medico_seq RESTART WITH 1;
ALTER SEQUENCE consultas_id_consulta_seq RESTART WITH 1;
ALTER SEQUENCE antecedentes_familiares_id_seq RESTART WITH 1;
ALTER SEQUENCE enfermedades_catalogo_id_seq RESTART WITH 1;
ALTER SEQUENCE recordatorios_id_seq RESTART WITH 1;
ALTER SEQUENCE auditoria_logs_id_seq RESTART WITH 1;
ALTER SEQUENCE cartilla_vacunas_id_seq RESTART WITH 1;
ALTER SEQUENCE chat_mensajes_id_seq RESTART WITH 1;

COMMIT;
