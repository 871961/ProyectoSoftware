-- =============================================================================
-- MedHistory Database Schema
-- Sistema de Gestión de Historial Clínico
-- Fecha: Marzo 2026
-- Autoras: Yousra y Claudia
-- =============================================================================
-- IMPORTANTE: Este esquema implementa borrado lógico (GDPR/LOPD compliant)
-- Las tablas principales usan columnas 'activo' y 'fecha_baja'
-- Las FKs NO usan ON DELETE CASCADE para evitar pérdida de datos históricos
-- =============================================================================

-- Limpieza (opcional - comentar si no quieres borrar datos existentes)
DROP TABLE IF EXISTS auditoria_logs CASCADE;
DROP TABLE IF EXISTS recordatorios CASCADE;
DROP TABLE IF EXISTS consultas CASCADE;
DROP TABLE IF EXISTS antecedentes_familiares CASCADE;
DROP TABLE IF EXISTS enfermedades_catalogo CASCADE;
DROP TABLE IF EXISTS perfiles_salud CASCADE;
DROP TABLE IF EXISTS pacientes CASCADE;
DROP TABLE IF EXISTS medicos CASCADE;
DROP TABLE IF EXISTS administradores CASCADE;

-- =============================================================================
-- TABLAS PRINCIPALES (con borrado lógico)
-- =============================================================================

-- Tabla de Pacientes
CREATE TABLE pacientes (
    dni VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    fecha_nacimiento DATE NOT NULL,
    num_seguridad_social VARCHAR(20) UNIQUE,
    -- BORRADO LÓGICO
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- Tabla de Médicos
CREATE TABLE medicos (
    id_medico SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    num_colegiado VARCHAR(50) UNIQUE NOT NULL,
    especialidad VARCHAR(100) NOT NULL,
    -- BORRADO LÓGICO
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- Tabla de Administradores
CREATE TABLE administradores (
    id_admin SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    -- BORRADO LÓGICO
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- =============================================================================
-- TABLAS DE HISTORIAL CLÍNICO (sin ON DELETE CASCADE)
-- =============================================================================

-- Perfiles de Salud de Pacientes
CREATE TABLE perfiles_salud (
    id_perfil SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) UNIQUE NOT NULL,
    peso NUMERIC(5,2),
    altura NUMERIC(3,2),
    alergias TEXT,
    actividad_fisica VARCHAR(100),
    consumo_tabaco VARCHAR(100),
    -- Sin CASCADE: Si intentas borrar un paciente físicamente, SQL dará error
    CONSTRAINT fk_paciente_perfil FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni) 
);

-- Catálogo de Enfermedades para Antecedentes Familiares
CREATE TABLE enfermedades_catalogo (
    id_enfermedad SERIAL PRIMARY KEY,
    nombre_patologia VARCHAR(150) UNIQUE NOT NULL
);

-- Antecedentes Familiares de Pacientes
CREATE TABLE antecedentes_familiares (
    id_antecedente SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    id_enfermedad INT NOT NULL,
    parentesco VARCHAR(50) NOT NULL,
    observaciones TEXT,
    CONSTRAINT fk_paciente_antecedente FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni),
    CONSTRAINT fk_enfermedad_antecedente FOREIGN KEY (id_enfermedad) 
        REFERENCES enfermedades_catalogo(id_enfermedad)
);

-- Consultas Médicas
CREATE TABLE consultas (
    id_consulta SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    id_medico INT NOT NULL,
    fecha TIMESTAMP NOT NULL,
    diagnostico TEXT,
    tratamiento TEXT,
    resultados TEXT,
    observaciones TEXT,
    CONSTRAINT fk_paciente_consulta FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni),
    CONSTRAINT fk_medico_consulta FOREIGN KEY (id_medico) 
        REFERENCES medicos(id_medico)
);

-- =============================================================================
-- RECORDATORIOS Y AUDITORÍA
-- =============================================================================

-- Recordatorios asociados a consultas
CREATE TABLE recordatorios (
    id_recordatorio SERIAL PRIMARY KEY,
    id_consulta INT NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    -- CLASIFICACIÓN DE RECORDATORIOS
    tipo_recordatorio VARCHAR(50) CHECK (tipo_recordatorio IN ('Medicación', 'Control', 'Cita', 'Otro')) DEFAULT 'Otro',
    razon VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Completado')),
    CONSTRAINT fk_consulta_recordatorio FOREIGN KEY (id_consulta) 
        REFERENCES consultas(id_consulta)
);

-- Auditoría y Logs (adaptada a las tablas divididas)
CREATE TABLE auditoria_logs (
    id_log SERIAL PRIMARY KEY,
    -- Referencias opcionales a las distintas tablas de usuarios
    id_paciente VARCHAR(20),
    id_medico INT,
    id_admin INT,
    
    accion VARCHAR(50) NOT NULL, -- Ej: 'CREAR_CONSULTA', 'BAJA_PACIENTE'
    tabla_afectada VARCHAR(50) NOT NULL, -- Ej: 'consultas', 'pacientes'
    registro_id VARCHAR(50), -- ID de la fila que fue modificada/creada (puede ser INT o VARCHAR)
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalles TEXT, -- Ideal para guardar un JSON con los cambios realizados
    
    -- Claves foráneas
    CONSTRAINT fk_auditoria_paciente FOREIGN KEY (id_paciente) REFERENCES pacientes(dni),
    CONSTRAINT fk_auditoria_medico FOREIGN KEY (id_medico) REFERENCES medicos(id_medico),
    CONSTRAINT fk_auditoria_admin FOREIGN KEY (id_admin) REFERENCES administradores(id_admin),
    
    -- Restricción: Solo debe haber un tipo de usuario responsable de la acción
    CONSTRAINT chk_un_solo_autor CHECK (
        (id_paciente IS NOT NULL)::INT + 
        (id_medico IS NOT NULL)::INT + 
        (id_admin IS NOT NULL)::INT = 1
    )
);

-- =============================================================================
-- ÍNDICES PARA MEJORAR EL RENDIMIENTO
-- =============================================================================

CREATE INDEX idx_pacientes_email ON pacientes(email);
CREATE INDEX idx_pacientes_activo ON pacientes(activo);
CREATE INDEX idx_medicos_email ON medicos(email);
CREATE INDEX idx_medicos_activo ON medicos(activo);
CREATE INDEX idx_medicos_especialidad ON medicos(especialidad);
CREATE INDEX idx_administradores_email ON administradores(email);
CREATE INDEX idx_consultas_paciente ON consultas(id_paciente);
CREATE INDEX idx_consultas_medico ON consultas(id_medico);
CREATE INDEX idx_consultas_fecha ON consultas(fecha);
CREATE INDEX idx_recordatorios_fecha ON recordatorios(fecha_hora);
CREATE INDEX idx_auditoria_fecha ON auditoria_logs(fecha_hora);

-- =============================================================================
-- COMENTARIOS EN LAS TABLAS (Documentación)
-- =============================================================================

COMMENT ON TABLE pacientes IS 'Tabla de pacientes con borrado lógico (GDPR/LOPD)';
COMMENT ON TABLE medicos IS 'Tabla de médicos con borrado lógico';
COMMENT ON TABLE administradores IS 'Tabla de administradores del sistema';
COMMENT ON TABLE perfiles_salud IS 'Información detallada de salud de cada paciente';
COMMENT ON TABLE enfermedades_catalogo IS 'Catálogo de enfermedades para antecedentes familiares';
COMMENT ON TABLE antecedentes_familiares IS 'Historial familiar de enfermedades de cada paciente';
COMMENT ON TABLE consultas IS 'Registro de todas las consultas médicas realizadas';
COMMENT ON TABLE recordatorios IS 'Recordatorios asociados a consultas (medicación, citas, controles)';
COMMENT ON TABLE auditoria_logs IS 'Log de auditoría para trazabilidad y cumplimiento legal';

-- =============================================================================
-- FIN DEL SCHEMA
-- =============================================================================
-- Para insertar datos de prueba, ejecutar: database/datos_prueba.sql
-- =============================================================================