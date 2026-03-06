-- =============================================================================
-- Script COMPLETO de Reinstalacion - Ejecutar desde pgAdmin
-- =============================================================================
-- PASO 1: ELIMINAR TODAS LAS TABLAS
-- =============================================================================

DROP TABLE IF EXISTS auditoria_logs CASCADE;
DROP TABLE IF EXISTS recordatorios CASCADE;
DROP TABLE IF EXISTS consultas CASCADE;
DROP TABLE IF EXISTS antecedentes_familiares CASCADE;
DROP TABLE IF EXISTS perfiles_salud CASCADE;
DROP TABLE IF EXISTS pacientes CASCADE;
DROP TABLE IF EXISTS enfermedades_catalogo CASCADE;
DROP TABLE IF EXISTS medicos_especialistas CASCADE;
DROP TABLE IF EXISTS medicos_generales CASCADE;
DROP TABLE IF EXISTS medicos CASCADE;
DROP TABLE IF EXISTS administradores CASCADE;

-- =============================================================================
-- PASO 2: CREAR TODAS LAS TABLAS
-- =============================================================================

-- Tabla de Administradores
CREATE TABLE administradores (
    id_admin SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- Tabla de Medicos (Entidad Padre)
CREATE TABLE medicos (
    id_medico SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    contrasena_hash VARCHAR(256) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    num_colegiado VARCHAR(50) UNIQUE NOT NULL,
    tipo_medico VARCHAR(20) NOT NULL CHECK (tipo_medico IN ('general', 'especialista')),
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP
);

-- Tabla de Medicos Generales
CREATE TABLE medicos_generales (
    id_medico INT PRIMARY KEY,
    pacientes_asignados INT DEFAULT 0,
    CONSTRAINT fk_medico_general FOREIGN KEY (id_medico) 
        REFERENCES medicos(id_medico) ON DELETE CASCADE
);

-- Tabla de Medicos Especialistas
CREATE TABLE medicos_especialistas (
    id_medico INT PRIMARY KEY,
    especialidad VARCHAR(100) NOT NULL,
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
    id_medico_general INT,
    activo BOOLEAN DEFAULT TRUE,
    fecha_baja TIMESTAMP,
    CONSTRAINT fk_paciente_medico_general FOREIGN KEY (id_medico_general) 
        REFERENCES medicos_generales(id_medico)
);

-- Índice único parcial: solo valida unicidad cuando el valor NO es NULL
CREATE UNIQUE INDEX idx_pacientes_num_ss_unique 
ON pacientes (num_seguridad_social) 
WHERE num_seguridad_social IS NOT NULL;

-- Catalogo de Enfermedades
CREATE TABLE enfermedades_catalogo (
    id_enfermedad SERIAL PRIMARY KEY,
    nombre_patologia VARCHAR(150) UNIQUE NOT NULL
);

-- Perfiles de Salud
CREATE TABLE perfiles_salud (
    id_perfil SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) UNIQUE NOT NULL,
    peso NUMERIC(5,2),
    altura NUMERIC(3,2),
    alergias TEXT,
    actividad_fisica VARCHAR(100),
    consumo_tabaco VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_paciente_perfil FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni)
);

-- Antecedentes Familiares
CREATE TABLE antecedentes_familiares (
    id_antecedente SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    id_enfermedad INT NOT NULL,
    parentesco VARCHAR(50) NOT NULL, -- padre, madre, hermano, abuelo_paterno, etc.
    lado_familiar VARCHAR(20), -- paterno, materno, ambos
    edad_diagnóstico INT, -- edad en que el familiar fue diagnosticado
    notas_adicionales TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    CONSTRAINT fk_paciente_antecedente FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni),
    CONSTRAINT fk_enfermedad_antecedente FOREIGN KEY (id_enfermedad) 
        REFERENCES enfermedades_catalogo(id_enfermedad)
);

-- Consultas Medicas
CREATE TABLE consultas (
    id_consulta SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    id_medico INT NOT NULL,
    fecha TIMESTAMP NOT NULL,
    diagnostico TEXT,
    tratamiento TEXT,
    observaciones TEXT,
    CONSTRAINT fk_paciente_consulta FOREIGN KEY (id_paciente) 
        REFERENCES pacientes(dni),
    CONSTRAINT fk_medico_consulta FOREIGN KEY (id_medico) 
        REFERENCES medicos(id_medico)
);

-- Recordatorios
CREATE TABLE recordatorios (
    id_recordatorio SERIAL PRIMARY KEY,
    id_consulta INT NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    tipo_recordatorio VARCHAR(50) CHECK (tipo_recordatorio IN ('Medicacion', 'Control', 'Cita', 'Otro')) DEFAULT 'Otro',
    razon VARCHAR(255) NOT NULL,
    estado VARCHAR(20) DEFAULT 'Pendiente' CHECK (estado IN ('Pendiente', 'Completado')),
    CONSTRAINT fk_consulta_recordatorio FOREIGN KEY (id_consulta) 
        REFERENCES consultas(id_consulta)
);

-- Auditoria y Logs
CREATE TABLE auditoria_logs (
    id_log SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20),
    id_medico INT,
    id_admin INT,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50) NOT NULL,
    registro_id VARCHAR(50),
    fecha_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    detalles TEXT,
    CONSTRAINT fk_auditoria_paciente FOREIGN KEY (id_paciente) REFERENCES pacientes(dni),
    CONSTRAINT fk_auditoria_medico FOREIGN KEY (id_medico) REFERENCES medicos(id_medico),
    CONSTRAINT fk_auditoria_admin FOREIGN KEY (id_admin) REFERENCES administradores(id_admin),
    CONSTRAINT chk_un_solo_autor CHECK (
        (id_paciente IS NOT NULL)::INT + 
        (id_medico IS NOT NULL)::INT + 
        (id_admin IS NOT NULL)::INT = 1
    )
);

-- Indices
CREATE INDEX idx_pacientes_email ON pacientes(email);
CREATE INDEX idx_pacientes_activo ON pacientes(activo);
CREATE INDEX idx_pacientes_medico_general ON pacientes(id_medico_general);
CREATE INDEX idx_medicos_email ON medicos(email);
CREATE INDEX idx_medicos_activo ON medicos(activo);
CREATE INDEX idx_medicos_tipo ON medicos(tipo_medico);
CREATE INDEX idx_consultas_paciente ON consultas(id_paciente);
CREATE INDEX idx_consultas_medico ON consultas(id_medico);

-- Fin de la creacion de tablas
