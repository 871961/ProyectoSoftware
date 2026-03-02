<?php
/**
 * Archivo: MedicoDAO.php
 * Descripción: Data Access Object para la entidad Médico
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/MedicoVO.php';

class MedicoDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Inserta un nuevo médico (solo por administrador)
     */
    public function insertar(MedicoVO $medico, $id_admin = null) {
        try {
            $sql = "INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, especialidad) 
                    VALUES (:nombre, :apellidos, :email, :contrasena_hash, :telefono, :direccion, :num_colegiado, :especialidad)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $medico->getNombre());
            $stmt->bindValue(':apellidos', $medico->getApellidos());
            $stmt->bindValue(':email', $medico->getEmail());
            $stmt->bindValue(':contrasena_hash', $medico->getContrasenaHash());
            $stmt->bindValue(':telefono', $medico->getTelefono());
            $stmt->bindValue(':direccion', $medico->getDireccion());
            $stmt->bindValue(':num_colegiado', $medico->getNumColegiado());
            $stmt->bindValue(':especialidad', $medico->getEspecialidad());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $medico->setIdMedico($this->db->lastInsertId());
                $this->registrarAuditoria('CREAR_MEDICO', 'medicos', $medico->getIdMedico(), $id_admin);
                return true;
            }
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al insertar médico: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un médico existente
     */
    public function actualizar(MedicoVO $medico, $id_admin = null) {
        try {
            $sql = "UPDATE medicos SET nombre = :nombre, apellidos = :apellidos, email = :email, 
                    telefono = :telefono, direccion = :direccion, num_colegiado = :num_colegiado, 
                    especialidad = :especialidad WHERE id_medico = :id_medico AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $medico->getNombre());
            $stmt->bindParam(':apellidos', $medico->getApellidos());
            $stmt->bindParam(':email', $medico->getEmail());
            $stmt->bindParam(':telefono', $medico->getTelefono());
            $stmt->bindParam(':direccion', $medico->getDireccion());
            $stmt->bindParam(':num_colegiado', $medico->getNumColegiado());
            $stmt->bindParam(':especialidad', $medico->getEspecialidad());
            $stmt->bindParam(':id_medico', $medico->getIdMedico());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('ACTUALIZAR_MEDICO', 'medicos', $medico->getIdMedico(), $id_admin);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar médico: " . $e->getMessage());
        }
    }
    
    /**
     * Realiza borrado lógico del médico
     */
    public function darDeBaja($id_medico, $id_admin = null) {
        try {
            $sql = "UPDATE medicos SET activo = FALSE, fecha_baja = CURRENT_TIMESTAMP WHERE id_medico = :id_medico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('BAJA_MEDICO', 'medicos', $id_medico, $id_admin);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al dar de baja al médico: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un médico por ID (solo activos)
     */
    public function obtenerPorId($id_medico) {
        try {
            $sql = "SELECT * FROM medicos WHERE id_medico = :id_medico AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new MedicoVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener médico: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los médicos (activos e inactivos)
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT * FROM medicos ORDER BY activo DESC, apellidos, nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $medicos = [];
            while ($fila = $stmt->fetch()) {
                $medicos[] = new MedicoVO($fila);
            }
            
            return $medicos;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener médicos: " . $e->getMessage());
        }
    }
    
    /**
     * Reactiva un médico dado de baja (borrado lógico reverso)
     */
    public function darDeAlta($id_medico, $id_admin = null) {
        try {
            $sql = "UPDATE medicos SET activo = TRUE, fecha_baja = NULL WHERE id_medico = :id_medico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('ALTA_MEDICO', 'medicos', $id_medico, $id_admin);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al dar de alta al médico: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene médicos por especialidad
     */
    public function obtenerPorEspecialidad($especialidad) {
        try {
            $sql = "SELECT * FROM medicos WHERE especialidad = :especialidad AND activo = TRUE ORDER BY apellidos, nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':especialidad', $especialidad);
            $stmt->execute();
            
            $medicos = [];
            while ($fila = $stmt->fetch()) {
                $medicos[] = new MedicoVO($fila);
            }
            
            return $medicos;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener médicos por especialidad: " . $e->getMessage());
        }
    }
    
    /**
     * Busca médico por email (para login)
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT * FROM medicos WHERE email = :email AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new MedicoVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al buscar médico por email: " . $e->getMessage());
        }
    }
    
    /**
     * Registra acción en auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registro_id, $id_admin = null) {
        try {
            $sql = "INSERT INTO auditoria_logs (id_admin, accion, tabla_afectada, registro_id, detalles) 
                    VALUES (:id_admin, :accion, :tabla, :registro_id, :detalles)";
            
            $detalles = json_encode(['accion' => $accion, 'timestamp' => date('Y-m-d H:i:s')]);
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin);
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