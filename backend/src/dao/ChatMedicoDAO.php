<?php
/**
 * Archivo: ChatMedicoDAO.php
 * Descripcion: DAO para mensajeria segura medico-medico
 */

require_once __DIR__ . '/../config/database.php';

class ChatMedicoDAO {
    private $db;
    private $supportsAttachmentColumns = null;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureChatSchema();
    }

    private function ensureChatSchema() {
        if ($this->tablaExiste('chat_mensajes')) {
            $this->db->exec("ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS tipo_contenido VARCHAR(20) NOT NULL DEFAULT 'texto'");
            $this->db->exec("ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS nombre_archivo VARCHAR(255)");
            $this->db->exec("ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS ruta_archivo TEXT");
            $this->db->exec("ALTER TABLE chat_mensajes ADD COLUMN IF NOT EXISTS tamano_bytes INT");
        } else {
            $this->db->exec("CREATE TABLE chat_mensajes (
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
            )");
        }

        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_chat_emisor_receptor_fecha ON chat_mensajes(id_emisor, id_receptor, enviado_en DESC)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_chat_receptor_leido ON chat_mensajes(id_receptor, leido_en)");
    }

    private function tablaExiste($tabla) {
        $sql = "SELECT COUNT(*)
                FROM information_schema.tables
                WHERE table_schema = 'public'
                  AND table_name = :tabla";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':tabla' => $tabla]);
        return ((int)$stmt->fetchColumn() > 0);
    }

    private function columnaExiste($tabla, $columna) {
        $sql = "SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_schema = 'public'
                  AND table_name = :tabla
                  AND column_name = :columna";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':tabla' => $tabla,
            ':columna' => $columna
        ]);
        return ((int)$stmt->fetchColumn() > 0);
    }

    private function chatSupportsAttachmentColumns() {
        if ($this->supportsAttachmentColumns !== null) {
            return $this->supportsAttachmentColumns;
        }

        $sql = "SELECT COUNT(*)
                FROM information_schema.columns
                WHERE table_name = 'chat_mensajes'
                  AND column_name IN ('tipo_contenido', 'nombre_archivo', 'ruta_archivo', 'tamano_bytes')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $this->supportsAttachmentColumns = ((int)$stmt->fetchColumn() >= 4);

        return $this->supportsAttachmentColumns;
    }

    public function listarMedicosDisponibles($idMedicoActual, $busqueda = '') {
        $sql = "SELECT m.id_medico, m.nombre, m.apellidos, m.num_colegiado, m.tipo_medico, COALESCE(me.especialidad, 'Medico General') AS especialidad
                FROM medicos m
                LEFT JOIN medicos_especialistas me ON me.id_medico = m.id_medico
                WHERE m.activo = TRUE
                  AND m.id_medico <> :id_medico";

        $params = [':id_medico' => $idMedicoActual];
        $busqueda = trim((string) $busqueda);

        if ($busqueda !== '') {
            $sql .= "
                  AND (
                    lower(m.nombre) LIKE lower(:q_like)
                   OR lower(m.apellidos) LIKE lower(:q_like)
                   OR lower(m.nombre || ' ' || m.apellidos) LIKE lower(:q_like)
                     OR m.num_colegiado ILIKE :q_like
                  )";
            $params[':q_like'] = '%' . $busqueda . '%';
        }

        $sql .= "
                ORDER BY m.apellidos, m.nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function medicoActivoPorId($idMedico) {
        $sql = "SELECT id_medico FROM medicos WHERE id_medico = :id_medico AND activo = TRUE";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_medico' => $idMedico]);
        return $stmt->fetchColumn();
    }

    public function soportaAdjuntos() {
        return $this->chatSupportsAttachmentColumns();
    }

    public function crearMensaje($idEmisor, $idReceptor, $mensajeCifrado, $nonce, $tag, $algoritmo, $tipoContenido = 'texto', $nombreArchivo = null, $rutaArchivo = null, $tamanoBytes = null) {
        if (!$this->tablaExiste('chat_mensajes')) {
            throw new Exception('La tabla chat_mensajes no existe. Ejecuta database/chat_medicos_seguro.sql para habilitar el chat.');
        }

        $usaAdjuntos = $this->chatSupportsAttachmentColumns();

        if ($usaAdjuntos) {
            $sql = "INSERT INTO chat_mensajes
                        (id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo, tipo_contenido, nombre_archivo, ruta_archivo, tamano_bytes)
                    VALUES
                        (:id_emisor, :id_receptor, :mensaje_cifrado, :nonce, :tag, :algoritmo, :tipo_contenido, :nombre_archivo, :ruta_archivo, :tamano_bytes)
                    RETURNING id_mensaje, enviado_en";
        } else {
            $sql = "INSERT INTO chat_mensajes
                        (id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo)
                    VALUES
                        (:id_emisor, :id_receptor, :mensaje_cifrado, :nonce, :tag, :algoritmo)
                    RETURNING id_mensaje, enviado_en";
        }

        $stmt = $this->db->prepare($sql);
        $params = [
            ':id_emisor' => $idEmisor,
            ':id_receptor' => $idReceptor,
            ':mensaje_cifrado' => $mensajeCifrado,
            ':nonce' => $nonce,
            ':tag' => $tag,
            ':algoritmo' => $algoritmo
        ];

        if ($usaAdjuntos) {
            $params[':tipo_contenido'] = $tipoContenido;
            $params[':nombre_archivo'] = $nombreArchivo;
            $params[':ruta_archivo'] = $rutaArchivo;
            $params[':tamano_bytes'] = $tamanoBytes;
        }

        $stmt->execute($params);

        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->registrarAuditoria(
            $idEmisor,
            'CHAT_SEND',
            isset($fila['id_mensaje']) ? $fila['id_mensaje'] : null,
            [
                'id_receptor' => (int)$idReceptor,
                'tipo_contenido' => $tipoContenido,
                'nombre_archivo' => $nombreArchivo
            ]
        );

        return $fila;
    }

    public function obtenerConversacion($idMedicoA, $idMedicoB, $limite = 100) {
        if (!$this->tablaExiste('chat_mensajes')) {
            return [];
        }

        $usaAdjuntos = $this->chatSupportsAttachmentColumns();
        $camposAdjuntos = $usaAdjuntos
            ? ", tipo_contenido, nombre_archivo, ruta_archivo, tamano_bytes"
            : ", 'texto' AS tipo_contenido, NULL AS nombre_archivo, NULL AS ruta_archivo, NULL::INT AS tamano_bytes";

        $filtraEliminadoEmisor = $this->columnaExiste('chat_mensajes', 'eliminado_por_emisor');
        $filtraEliminadoReceptor = $this->columnaExiste('chat_mensajes', 'eliminado_por_receptor');

        $condA = "id_emisor = :id_a AND id_receptor = :id_b";
        $condB = "id_emisor = :id_b AND id_receptor = :id_a";
        if ($filtraEliminadoEmisor) {
            $condA .= " AND eliminado_por_emisor = FALSE";
        }
        if ($filtraEliminadoReceptor) {
            $condB .= " AND eliminado_por_receptor = FALSE";
        }

        $sql = "SELECT id_mensaje, id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo{$camposAdjuntos}, enviado_en, leido_en
                FROM chat_mensajes
                WHERE (
                    {$condA}
                ) OR (
                    {$condB}
                )
                ORDER BY id_mensaje DESC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_a', $idMedicoA, PDO::PARAM_INT);
        $stmt->bindValue(':id_b', $idMedicoB, PDO::PARAM_INT);
        $stmt->bindValue(':limite', max(1, min(200, (int)$limite)), PDO::PARAM_INT);
        $stmt->execute();

        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($filas);
    }

    public function marcarLeidos($idReceptor, $idEmisor) {
        $sql = "UPDATE chat_mensajes
                SET leido_en = CURRENT_TIMESTAMP
                WHERE id_receptor = :id_receptor
                  AND id_emisor = :id_emisor
                  AND leido_en IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_receptor' => $idReceptor,
            ':id_emisor' => $idEmisor
        ]);

        return $stmt->rowCount();
    }

    public function listarResumenConversaciones($idMedico) {
        if (!$this->tablaExiste('chat_mensajes')) {
            return [];
        }

        $filtraEliminadoEmisor = $this->columnaExiste('chat_mensajes', 'eliminado_por_emisor');
        $filtraEliminadoReceptor = $this->columnaExiste('chat_mensajes', 'eliminado_por_receptor');

        $filtroEmisor = $filtraEliminadoEmisor ? " AND eliminado_por_emisor = FALSE" : "";
        $filtroReceptor = $filtraEliminadoReceptor ? " AND eliminado_por_receptor = FALSE" : "";

        $sql = "SELECT
                    c.id_contacto,
                    m.nombre,
                    m.apellidos,
                    m.tipo_medico,
                    COALESCE(me.especialidad, 'Medico General') AS especialidad,
                    c.ultimo_envio,
                    c.no_leidos
                FROM (
                    SELECT
                        CASE WHEN id_emisor = :id_medico THEN id_receptor ELSE id_emisor END AS id_contacto,
                        MAX(enviado_en) AS ultimo_envio,
                        SUM(CASE WHEN id_receptor = :id_medico AND leido_en IS NULL THEN 1 ELSE 0 END) AS no_leidos
                    FROM chat_mensajes
                    WHERE
                        (id_emisor = :id_medico{$filtroEmisor})
                        OR
                        (id_receptor = :id_medico{$filtroReceptor})
                    GROUP BY CASE WHEN id_emisor = :id_medico THEN id_receptor ELSE id_emisor END
                ) c
                INNER JOIN medicos m ON m.id_medico = c.id_contacto
                LEFT JOIN medicos_especialistas me ON me.id_medico = m.id_medico
                WHERE m.activo = TRUE
                ORDER BY c.ultimo_envio DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_medico' => $idMedico]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarNoLeidos($idMedico) {
        if (!$this->tablaExiste('chat_mensajes')) {
            return 0;
        }

        $filtroEliminado = $this->columnaExiste('chat_mensajes', 'eliminado_por_receptor')
            ? " AND eliminado_por_receptor = FALSE"
            : "";

        $sql = "SELECT COALESCE(SUM(CASE WHEN leido_en IS NULL THEN 1 ELSE 0 END), 0) AS total_no_leidos
                FROM chat_mensajes
                WHERE id_receptor = :id_medico
                  {$filtroEliminado}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_medico' => $idMedico]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($fila['total_no_leidos']) ? (int)$fila['total_no_leidos'] : 0;
    }

        public function obtenerMensajePorIdParaMedico($idMensaje, $idMedico) {
            if (!$this->tablaExiste('chat_mensajes')) {
                return null;
            }

            $usaAdjuntos = $this->chatSupportsAttachmentColumns();
            $camposAdjuntos = $usaAdjuntos
                ? ", nombre_archivo, ruta_archivo, tamano_bytes, tipo_contenido"
                : ", NULL AS nombre_archivo, NULL AS ruta_archivo, NULL::INT AS tamano_bytes, 'texto' AS tipo_contenido";

            $filtroEliminadoEmisor = $this->columnaExiste('chat_mensajes', 'eliminado_por_emisor')
                ? " AND eliminado_por_emisor = FALSE"
                : "";
            $filtroEliminadoReceptor = $this->columnaExiste('chat_mensajes', 'eliminado_por_receptor')
                ? " AND eliminado_por_receptor = FALSE"
                : "";

            $sql = "SELECT id_mensaje, id_emisor, id_receptor{$camposAdjuntos}
                                FROM chat_mensajes
                                WHERE id_mensaje = :id_mensaje
                                    AND (
                                        (id_emisor = :id_medico{$filtroEliminadoEmisor})
                                        OR
                                        (id_receptor = :id_medico{$filtroEliminadoReceptor})
                                    )
                                LIMIT 1";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                        ':id_mensaje' => $idMensaje,
                        ':id_medico' => $idMedico
                ]);

                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

    public function registrarEventoChat($idMedico, $accion, $registroId = null, $detalles = []) {
        $this->registrarAuditoria($idMedico, $accion, $registroId, $detalles);
    }

    private function registrarAuditoria($idMedico, $accion, $registroId, $detalles = []) {
        try {
            $sql = "INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles)
                    VALUES (:id_medico, :accion, :tabla, :registro_id, :detalles)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_medico' => $idMedico,
                ':accion' => $accion,
                ':tabla' => 'chat_mensajes',
                ':registro_id' => $registroId,
                ':detalles' => json_encode($detalles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
        } catch (Exception $e) {
            error_log('Error en auditoria de chat: ' . $e->getMessage());
        }
    }
}
