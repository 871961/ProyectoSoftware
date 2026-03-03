<?php
/**
 * Archivo: ConsultaDAO.php
 * Descripción: Data Access Object para la entidad Consulta
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once '../config/database.php';
require_once '../vo/ConsultaVO.php';

class ConsultaDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Inserta una nueva consulta
     */
    public function insertar(ConsultaVO $consulta) {
        try {
            $sql = "INSERT INTO consultas (id_paciente, id_medico, fecha, diagnostico, tratamiento, resultados, observaciones) 
                    VALUES (:id_paciente, :id_medico, :fecha, :diagnostico, :tratamiento, :resultados, :observaciones)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_paciente', $consulta->getIdPaciente());
            $stmt->bindParam(':id_medico', $consulta->getIdMedico());
            $stmt->bindParam(':fecha', $consulta->getFecha());
            $stmt->bindParam(':diagnostico', $consulta->getDiagnostico());
            $stmt->bindParam(':tratamiento', $consulta->getTratamiento());
            $stmt->bindParam(':resultados', $consulta->getResultados());
            $stmt->bindParam(':observaciones', $consulta->getObservaciones());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $consulta->setIdConsulta($this->db->lastInsertId());
                $this->registrarAuditoria('CREAR_CONSULTA', 'consultas', $consulta->getIdConsulta(), $consulta->getIdMedico());
                return true;
            }
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al insertar consulta: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene consultas por paciente
     */
    public function obtenerPorPaciente($id_paciente) {
        try {
            $sql = "SELECT c.*, m.nombre as medico_nombre, m.apellidos as medico_apellidos, m.especialidad 
                    FROM consultas c 
                    INNER JOIN medicos m ON c.id_medico = m.id_medico 
                    WHERE c.id_paciente = :id_paciente 
                    ORDER BY c.fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente);
            $stmt->execute();
            
            $consultas = [];
            while ($fila = $stmt->fetch()) {
                $consultas[] = new ConsultaVO($fila);
            }
            
            return $consultas;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener consultas del paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene consultas por médico
     */
    public function obtenerPorMedico($id_medico, $fecha_desde = null, $fecha_hasta = null) {
        try {
            $sql = "SELECT c.*, p.nombre as paciente_nombre, p.apellidos as paciente_apellidos 
                    FROM consultas c 
                    INNER JOIN pacientes p ON c.id_paciente = p.dni 
                    WHERE c.id_medico = :id_medico";
            
            if ($fecha_desde && $fecha_hasta) {
                $sql .= " AND c.fecha BETWEEN :fecha_desde AND :fecha_hasta";
            }
            
            $sql .= " ORDER BY c.fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            
            if ($fecha_desde && $fecha_hasta) {
                $stmt->bindParam(':fecha_desde', $fecha_desde);
                $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            }
            
            $stmt->execute();
            
            $consultas = [];
            while ($fila = $stmt->fetch()) {
                $consultas[] = new ConsultaVO($fila);
            }
            
            return $consultas;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener consultas del médico: " . $e->getMessage());
        }
    }

    /**
     * Obtiene una consulta por ID
     */
    public function obtenerPorId($id_consulta) {
        try {
            $sql = "SELECT * FROM consultas WHERE id_consulta = :id_consulta";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_consulta', $id_consulta);
            $stmt->execute();
            $resultado = $stmt->fetch();
            if ($resultado) {
                return new ConsultaVO($resultado);
            }
            return null;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener consulta: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza una consulta
     */
    public function actualizar(ConsultaVO $consulta) {
        try {
            $sql = "UPDATE consultas SET diagnostico = :diagnostico, tratamiento = :tratamiento, 
                    resultados = :resultados, observaciones = :observaciones 
                    WHERE id_consulta = :id_consulta";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':diagnostico', $consulta->getDiagnostico());
            $stmt->bindParam(':tratamiento', $consulta->getTratamiento());
            $stmt->bindParam(':resultados', $consulta->getResultados());
            $stmt->bindParam(':observaciones', $consulta->getObservaciones());
            $stmt->bindParam(':id_consulta', $consulta->getIdConsulta());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('ACTUALIZAR_CONSULTA', 'consultas', $consulta->getIdConsulta(), $consulta->getIdMedico());
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar consulta: " . $e->getMessage());
        }
    }

    /**
     * Actualiza una consulta solo si pertenece al médico indicado
     */
    public function actualizarPorMedico(ConsultaVO $consulta, $id_medico) {
        try {
            $sql = "UPDATE consultas 
                    SET fecha = :fecha,
                        diagnostico = :diagnostico,
                        tratamiento = :tratamiento,
                        resultados = :resultados,
                        observaciones = :observaciones
                    WHERE id_consulta = :id_consulta AND id_medico = :id_medico";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha', $consulta->getFecha());
            $stmt->bindParam(':diagnostico', $consulta->getDiagnostico());
            $stmt->bindParam(':tratamiento', $consulta->getTratamiento());
            $stmt->bindParam(':resultados', $consulta->getResultados());
            $stmt->bindParam(':observaciones', $consulta->getObservaciones());
            $stmt->bindParam(':id_consulta', $consulta->getIdConsulta());
            $stmt->bindParam(':id_medico', $id_medico);

            $resultado = $stmt->execute();
            if ($resultado && $stmt->rowCount() > 0) {
                $this->registrarAuditoria('ACTUALIZAR_CONSULTA', 'consultas', $consulta->getIdConsulta(), $id_medico);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar consulta del médico: " . $e->getMessage());
        }
    }

    /**
     * Elimina una consulta solo si pertenece al medico indicado
     */
    public function eliminarPorMedico($id_consulta, $id_medico) {
        try {
            $sql = "DELETE FROM consultas WHERE id_consulta = :id_consulta AND id_medico = :id_medico";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_consulta', $id_consulta);
            $stmt->bindParam(':id_medico', $id_medico);
            $resultado = $stmt->execute();
            if ($resultado && $stmt->rowCount() > 0) {
                $this->registrarAuditoria('ELIMINAR_CONSULTA', 'consultas', $id_consulta, $id_medico);
                return true;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al eliminar consulta del medico: " . $e->getMessage());
        }
    }
    
    /**
     * Registra acción en auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registro_id, $id_medico = null) {
        try {
            $sql = "INSERT INTO auditoria_logs (id_medico, accion, tabla_afectada, registro_id, detalles) 
                    VALUES (:id_medico, :accion, :tabla, :registro_id, :detalles)";
            
            $detalles = json_encode(['accion' => $accion, 'timestamp' => date('Y-m-d H:i:s')]);
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->bindParam(':accion', $accion);
            $stmt->bindParam(':tabla', $tabla);
            $stmt->bindParam(':registro_id', $registro_id);
            $stmt->bindParam(':detalles', $detalles);
            
            $stmt->execute();
            
        } catch (PDOException $e) {
            // Log error pero no fallar la operación principal
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
}
