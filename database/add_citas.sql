-- =============================================================================
-- Migración: Módulo de Citas Médicas
-- Sistema MedHistory
-- Fecha: Mayo 2026
-- =============================================================================
-- Ejecutar sobre la base de datos existente (no destruye datos)
-- =============================================================================

CREATE TABLE IF NOT EXISTS citas (
    id_cita SERIAL PRIMARY KEY,
    id_paciente VARCHAR(20) NOT NULL,
    id_medico INT NOT NULL,
    fecha_hora TIMESTAMP NOT NULL,
    motivo TEXT NOT NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'Pendiente'
        CHECK (estado IN ('Pendiente', 'Confirmada', 'Cancelada', 'Completada')),
    tipo VARCHAR(20) NOT NULL DEFAULT 'Presencial'
        CHECK (tipo IN ('Presencial', 'Telematica')),
    notas_cancelacion TEXT,
    cancelada_por VARCHAR(10) CHECK (cancelada_por IN ('paciente', 'medico')),
    activo BOOLEAN NOT NULL DEFAULT TRUE,
    fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cita_paciente FOREIGN KEY (id_paciente) REFERENCES pacientes(dni),
    CONSTRAINT fk_cita_medico FOREIGN KEY (id_medico) REFERENCES medicos(id_medico)
);

CREATE INDEX IF NOT EXISTS idx_citas_paciente    ON citas(id_paciente);
CREATE INDEX IF NOT EXISTS idx_citas_medico      ON citas(id_medico);
CREATE INDEX IF NOT EXISTS idx_citas_fecha       ON citas(fecha_hora);
CREATE INDEX IF NOT EXISTS idx_citas_estado      ON citas(estado);
CREATE INDEX IF NOT EXISTS idx_citas_activo      ON citas(activo);

COMMENT ON TABLE citas IS 'Agenda de citas solicitadas por pacientes o tutores legales';
