<?php
/**
 * Archivo: RecordatorioDAO.php
 * Descripción: Data Access Object para manejar recordatorios en PostgreSQL
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once 'config/database.php';
require_once 'vo/RecordatorioVO.php';

class RecordatorioDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo recordatorio
     */
    public function insertar(RecordatorioVO $recordatorio) {
        try {
            // Validar antes de insertar
            $errores = $recordatorio->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO recordatorios 
                    (id_paciente, id_medico, tipo, titulo, descripcion, fecha_recordatorio, 
                     hora_recordatorio, estado, prioridad, notas_adicionales) 
                    VALUES (:id_paciente, :id_medico, :tipo, :titulo, :descripcion, :fecha_recordatorio,
                            :hora_recordatorio, :estado, :prioridad, :notas_adicionales) 
                    RETURNING id_recordatorio";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_paciente' => $recordatorio->getIdPaciente(),
                ':id_medico' => $recordatorio->getIdMedico(),
                ':tipo' => $recordatorio->getTipo(),
                ':titulo' => $recordatorio->getTitulo(),
                ':descripcion' => $recordatorio->getDescripcion(),
                ':fecha_recordatorio' => $recordatorio->getFechaRecordatorio(),
                ':hora_recordatorio' => $recordatorio->getHoraRecordatorio(),
                ':estado' => $recordatorio->getEstado(),
                ':prioridad' => $recordatorio->getPrioridad(),
                ':notas_adicionales' => $recordatorio->getNotasAdicionales()
            ]);

            $id_recordatorio = $stmt->fetchColumn();
            $recordatorio->setIdRecordatorio($id_recordatorio);

            // Registrar en auditoría
            $this->registrarAuditoria('INSERT', 'recordatorios', $id_recordatorio, 
                                    'Creación de recordatorio: ' . $recordatorio->getTitulo());

            $this->db->commit();
            return $id_recordatorio;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al insertar recordatorio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todos los recordatorios de un paciente
     */
    public function obtenerPorPaciente($id_paciente, $solo_activos = true) {
        try {
            $condicion_activo = $solo_activos ? "AND r.activo = true" : "";
            
            $sql = "SELECT r.*, p.nombre as paciente_nombre, p.apellidos as paciente_apellidos,
                           m.nombre as medico_nombre, m.apellidos as medico_apellidos, me.especialidad
                    FROM recordatorios r 
                    LEFT JOIN pacientes p ON r.id_paciente = p.id_paciente 
                    LEFT JOIN medicos m ON r.id_medico = m.id_medico 
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE r.id_paciente = :id_paciente $condicion_activo
                    ORDER BY r.fecha_recordatorio ASC, r.hora_recordatorio ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_paciente' => $id_paciente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener recordatorios por paciente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene recordatorios pendientes de un paciente
     */
    public function obtenerPendientesPorPaciente($id_paciente) {
        try {
            $sql = "SELECT r.*, p.nombre as paciente_nombre, p.apellidos as paciente_apellidos,
                           m.nombre as medico_nombre, m.apellidos as medico_apellidos, me.especialidad
                    FROM recordatorios r 
                    LEFT JOIN pacientes p ON r.id_paciente = p.id_paciente 
                    LEFT JOIN medicos m ON r.id_medico = m.id_medico 
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE r.id_paciente = :id_paciente 
                    AND r.estado = 'pendiente' 
                    AND r.activo = true
                    ORDER BY r.prioridad = 'urgente' DESC,
                             r.prioridad = 'alta' DESC,
                             r.fecha_recordatorio ASC, 
                             r.hora_recordatorio ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_paciente' => $id_paciente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener recordatorios pendientes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene recordatorios para hoy (todos los pacientes)
     */
    public function obtenerParaHoy() {
        try {
            $sql = "SELECT r.*, p.nombre as paciente_nombre, p.apellidos as paciente_apellidos, p.telefono,
                           m.nombre as medico_nombre, m.apellidos as medico_apellidos, me.especialidad
                    FROM recordatorios r 
                    JOIN pacientes p ON r.id_paciente = p.id_paciente 
                    LEFT JOIN medicos m ON r.id_medico = m.id_medico 
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE DATE(r.fecha_recordatorio) = CURRENT_DATE
                    AND r.estado = 'pendiente' 
                    AND r.activo = true
                    AND p.activo = true
                    ORDER BY r.prioridad = 'urgente' DESC,
                             r.prioridad = 'alta' DESC,
                             r.hora_recordatorio ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener recordatorios para hoy: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene recordatorios por médico y fecha
     */
    public function obtenerPorMedicoYFecha($id_medico, $fecha = null) {
        try {
            $condicion_fecha = $fecha ? "AND DATE(r.fecha_recordatorio) = :fecha" : "";
            
            $sql = "SELECT r.*, p.nombre as paciente_nombre, p.apellidos as paciente_apellidos, p.telefono
                    FROM recordatorios r 
                    JOIN pacientes p ON r.id_paciente = p.id_paciente 
                    WHERE r.id_medico = :id_medico 
                    AND r.activo = true
                    AND p.activo = true
                    $condicion_fecha
                    ORDER BY r.fecha_recordatorio ASC, r.hora_recordatorio ASC";

            $stmt = $this->db->prepare($sql);
            $params = [':id_medico' => $id_medico];
            if ($fecha) {
                $params[':fecha'] = $fecha;
            }
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener recordatorios por médico: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marca un recordatorio como completado
     */
    public function marcarComoCompletado($id_recordatorio, $notas_adicionales = null) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE recordatorios 
                    SET estado = 'completado', 
                        fecha_completado = CURRENT_TIMESTAMP";
            
            if ($notas_adicionales) {
                $sql .= ", notas_adicionales = COALESCE(notas_adicionales, '') || '\n' || :notas";
            }
            
            $sql .= " WHERE id_recordatorio = :id_recordatorio AND activo = true";

            $stmt = $this->db->prepare($sql);
            $params = [':id_recordatorio' => $id_recordatorio];
            if ($notas_adicionales) {
                $params[':notas'] = 'COMPLETADO: ' . $notas_adicionales;
            }
            
            $resultado = $stmt->execute($params);

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'recordatorios', $id_recordatorio, 
                                    'Recordatorio marcado como completado');

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al marcar recordatorio como completado: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza el estado de un recordatorio
     */
    public function actualizarEstado($id_recordatorio, $nuevo_estado, $notas = null) {
        try {
            $estados_validos = ['pendiente', 'completado', 'cancelado', 'vencido'];
            if (!in_array($nuevo_estado, $estados_validos)) {
                throw new Exception("Estado no válido: $nuevo_estado");
            }

            $this->db->beginTransaction();

            $sql = "UPDATE recordatorios 
                    SET estado = :estado";
            
            if ($nuevo_estado === 'completado') {
                $sql .= ", fecha_completado = CURRENT_TIMESTAMP";
            }
            
            if ($notas) {
                $sql .= ", notas_adicionales = COALESCE(notas_adicionales, '') || '\n' || :notas";
            }
            
            $sql .= " WHERE id_recordatorio = :id_recordatorio AND activo = true";

            $stmt = $this->db->prepare($sql);
            $params = [
                ':estado' => $nuevo_estado,
                ':id_recordatorio' => $id_recordatorio
            ];
            
            if ($notas) {
                $params[':notas'] = strtoupper($nuevo_estado) . ': ' . $notas;
            }
            
            $resultado = $stmt->execute($params);

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'recordatorios', $id_recordatorio, 
                                    "Estado cambiado a: $nuevo_estado");

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar estado de recordatorio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Marca recordatorios vencidos automáticamente
     */
    public function marcarVencidos() {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE recordatorios 
                    SET estado = 'vencido'
                    WHERE estado = 'pendiente' 
                    AND activo = true
                    AND (fecha_recordatorio < CURRENT_DATE 
                         OR (fecha_recordatorio = CURRENT_DATE 
                             AND hora_recordatorio < CURRENT_TIME))";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute();
            $afectados = $stmt->rowCount();

            // Registrar en auditoría
            if ($afectados > 0) {
                $this->registrarAuditoria('UPDATE', 'recordatorios', null, 
                                        "Marcados $afectados recordatorios como vencidos automáticamente");
            }

            $this->db->commit();
            return $afectados;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al marcar recordatorios vencidos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas de recordatorios
     */
    public function obtenerEstadisticas($id_medico = null) {
        try {
            $condicion_medico = $id_medico ? "AND r.id_medico = :id_medico" : "";
            
            $sql = "SELECT 
                        COUNT(*) as total,
                        COUNT(CASE WHEN r.estado = 'pendiente' THEN 1 END) as pendientes,
                        COUNT(CASE WHEN r.estado = 'completado' THEN 1 END) as completados,
                        COUNT(CASE WHEN r.estado = 'vencido' THEN 1 END) as vencidos,
                        COUNT(CASE WHEN r.prioridad = 'urgente' AND r.estado = 'pendiente' THEN 1 END) as urgentes,
                        COUNT(CASE WHEN DATE(r.fecha_recordatorio) = CURRENT_DATE AND r.estado = 'pendiente' THEN 1 END) as hoy
                    FROM recordatorios r
                    WHERE r.activo = true
                    $condicion_medico";

            $stmt = $this->db->prepare($sql);
            if ($id_medico) {
                $stmt->execute([':id_medico' => $id_medico]);
            } else {
                $stmt->execute();
            }

            return $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de recordatorios: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Borrado lógico (desactivar recordatorio)
     */
    public function darDeBaja($id_recordatorio, $id_responsable) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE recordatorios 
                    SET activo = false, estado = 'cancelado'
                    WHERE id_recordatorio = :id_recordatorio";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([':id_recordatorio' => $id_recordatorio]);

            // Registrar en auditoría
            $this->registrarAuditoria('DELETE', 'recordatorios', $id_recordatorio, 
                                    'Borrado lógico de recordatorio', $id_responsable);

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en borrado lógico de recordatorio: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Registra las acciones en la tabla de auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registro_id, $detalles, $id_responsable = null) {
        try {
            $sql = "INSERT INTO auditoria_logs (accion, tabla_afectada, registro_id, detalles, usuario_responsable) 
                    VALUES (:accion, :tabla, :registro_id, :detalles, :usuario_responsable)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':accion' => $accion,
                ':tabla' => $tabla,
                ':registro_id' => $registro_id,
                ':detalles' => $detalles,
                ':usuario_responsable' => $id_responsable ?? 'Sistema'
            ]);

        } catch (Exception $e) {
            error_log("Error al registrar auditoría: " . $e->getMessage());
            // No lanzar excepción para no interrumpir la operación principal
        }
    }
}
?>