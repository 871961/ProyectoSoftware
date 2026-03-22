<?php
/**
 * Archivo: RecordatorioDAO.php
 * Descripción: DAO alineado con el schema (recordatorios vinculados a consultas)
 * Fecha: Marzo 2026
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/RecordatorioVO.php';

class RecordatorioDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function insertar(RecordatorioVO $recordatorio) {
        try {
            $errores = $recordatorio->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO recordatorios (id_consulta, fecha_hora, tipo_recordatorio, razon, estado)
                    VALUES (:id_consulta, :fecha_hora, :tipo_recordatorio, :razon, :estado)
                    RETURNING id_recordatorio";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_consulta' => $recordatorio->getIdConsulta(),
                ':fecha_hora' => $recordatorio->getFechaHora(),
                ':tipo_recordatorio' => $recordatorio->getTipoRecordatorio(),
                ':razon' => $recordatorio->getRazon(),
                ':estado' => $recordatorio->getEstado()
            ]);

            $id = $stmt->fetchColumn();
            $recordatorio->setIdRecordatorio($id);

            $this->registrarAuditoria('INSERT', 'recordatorios', $id, 'Creación de recordatorio');
            $this->db->commit();
            return $id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPorPaciente($id_paciente, $solo_pendientes = false) {
        $condPend = $solo_pendientes ? "AND r.estado = 'Pendiente'" : "";
        $sql = "SELECT r.*, c.id_paciente, c.id_medico,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos,
                       m.nombre AS medico_nombre, m.apellidos AS medico_apellidos
                FROM recordatorios r
                JOIN consultas c ON r.id_consulta = c.id_consulta
                JOIN pacientes p ON c.id_paciente = p.dni
                JOIN medicos m ON c.id_medico = m.id_medico
                WHERE c.id_paciente = :id_paciente
                $condPend
                ORDER BY r.fecha_hora ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_paciente' => $id_paciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorMedicoYFecha($id_medico, $fecha = null) {
        $condFecha = $fecha ? "AND DATE(r.fecha_hora) = :fecha" : "";
        $sql = "SELECT r.*, c.id_paciente,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos
                FROM recordatorios r
                JOIN consultas c ON r.id_consulta = c.id_consulta
                JOIN pacientes p ON c.id_paciente = p.dni
                WHERE c.id_medico = :id_medico
                $condFecha
                ORDER BY r.fecha_hora ASC";
        $stmt = $this->db->prepare($sql);
        $params = [':id_medico' => $id_medico];
        if ($fecha) $params[':fecha'] = $fecha;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorConsulta($id_consulta, $id_medico) {
        $sql = "SELECT r.*, c.id_paciente,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos
                FROM recordatorios r
                JOIN consultas c ON r.id_consulta = c.id_consulta
                JOIN pacientes p ON c.id_paciente = p.dni
                WHERE r.id_consulta = :id_consulta AND c.id_medico = :id_medico
                ORDER BY r.fecha_hora ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_consulta' => $id_consulta,
            ':id_medico' => $id_medico
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id_recordatorio) {
        $sql = "SELECT r.*, c.id_paciente, c.id_medico
                FROM recordatorios r
                JOIN consultas c ON r.id_consulta = c.id_consulta
                WHERE r.id_recordatorio = :id_recordatorio";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_recordatorio' => $id_recordatorio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id_recordatorio, $nuevo_estado) {
        $validos = [RecordatorioVO::ESTADO_PENDIENTE, RecordatorioVO::ESTADO_COMPLETADO];
        if (!in_array($nuevo_estado, $validos)) {
            throw new Exception("Estado no válido: $nuevo_estado");
        }
        $sql = "UPDATE recordatorios SET estado = :estado WHERE id_recordatorio = :id_recordatorio";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $nuevo_estado, ':id_recordatorio' => $id_recordatorio]);
    }

    public function actualizar(RecordatorioVO $r) {
        $errores = $r->validar();
        if (!empty($errores)) {
            throw new Exception("Datos inválidos: " . implode(', ', $errores));
        }
        $sql = "UPDATE recordatorios 
                SET fecha_hora = :fecha_hora,
                    tipo_recordatorio = :tipo_recordatorio,
                    razon = :razon
                WHERE id_recordatorio = :id_recordatorio";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':fecha_hora' => $r->getFechaHora(),
            ':tipo_recordatorio' => $r->getTipoRecordatorio(),
            ':razon' => $r->getRazon(),
            ':id_recordatorio' => $r->getIdRecordatorio()
        ]);
    }

    public function eliminar($id_recordatorio, $id_medico) {
        $sql = "DELETE FROM recordatorios USING consultas 
                WHERE recordatorios.id_recordatorio = :id_recordatorio
                  AND consultas.id_consulta = recordatorios.id_consulta
                  AND consultas.id_medico = :id_medico";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id_recordatorio' => $id_recordatorio,
            ':id_medico' => $id_medico
        ]);
    }

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
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
}
?>
