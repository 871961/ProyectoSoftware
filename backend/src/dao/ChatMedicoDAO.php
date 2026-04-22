<?php
/**
 * Archivo: ChatMedicoDAO.php
 * Descripcion: DAO para mensajeria segura medico-medico
 */

require_once __DIR__ . '/../config/database.php';

class ChatMedicoDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
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

    public function crearMensaje($idEmisor, $idReceptor, $mensajeCifrado, $nonce, $tag, $algoritmo, $tipoContenido = 'texto', $nombreArchivo = null, $rutaArchivo = null, $tamanoBytes = null) {
        $sql = "INSERT INTO chat_mensajes
                    (id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo, tipo_contenido, nombre_archivo, ruta_archivo, tamano_bytes)
                VALUES
                    (:id_emisor, :id_receptor, :mensaje_cifrado, :nonce, :tag, :algoritmo, :tipo_contenido, :nombre_archivo, :ruta_archivo, :tamano_bytes)
                RETURNING id_mensaje, enviado_en";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_emisor' => $idEmisor,
            ':id_receptor' => $idReceptor,
            ':mensaje_cifrado' => $mensajeCifrado,
            ':nonce' => $nonce,
            ':tag' => $tag,
            ':algoritmo' => $algoritmo,
            ':tipo_contenido' => $tipoContenido,
            ':nombre_archivo' => $nombreArchivo,
            ':ruta_archivo' => $rutaArchivo,
            ':tamano_bytes' => $tamanoBytes
        ]);

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
        $sql = "SELECT id_mensaje, id_emisor, id_receptor, mensaje_cifrado, nonce, tag, algoritmo, tipo_contenido, nombre_archivo, ruta_archivo, tamano_bytes, enviado_en, leido_en
                FROM chat_mensajes
                WHERE (
                    id_emisor = :id_a AND id_receptor = :id_b AND eliminado_por_emisor = FALSE
                ) OR (
                    id_emisor = :id_b AND id_receptor = :id_a AND eliminado_por_receptor = FALSE
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
                        (id_emisor = :id_medico AND eliminado_por_emisor = FALSE)
                        OR
                        (id_receptor = :id_medico AND eliminado_por_receptor = FALSE)
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

        public function obtenerMensajePorIdParaMedico($idMensaje, $idMedico) {
                $sql = "SELECT id_mensaje, id_emisor, id_receptor, nombre_archivo, ruta_archivo, tamano_bytes, tipo_contenido
                                FROM chat_mensajes
                                WHERE id_mensaje = :id_mensaje
                                    AND (
                                        (id_emisor = :id_medico AND eliminado_por_emisor = FALSE)
                                        OR
                                        (id_receptor = :id_medico AND eliminado_por_receptor = FALSE)
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
