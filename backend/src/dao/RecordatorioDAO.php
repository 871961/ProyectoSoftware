<?php
/**
 * Archivo: RecordatorioDAO.php
 * Descripcion: DAO alineado con el schema (recordatorios vinculados a consultas)
 * Fecha: Marzo 2026
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/RecordatorioVO.php';

class RecordatorioDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function insertar(RecordatorioVO $recordatorio, $id_medico = null, $id_paciente = null) {
        try {
            $errores = $recordatorio->validar();
            if (!empty($errores)) {
                throw new Exception("Datos invalidos: " . implode(", ", $errores));
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

            // Cerrar la transaccion principal antes de cualquier auditoria
            $this->db->commit();

            // Auditoria en operacion aparte para no invalidar la insercion si falla
            $this->registrarAuditoria(
                'INSERT',
                'recordatorios',
                $id,
                'Creacion de recordatorio',
                $id_medico,
                $id_paciente
            );

            return $id;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
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
            throw new Exception("Estado no valido: $nuevo_estado");
        }
        $sql = "UPDATE recordatorios SET estado = :estado WHERE id_recordatorio = :id_recordatorio";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':estado' => $nuevo_estado, ':id_recordatorio' => $id_recordatorio]);
    }

    public function actualizar(RecordatorioVO $r) {
        $errores = $r->validar();
        if (!empty($errores)) {
            throw new Exception("Datos invalidos: " . implode(', ', $errores));
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

    private function registrarAuditoria($accion, $tabla, $registro_id, $detalles, $id_medico = null, $id_paciente = null) {
        try {
            // La tabla auditoria_logs obliga a un unico autor. Si no tenemos datos, salimos.
            if ($id_medico === null && $id_paciente === null) {
                return;
            }

            if ($id_medico !== null) {
                $sql = "INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles)
                        VALUES (:id_medico, :accion, :tabla, :registro_id, :detalles)";
                $params = [
                    ':id_medico' => $id_medico,
                    ':accion' => $accion,
                    ':tabla' => $tabla,
                    ':registro_id' => $registro_id,
                    ':detalles' => $detalles
                ];
            } else {
                $sql = "INSERT INTO auditoria_logs (id_paciente, accion, tabla_afectada, registro_id, detalles)
                        VALUES (:id_paciente, :accion, :tabla, :registro_id, :detalles)";
                $params = [
                    ':id_paciente' => $id_paciente,
                    ':accion' => $accion,
                    ':tabla' => $tabla,
                    ':registro_id' => $registro_id,
                    ':detalles' => $detalles
                ];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Error en auditoria: " . $e->getMessage());
        }
    }
}
?>
