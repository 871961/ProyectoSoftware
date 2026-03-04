-- =============================================================================
-- Script de Limpieza de Base de Datos - MedHistory
-- Descripción: Limpia todas las tablas y reinicia secuencias
-- Fecha: Marzo 2026
-- Autoras: Yousra y Claudia
-- =============================================================================
-- ADVERTENCIA: Este script elimina TODOS los datos de la base de datos
-- Usar con precaución y solo en entornos de desarrollo/prueba
-- =============================================================================

-- Desconectar todas las sesiones activas (excepto la actual)
-- Descomentar si es necesario:
-- SELECT pg_terminate_backend(pg_stat_activity.pid)
-- FROM pg_stat_activity
-- WHERE pg_stat_activity.datname = 'medhistory'
-- AND pid <> pg_backend_pid();

-- Eliminar todas las tablas en orden inverso de dependencias
DROP TABLE IF EXISTS auditoria_logs CASCADE;
DROP TABLE IF EXISTS recordatorios CASCADE;
DROP TABLE IF EXISTS consultas CASCADE;
DROP TABLE IF EXISTS antecedentes_familiares CASCADE;
DROP TABLE IF EXISTS enfermedades_catalogo CASCADE;
DROP TABLE IF EXISTS perfiles_salud CASCADE;
DROP TABLE IF EXISTS pacientes CASCADE;
DROP TABLE IF EXISTS medicos_generales CASCADE;
DROP TABLE IF EXISTS medicos_especialistas CASCADE;
DROP TABLE IF EXISTS medicos CASCADE;
DROP TABLE IF EXISTS administradores CASCADE;

-- Mensaje de confirmación
DO $$
BEGIN
    RAISE NOTICE '=================================================';
    RAISE NOTICE 'Base de datos limpiada correctamente';
    RAISE NOTICE 'Todas las tablas han sido eliminadas';
    RAISE NOTICE '=================================================';
    RAISE NOTICE 'Próximo paso:';
    RAISE NOTICE '1. Ejecutar schema.sql para recrear las tablas';
    RAISE NOTICE '2. Ejecutar datos_prueba.sql para insertar datos';
    RAISE NOTICE 'O ejecutar test_db.txt desde el navegador';
    RAISE NOTICE '=================================================';
END $$;
