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
DROP TABLE IF EXISTS chat_mensajes CASCADE;
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

-- =============================================================================
-- TABLAS PRINCIPALES (con borrado lógico)
-- =============================================================================

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

-- Tabla de Médicos (Entidad Padre)
CREATE TABLE medicos (
    id_medico SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    num_colegiado VARCHAR(50) UNIQUE NOT NULL,
    -- Tipo de médico: 'general' o 'especialista'
    tipo_medico VARCHAR(20) NOT NULL CHECK (tipo_medico IN ('general', 'especialista')),
    -- BORRADO LÓGICO
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- Tabla de Médicos Generales (Médicos de Cabecera)
-- Especialización de la tabla medicos
CREATE TABLE medicos_generales (
    id_medico INT PRIMARY KEY,
    -- Información adicional específica de médicos generales
    pacientes_asignados INT DEFAULT 0,
    CONSTRAINT fk_medico_general FOREIGN KEY (id_medico) 
        REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Médicos Especialistas
-- Especialización de la tabla medicos  
CREATE TABLE medicos_especialistas (
    id_medico INT PRIMARY KEY,
    especialidad VARCHAR(100) NOT NULL, -- Cardiología, Dermatología, etc.
    -- Información adicional específica de especialistas
    CONSTRAINT fk_medico_especialista FOREIGN KEY (id_medico) 
        REFERENCES medicos(id_medico) ON DELETE CASCADE
);

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
    num_seguridad_social VARCHAR(20),
    -- Médico General asignado (médico de cabecera)
    id_medico_general INT,
    -- BORRADO LÓGICO
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP,
    -- FK al médico general asignado
    CONSTRAINT fk_paciente_medico_general FOREIGN KEY (id_medico_general) 
        REFERENCES medicos_generales(id_medico)
);

-- Índice único parcial: solo valida unicidad cuando el valor NO es NULL
CREATE UNIQUE INDEX idx_pacientes_num_ss_unique 
ON pacientes (num_seguridad_social) 
WHERE num_seguridad_social IS NOT NULL;

-- =============================================================================
-- TABLAS DE HISTORIAL CLÍNICO (sin ON DELETE CASCADE)
-- =============================================================================

-- Perfiles de Salud de Pacientes
CREATE TABLE perfiles_salud (
    id_perfil SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) UNIQUE NOT NULL,
    peso NUMERIC(5,2),
    altura NUMERIC(3,2),
    peso_kg NUMERIC(5,2),
    altura_cm NUMERIC(5,2),
    alergias TEXT,
    enfermedades TEXT,
    actividad_fisica VARCHAR(100),
    consumo_tabaco VARCHAR(100),
    consumo_alcohol VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    parentesco VARCHAR(50) NOT NULL, -- padre, madre, hermano, abuelo_paterno, etc.
    lado_familiar VARCHAR(20), -- paterno, materno, ambos
    edad_diagnostico INT, -- edad en que el familiar fue diagnosticado
    notas_adicionales TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
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

-- Chat seguro medico-medico (contenido cifrado en reposo)
CREATE TABLE chat_mensajes (
    id_mensaje SERIAL PRIMARY KEY,
    id_emisor INT NOT NULL,
    id_receptor INT NOT NULL,
    mensaje_cifrado TEXT NOT NULL,
    nonce VARCHAR(64) NOT NULL,
    tag VARCHAR(64) NOT NULL,
    algoritmo VARCHAR(32) NOT NULL DEFAULT 'aes-256-gcm',
    tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'texto',
    nombre_archivo VARCHAR(255),
    ruta_archivo TEXT,
    tamano_bytes INT,
    enviado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    leido_en TIMESTAMP,
    eliminado_por_emisor BOOLEAN DEFAULT FALSE,
    eliminado_por_receptor BOOLEAN DEFAULT FALSE,
    CONSTRAINT fk_chat_emisor FOREIGN KEY (id_emisor) REFERENCES medicos(id_medico),
    CONSTRAINT fk_chat_receptor FOREIGN KEY (id_receptor) REFERENCES medicos(id_medico),
    CONSTRAINT chk_chat_distinto_autor CHECK (id_emisor <> id_receptor),
    CONSTRAINT chk_chat_tipo_contenido CHECK (tipo_contenido IN ('texto', 'archivo'))
);

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
CREATE INDEX idx_pacientes_medico_general ON pacientes(id_medico_general);
CREATE INDEX idx_medicos_email ON medicos(email);
CREATE INDEX idx_medicos_activo ON medicos(activo);
CREATE INDEX idx_medicos_tipo ON medicos(tipo_medico);
CREATE INDEX idx_medicos_especialistas_especialidad ON medicos_especialistas(especialidad);
CREATE INDEX idx_administradores_email ON administradores(email);
CREATE INDEX idx_consultas_paciente ON consultas(id_paciente);
CREATE INDEX idx_consultas_medico ON consultas(id_medico);
CREATE INDEX idx_consultas_fecha ON consultas(fecha);
CREATE INDEX idx_chat_emisor_receptor_fecha ON chat_mensajes(id_emisor, id_receptor, enviado_en DESC);
CREATE INDEX idx_chat_receptor_leido ON chat_mensajes(id_receptor, leido_en);
CREATE INDEX idx_recordatorios_fecha ON recordatorios(fecha_hora);
CREATE INDEX idx_auditoria_fecha ON auditoria_logs(fecha_hora);

-- =============================================================================
-- COMENTARIOS EN LAS TABLAS (Documentación)
-- =============================================================================

COMMENT ON TABLE pacientes IS 'Tabla de pacientes con borrado lógico (GDPR/LOPD)';
COMMENT ON TABLE medicos IS 'Tabla de médicos (entidad padre) con borrado lógico';
COMMENT ON TABLE medicos_generales IS 'Médicos de cabecera asignados a pacientes';
COMMENT ON TABLE medicos_especialistas IS 'Médicos especialistas (cardiología, dermatología, etc.)';
COMMENT ON TABLE administradores IS 'Tabla de administradores del sistema';
COMMENT ON TABLE perfiles_salud IS 'Información detallada de salud de cada paciente';
COMMENT ON TABLE enfermedades_catalogo IS 'Catálogo de enfermedades para antecedentes familiares';
COMMENT ON TABLE antecedentes_familiares IS 'Historial familiar de enfermedades de cada paciente';
COMMENT ON TABLE consultas IS 'Registro de todas las consultas médicas realizadas';
COMMENT ON TABLE chat_mensajes IS 'Mensajeria cifrada medico-medico para coordinacion asistencial';
COMMENT ON TABLE recordatorios IS 'Recordatorios asociados a consultas (medicación, citas, controles)';
COMMENT ON TABLE auditoria_logs IS 'Log de auditoría para trazabilidad y cumplimiento legal';

-- =============================================================================
-- FIN DEL SCHEMA
-- =============================================================================
-- Para insertar datos de prueba, ejecutar: database/datos_prueba.sql
-- =============================================================================
