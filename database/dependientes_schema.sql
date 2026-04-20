-- =============================================================================
-- MedHistory - Schema para Pacientes Dependientes (Menores)
-- Sistema de Gestión de Historial Clínico
-- Fecha: Marzo 2026
-- =============================================================================
-- NOTA: Ejecutar después de schema.sql
-- =============================================================================

-- Diseño actualizado: Pacientes dependientes como entidad débil de `pacientes`
-- En lugar de tablas independientes para dependientes, se reutiliza la tabla `pacientes`.
-- Se añaden columnas específicas para marcar un paciente como dependiente y enlazarlo con su tutor.

-- Añadir columnas a `pacientes` para soportar dependientes (si no existen)
ALTER TABLE pacientes
ADD COLUMN IF NOT EXISTS es_dependiente BOOLEAN DEFAULT FALSE,
ADD COLUMN IF NOT EXISTS dni_tutor VARCHAR(20),
ADD COLUMN IF NOT EXISTS id_pediatra INT,
ADD COLUMN IF NOT EXISTS grupo_sanguineo VARCHAR(5),
ADD COLUMN IF NOT EXISTS alergias TEXT,
ADD COLUMN IF NOT EXISTS observaciones TEXT;

-- Añadir FK self-referencial: dni_tutor -> pacientes(dni) (si no existe)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu USING (constraint_name, table_schema)
        WHERE tc.constraint_type = 'FOREIGN KEY'
          AND tc.table_name = 'pacientes'
          AND kcu.column_name = 'dni_tutor'
    ) THEN
        ALTER TABLE pacientes
        ADD CONSTRAINT fk_paciente_tutor FOREIGN KEY (dni_tutor) REFERENCES pacientes(dni);
    END IF;
END $$;

-- Añadir FK a pediatra (medicos_especialistas)
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.table_constraints tc
        JOIN information_schema.key_column_usage kcu USING (constraint_name, table_schema)
        WHERE tc.constraint_type = 'FOREIGN KEY'
          AND tc.table_name = 'pacientes'
          AND kcu.column_name = 'id_pediatra'
    ) THEN
        ALTER TABLE pacientes
        ADD CONSTRAINT fk_paciente_pediatra FOREIGN KEY (id_pediatra) REFERENCES medicos_especialistas(id_medico);
    END IF;
END $$;

-- Índices para dependientes
CREATE INDEX IF NOT EXISTS idx_pacientes_es_dependiente ON pacientes(es_dependiente);
CREATE INDEX IF NOT EXISTS idx_pacientes_dni_tutor ON pacientes(dni_tutor);
CREATE INDEX IF NOT EXISTS idx_pacientes_id_pediatra ON pacientes(id_pediatra);

-- Eliminar tablas específicas de dependientes antiguas (si existen) para evitar duplicidad
DROP TABLE IF EXISTS recordatorios_dependientes CASCADE;
DROP TABLE IF EXISTS consultas_dependientes CASCADE;
DROP TABLE IF EXISTS antecedentes_familiares_dependientes CASCADE;
DROP TABLE IF EXISTS perfiles_salud_dependientes CASCADE;
DROP TABLE IF EXISTS pacientes_dependientes CASCADE;

-- Mantener contador de dependientes asignados en medicos_especialistas
ALTER TABLE medicos_especialistas
ADD COLUMN IF NOT EXISTS dependientes_asignados INT DEFAULT 0;

-- Tabla para registrar cartilla de vacunas (reutilizable para pacientes y dependientes)
CREATE TABLE IF NOT EXISTS cartilla_vacunas (
    id_vacuna SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    nombre_vacuna VARCHAR(255) NOT NULL,
    fecha_administracion DATE,
    dosis VARCHAR(50),
    centro VARCHAR(255),
    observaciones TEXT,
    estado VARCHAR(20) DEFAULT 'Administrada' CHECK (estado IN ('Administrada','Pendiente','No administrada')),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_vacuna_paciente FOREIGN KEY (id_paciente) REFERENCES pacientes(dni)
);

CREATE INDEX IF NOT EXISTS idx_cartilla_vacunas_paciente ON cartilla_vacunas(id_paciente);

-- =============================================================================
-- COMENTARIOS
-- =============================================================================
COMMENT ON TABLE pacientes_dependientes IS 'Pacientes menores de edad a cargo de un tutor (paciente adulto)';
COMMENT ON TABLE perfiles_salud_dependientes IS 'Perfil de salud de pacientes dependientes';
COMMENT ON TABLE antecedentes_familiares_dependientes IS 'Antecedentes familiares de pacientes dependientes';
COMMENT ON TABLE consultas_dependientes IS 'Consultas médicas de pacientes dependientes';
COMMENT ON TABLE recordatorios_dependientes IS 'Recordatorios para pacientes dependientes';

-- =============================================================================
-- FIN DEL SCHEMA DE DEPENDIENTES
-- =============================================================================
