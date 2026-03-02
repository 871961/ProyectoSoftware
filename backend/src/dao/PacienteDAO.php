<?php
/**
 * Archivo: PacienteDAO.php
 * Descripción: Data Access Object para la entidad Paciente
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/PacienteVO.php';

class PacienteDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Inserta un nuevo paciente en la base de datos
     */
    public function insertar(PacienteVO $paciente) {
        try {
            $sql = "INSERT INTO pacientes (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social) 
                    VALUES (:dni, :nombre, :apellidos, :email, :contrasena_hash, :telefono, :direccion, :fecha_nacimiento, :num_seguridad_social)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dni', $paciente->getDni());
            $stmt->bindValue(':nombre', $paciente->getNombre());
            $stmt->bindValue(':apellidos', $paciente->getApellidos());
            $stmt->bindValue(':email', $paciente->getEmail());
            $stmt->bindValue(':contrasena_hash', $paciente->getContrasenaHash());
            $stmt->bindValue(':telefono', $paciente->getTelefono());
            $stmt->bindValue(':direccion', $paciente->getDireccion());
            $stmt->bindValue(':fecha_nacimiento', $paciente->getFechaNacimiento());
            $stmt->bindValue(':num_seguridad_social', $paciente->getNumSeguridadSocial());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $dni = $paciente->getDni();
                $this->registrarAuditoria('CREAR_PACIENTE', 'pacientes', $dni, null);
                return $dni;
            }
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al insertar paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un paciente existente
     */
    public function actualizar(PacienteVO $paciente) {
        try {
            $sql = "UPDATE pacientes SET nombre = :nombre, apellidos = :apellidos, email = :email, 
                    telefono = :telefono, direccion = :direccion, fecha_nacimiento = :fecha_nacimiento, 
                    num_seguridad_social = :num_seguridad_social WHERE dni = :dni AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $paciente->getNombre());
            $stmt->bindParam(':apellidos', $paciente->getApellidos());
            $stmt->bindParam(':email', $paciente->getEmail());
            $stmt->bindParam(':telefono', $paciente->getTelefono());
            $stmt->bindParam(':direccion', $paciente->getDireccion());
            $stmt->bindParam(':fecha_nacimiento', $paciente->getFechaNacimiento());
            $stmt->bindParam(':num_seguridad_social', $paciente->getNumSeguridadSocial());
            $stmt->bindParam(':dni', $paciente->getDni());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('ACTUALIZAR_PACIENTE', 'pacientes', $paciente->getDni(), null);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Realiza borrado lógico del paciente (cumplimiento legal GDPR/LOPD)
     */
    public function darDeBaja($dni, $id_admin = null) {
        try {
            $sql = "UPDATE pacientes SET activo = FALSE, fecha_baja = CURRENT_TIMESTAMP WHERE dni = :dni";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dni', $dni);
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $this->registrarAuditoria('BAJA_PACIENTE', 'pacientes', $dni, $id_admin);
            }
            
            return $resultado;
            
        } catch (PDOException $e) {
            throw new Exception("Error al dar de baja al paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un paciente por DNI (solo activos)
     */
    public function obtenerPorId($dni) {
        try {
            $sql = "SELECT * FROM pacientes WHERE dni = :dni AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':dni', $dni);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new PacienteVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener paciente: " . $e->getMessage());
        }
    }
    
    /**
     * Busca un paciente por DNI (solo activos) - Alias de obtenerPorId
     */
    public function buscarPorId($dni) {
        return $this->obtenerPorId($dni);
    }
    
    /**
     * Obtiene todos los pacientes activos
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT * FROM pacientes WHERE activo = TRUE ORDER BY apellidos, nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $pacientes = [];
            while ($fila = $stmt->fetch()) {
                $pacientes[] = new PacienteVO($fila);
            }
            
            return $pacientes;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener pacientes: " . $e->getMessage());
        }
    }
    
    /**
     * Busca pacientes por email (para login y validaciones)
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT * FROM pacientes WHERE email = :email AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new PacienteVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al buscar paciente por email: " . $e->getMessage());
        }
    }

    /**
     * Busca pacientes por email sin filtrar por estado (activo/inactivo)
     * Útil para scripts de mantenimiento o pruebas que necesitan detectar registros existentes
     */
    public function buscarPorEmailInclusoInactivos($email) {
        try {
            $sql = "SELECT * FROM pacientes WHERE email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new PacienteVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al buscar paciente por email (incluyendo inactivos): " . $e->getMessage());
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