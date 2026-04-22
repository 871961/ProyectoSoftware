-- =============================================================================
-- Migracion: chat seguro medico-medico
-- Fecha: Abril 2026
-- =============================================================================

CREATE TABLE IF NOT EXISTS chat_mensajes (
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

ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'texto';
ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS nombre_archivo VARCHAR(255);
ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS ruta_archivo TEXT;
ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS tamano_bytes INT;

CREATE INDEX IF NOT EXISTS idx_chat_emisor_receptor_fecha
    ON chat_mensajes(id_emisor, id_receptor, enviado_en DESC);

CREATE INDEX IF NOT EXISTS idx_chat_receptor_leido
    ON chat_mensajes(id_receptor, leido_en);

COMMENT ON TABLE chat_mensajes IS 'Mensajeria cifrada medico-medico para coordinacion asistencial';
