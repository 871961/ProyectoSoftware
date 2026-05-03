-- =============================================================================
-- SCRIPT MAESTRO: INSERTS MASIVOS
-- Ejecuta todas las fases en orden:
-- 1. Limpieza de BD
-- 2. Administradoras
-- 3. Médicos (50: 15 generales + 10 pediatras + 25 especialistas)
-- =============================================================================

\echo '════════════════════════════════════════════════════════════════'
\echo 'Iniciando carga de datos de prueba...'
\echo '════════════════════════════════════════════════════════════════'

-- Fase 0: LIMPIEZA
\echo ''
\echo '► Fase 0: Limpiando base de datos...'
\i 00_clean_database.sql
\echo '✓ Base de datos limpiada'

-- Fase 1: ADMINISTRADORAS
\echo ''
\echo '► Fase 1: Insertando administradoras (2)...'
\i 01_administradoras.sql
\echo '✓ Administradoras insertadas'

-- Fase 2: MÉDICOS
\echo ''
\echo '► Fase 2: Insertando médicos (50 total)...'
\echo '  - 15 Generales'
\echo '  - 10 Pediatras'
\echo '  - 25 Especialistas (diversas especialidades)'
\i 02_medicos.sql
\echo '✓ Médicos insertados'

-- Fase 3: PACIENTES
\echo ''
\echo '► Fase 3: Insertando pacientes (100 total)...'
\echo '  - 70 Adultos asignados a médicos generales'
\echo '  - 30 Menores (dependientes) asignados a pediatras'
\i 03_pacientes.sql
\echo '✓ Pacientes insertados'

-- Fase 4: CATÁLOGO DE ENFERMEDADES
\echo ''
\echo '► Fase 4: Insertando catálogo de enfermedades (100 patologías)...'
\i 04_enfermedades_catalogo.sql
\echo '✓ Catálogo de enfermedades insertado'

-- Fase 5: PERFILES DE SALUD
\echo ''
\echo '► Fase 5: Insertando perfiles de salud detallados (100 pacientes)...'
\echo '  - 70 Adultos con antecedentes y hábitos variados'
\echo '  - 30 Menores con perfiles pediátricos'
\i 05_perfiles_salud.sql
\echo '✓ Perfiles de salud insertados'

\echo ''
\echo '════════════════════════════════════════════════════════════════'
\echo 'DATOS DE ACCESO'
\echo '════════════════════════════════════════════════════════════════'
\echo ''
\echo 'ADMINISTRADORAS (2):'
\echo '  • claudia@clinica.com / test123'
\echo '  • yousra@clinica.com / test123'
\echo ''
\echo 'MÉDICOS (50):'
\echo '  • nombre.apellido@clinica.com / test123'
\echo '  • Números de colegiado: CMED001 - CMED050'
\echo ''
\echo 'PACIENTES (100):'
\echo '  • nombre.apellido@gmail.com / test123'
\echo '  • DNIs: 12345678A - 30303030DD (adultos)'
\echo '  • DNIs: 11111111A - 30303030DD (menores)'
\echo ''
\echo '════════════════════════════════════════════════════════════════'
\echo 'Carga completada ✓'
\echo '════════════════════════════════════════════════════════════════'
