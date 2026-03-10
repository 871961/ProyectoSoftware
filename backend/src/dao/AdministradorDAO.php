<?php
/**
 * Archivo: AdministradorDAO.php
 * Descripción: Data Access Object para la entidad Administrador
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/AdministradorVO.php';

class AdministradorDAO {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Autentica un administrador
     */
    public function autenticar($email, $contrasena) {
        try {
            $sql = "SELECT * FROM administradores WHERE email = :email AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado && password_verify($contrasena, $resultado['contrasena_hash'])) {
                $this->registrarAuditoria('LOGIN_ADMIN', 'administradores', $resultado['id_admin'], $resultado['id_admin']);
                return new AdministradorVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al autenticar administrador: " . $e->getMessage());
        }
    }
    
    /**
     * Inserta un nuevo administrador
     */
    public function insertar(AdministradorVO $admin) {
        try {
            $sql = "INSERT INTO administradores (nombre, apellidos, email, contrasena_hash) 
                    VALUES (:nombre, :apellidos, :email, :contrasena_hash)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $admin->getNombre());
            $stmt->bindValue(':apellidos', $admin->getApellidos());
            $stmt->bindValue(':email', $admin->getEmail());
            $stmt->bindValue(':contrasena_hash', $admin->getContrasenaHash());
            
            $resultado = $stmt->execute();
            
            if ($resultado) {
                $admin->setIdAdmin($this->db->lastInsertId());
                $this->registrarAuditoria('CREAR_ADMIN', 'administradores', $admin->getIdAdmin(), null);
                return true;
            }
            return false;
            
        } catch (PDOException $e) {
            throw new Exception("Error al insertar administrador: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un administrador por ID
     */
    public function obtenerPorId($id_admin) {
        try {
            $sql = "SELECT * FROM administradores WHERE id_admin = :id_admin AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_admin', $id_admin);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new AdministradorVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener administrador: " . $e->getMessage());
        }
    }
    
    /**
     * Busca administrador por email
     */
    public function buscarPorEmail($email) {
        try {
            $sql = "SELECT * FROM administradores WHERE email = :email AND activo = TRUE";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            $resultado = $stmt->fetch();
            
            if ($resultado) {
                return new AdministradorVO($resultado);
            }
            
            return null;
            
        } catch (PDOException $e) {
            throw new Exception("Error al buscar administrador por email: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los administradores activos
     */
    public function obtenerTodos() {
        try {
            $sql = "SELECT * FROM administradores WHERE activo = TRUE ORDER BY id_admin";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $rows = $stmt->fetchAll();
            $result = [];
            foreach ($rows as $r) {
                $result[] = new AdministradorVO($r);
            }
            return $result;

        } catch (PDOException $e) {
            throw new Exception("Error al obtener administradores: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene estadísticas del sistema para el panel de administración
     */
    public function obtenerEstadisticas() {
        try {
            $estadisticas = [];
            
            // Total pacientes activos
            $sql = "SELECT COUNT(*) as total FROM pacientes WHERE activo = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $estadisticas['pacientes_activos'] = $stmt->fetch()['total'];
            
            // Total médicos activos
            $sql = "SELECT COUNT(*) as total FROM medicos WHERE activo = TRUE";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $estadisticas['medicos_activos'] = $stmt->fetch()['total'];
            
            // Total consultas este mes
            $sql = "SELECT COUNT(*) as total FROM consultas WHERE DATE_PART('month', fecha) = DATE_PART('month', CURRENT_DATE) AND DATE_PART('year', fecha) = DATE_PART('year', CURRENT_DATE)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $estadisticas['consultas_mes'] = $stmt->fetch()['total'];
            
            // Especialidades más demandadas
            $sql = "SELECT me.especialidad, COUNT(c.id_consulta) as total_consultas 
                    FROM medicos_especialistas me
                    INNER JOIN medicos m ON me.id_medico = m.id_medico
                    LEFT JOIN consultas c ON m.id_medico = c.id_medico 
                    WHERE m.activo = TRUE 
                    GROUP BY me.especialidad 
                    ORDER BY total_consultas DESC 
                    LIMIT 5";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $estadisticas['especialidades_demandadas'] = $stmt->fetchAll();
            
            return $estadisticas;
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener estadísticas: " . $e->getMessage());
        }
    }
    
    /**
     * Obtiene logs de auditoría recientes
     */
    public function obtenerLogsRecientes($limite = 50) {
        try {
            $sql = "SELECT al.*, 
                           COALESCE(p.nombre || ' ' || p.apellidos, 
                                   m.nombre || ' ' || m.apellidos, 
                                   a.nombre || ' ' || a.apellidos, 'Sistema') as usuario_responsable
                    FROM auditoria_logs al
                    LEFT JOIN pacientes p ON al.id_paciente = p.dni
                    LEFT JOIN medicos m ON al.id_medico = m.id_medico
                    LEFT JOIN administradores a ON al.id_admin = a.id_admin
                    ORDER BY al.fecha_hora DESC
                    LIMIT :limite";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            throw new Exception("Error al obtener logs de auditoría: " . $e->getMessage());
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