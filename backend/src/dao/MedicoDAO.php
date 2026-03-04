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
     * Con jerarquía: general o especialista
     */
    public function insertar(MedicoVO $medico, $id_admin = null) {
        try {
            $this->db->beginTransaction();
            
            // 1. Insertar en tabla medicos (entidad padre)
            $sql = "INSERT INTO medicos (nombre, apellidos, email, contrasena_hash, telefono, direccion, num_colegiado, tipo_medico) 
                    VALUES (:nombre, :apellidos, :email, :contrasena_hash, :telefono, :direccion, :num_colegiado, :tipo_medico)
                    RETURNING id_medico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $medico->getNombre());
            $stmt->bindValue(':apellidos', $medico->getApellidos());
            $stmt->bindValue(':email', $medico->getEmail());
            $stmt->bindValue(':contrasena_hash', $medico->getContrasenaHash());
            $stmt->bindValue(':telefono', $medico->getTelefono());
            $stmt->bindValue(':direccion', $medico->getDireccion());
            $stmt->bindValue(':num_colegiado', $medico->getNumColegiado());
            $stmt->bindValue(':tipo_medico', $medico->getTipoMedico());
            
            $stmt->execute();
            $id_medico = $stmt->fetchColumn();
            $medico->setIdMedico($id_medico);
            
            // 2. Insertar en tabla correspondiente según tipo
            if ($medico->esGeneral()) {
                // Insertar en medicos_generales
                $sql_general = "INSERT INTO medicos_generales (id_medico) VALUES (:id_medico)";
                $stmt_general = $this->db->prepare($sql_general);
                $stmt_general->bindValue(':id_medico', $id_medico);
                $stmt_general->execute();
            } else if ($medico->esEspecialista()) {
                // Insertar en medicos_especialistas
                $sql_especialista = "INSERT INTO medicos_especialistas (id_medico, especialidad) 
                                    VALUES (:id_medico, :especialidad)";
                $stmt_especialista = $this->db->prepare($sql_especialista);
                $stmt_especialista->bindValue(':id_medico', $id_medico);
                $stmt_especialista->bindValue(':especialidad', $medico->getEspecialidad());
                $stmt_especialista->execute();
            }
            
            $this->db->commit();
            $this->registrarAuditoria('CREAR_MEDICO', 'medicos', $id_medico, $id_admin);
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Error al insertar médico: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un médico existente
     */
    public function actualizar(MedicoVO $medico, $id_admin = null) {
        try {
            $this->db->beginTransaction();
            
            // Actualizar tabla principal medicos
            $sql = "UPDATE medicos SET nombre = :nombre, apellidos = :apellidos, email = :email, 
                    telefono = :telefono, direccion = :direccion, num_colegiado = :num_colegiado, 
                    tipo_medico = :tipo_medico WHERE id_medico = :id_medico AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $medico->getNombre());
            $stmt->bindParam(':apellidos', $medico->getApellidos());
            $stmt->bindParam(':email', $medico->getEmail());
            $stmt->bindParam(':telefono', $medico->getTelefono());
            $stmt->bindParam(':direccion', $medico->getDireccion());
            $stmt->bindParam(':num_colegiado', $medico->getNumColegiado());
            $stmt->bindParam(':tipo_medico', $medico->getTipoMedico());
            $stmt->bindParam(':id_medico', $medico->getIdMedico());
            
            $stmt->execute();
            
            // Actualizar especialidad si es especialista
            if ($medico->esEspecialista()) {
                $sql_esp = "UPDATE medicos_especialistas SET especialidad = :especialidad 
                           WHERE id_medico = :id_medico";
                $stmt_esp = $this->db->prepare($sql_esp);
                $stmt_esp->bindParam(':especialidad', $medico->getEspecialidad());
                $stmt_esp->bindParam(':id_medico', $medico->getIdMedico());
                $stmt_esp->execute();
            }
            
            $this->db->commit();
            $this->registrarAuditoria('ACTUALIZAR_MEDICO', 'medicos', $medico->getIdMedico(), $id_admin);
            return true;
            
        } catch (PDOException $e) {
            $this->db->rollBack();
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
     * Con JOIN a tablas medicos_generales o medicos_especialistas
     */
    public function obtenerPorId($id_medico) {
        try {
            $sql = "SELECT m.*, me.especialidad 
                    FROM medicos m
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE m.id_medico = :id_medico AND m.activo = TRUE";
            
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
     * Con JOIN para obtener especialidad si es especialista
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT m.*, me.especialidad 
                    FROM medicos m
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    ORDER BY m.activo DESC, m.apellidos, m.nombre";
            
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
            $sql = "SELECT m.*, me.especialidad 
                    FROM medicos m
                    INNER JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE me.especialidad = :especialidad AND m.activo = TRUE 
                    ORDER BY m.apellidos, m.nombre";
            
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
            $sql = "SELECT m.*, me.especialidad 
                    FROM medicos m
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE m.email = :email AND m.activo = TRUE";
            
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
     * Obtiene solo médicos generales activos
     */
    public function obtenerMedicosGenerales() {
        try {
            $sql = "SELECT m.* 
                    FROM medicos m
                    INNER JOIN medicos_generales mg ON m.id_medico = mg.id_medico
                    WHERE m.activo = TRUE 
                    ORDER BY m.apellidos, m.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $medicos = [];
            while ($fila = $stmt->fetch()) {
                $medicos[] = new MedicoVO($fila);
            }
            
            return $medicos;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener médicos generales: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un médico general aleatorio con menos pacientes asignados
     * Para asignación automática de nuevos pacientes
     */
    public function obtenerMedicoGeneralDisponible() {
        try {
            $sql = "SELECT m.* 
                    FROM medicos m
                    INNER JOIN medicos_generales mg ON m.id_medico = mg.id_medico
                    WHERE m.activo = TRUE 
                    ORDER BY mg.pacientes_asignados ASC, RANDOM()
                    LIMIT 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new MedicoVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener médico general disponible: " . $e->getMessage());
        }
    }
    
    /**
     * Incrementa el contador de pacientes asignados a un médico general
     */
    public function incrementarPacientesAsignados($id_medico) {
        try {
            $sql = "UPDATE medicos_generales 
                    SET pacientes_asignados = pacientes_asignados + 1 
                    WHERE id_medico = :id_medico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            throw new Exception("Error al incrementar pacientes asignados: " . $e->getMessage());
        }
    }
    
    /**
     * Decrementa el contador de pacientes asignados a un médico general
     */
    public function decrementarPacientesAsignados($id_medico) {
        try {
            $sql = "UPDATE medicos_generales 
                    SET pacientes_asignados = GREATEST(0, pacientes_asignados - 1)
                    WHERE id_medico = :id_medico";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_medico', $id_medico);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            throw new Exception("Error al decrementar pacientes asignados: " . $e->getMessage());
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