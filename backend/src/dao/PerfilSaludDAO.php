<?php
/**
 * Archivo: PerfilSaludDAO.php
 * Descripción: Data Access Object para manejar perfiles de salud en PostgreSQL
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/PerfilSaludVO.php';

class PerfilSaludDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo perfil de salud
     */
    public function insertar(PerfilSaludVO $perfil) {
        try {
            // Validar antes de insertar
            $errores = $perfil->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO perfiles_salud 
                    (id_paciente, peso_kg, altura_cm, tipo_sangre, alergias, 
                     medicamentos_actuales, observaciones_medicas) 
                    VALUES (:id_paciente, :peso_kg, :altura_cm, :tipo_sangre, :alergias, 
                            :medicamentos_actuales, :observaciones_medicas) 
                    RETURNING id_perfil";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_paciente' => $perfil->getIdPaciente(),
                ':peso_kg' => $perfil->getPesoKg(),
                ':altura_cm' => $perfil->getAlturaCm(),
                ':tipo_sangre' => $perfil->getTipoSangre(),
                ':alergias' => $perfil->getAlergias(),
                ':medicamentos_actuales' => $perfil->getMedicamentosActuales(),
                ':observaciones_medicas' => $perfil->getObservacionesMedicas()
            ]);

            $id_perfil = $stmt->fetchColumn();
            $perfil->setIdPerfil($id_perfil);

            // Registrar en auditoría
            $this->registrarAuditoria('INSERT', 'perfiles_salud', $id_perfil, 
                                    'Creación de perfil de salud para paciente ID: ' . $perfil->getIdPaciente());

            $this->db->commit();
            return $id_perfil;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al insertar perfil de salud: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el perfil de salud de un paciente específico
     */
    public function obtenerPorIdPaciente($id_paciente) {
        try {
            $sql = "SELECT ps.*, p.nombre, p.apellidos 
                    FROM perfiles_salud ps 
                    LEFT JOIN pacientes p ON ps.id_paciente = p.id_paciente 
                    WHERE ps.id_paciente = :id_paciente AND ps.activo = true 
                    ORDER BY ps.fecha_actualizacion DESC 
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_paciente' => $id_paciente]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener perfil por ID paciente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene un perfil por su ID
     */
    public function obtenerPorId($id_perfil) {
        try {
            $sql = "SELECT ps.*, p.nombre, p.apellidos 
                    FROM perfiles_salud ps 
                    LEFT JOIN pacientes p ON ps.id_paciente = p.id_paciente 
                    WHERE ps.id_perfil = :id_perfil AND ps.activo = true";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_perfil' => $id_perfil]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener perfil por ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza un perfil de salud existente
     */
    public function actualizar(PerfilSaludVO $perfil) {
        try {
            // Validar antes de actualizar
            $errores = $perfil->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "UPDATE perfiles_salud SET 
                        peso_kg = :peso_kg,
                        altura_cm = :altura_cm,
                        tipo_sangre = :tipo_sangre,
                        alergias = :alergias,
                        medicamentos_actuales = :medicamentos_actuales,
                        observaciones_medicas = :observaciones_medicas,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_perfil = :id_perfil AND activo = true";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':peso_kg' => $perfil->getPesoKg(),
                ':altura_cm' => $perfil->getAlturaCm(),
                ':tipo_sangre' => $perfil->getTipoSangre(),
                ':alergias' => $perfil->getAlergias(),
                ':medicamentos_actuales' => $perfil->getMedicamentosActuales(),
                ':observaciones_medicas' => $perfil->getObservacionesMedicas(),
                ':id_perfil' => $perfil->getIdPerfil()
            ]);

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'perfiles_salud', $perfil->getIdPerfil(), 
                                    'Actualización de perfil de salud');

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar perfil de salud: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza solo el peso y altura (método específico para consultas rápidas)
     */
    public function actualizarPesoAltura($id_perfil, $peso_kg, $altura_cm, $id_responsable) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE perfiles_salud SET 
                        peso_kg = :peso_kg,
                        altura_cm = :altura_cm,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_perfil = :id_perfil AND activo = true";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':peso_kg' => $peso_kg,
                ':altura_cm' => $altura_cm,
                ':id_perfil' => $id_perfil
            ]);

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'perfiles_salud', $id_perfil, 
                                    "Actualización de peso ($peso_kg kg) y altura ($altura_cm cm)", $id_responsable);

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar peso y altura: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todos los perfiles de salud activos
     */
    public function obtenerTodos($limite = 100) {
        try {
            $sql = "SELECT ps.*, p.nombre, p.apellidos, p.email 
                    FROM perfiles_salud ps 
                    LEFT JOIN pacientes p ON ps.id_paciente = p.id_paciente 
                    WHERE ps.activo = true 
                    ORDER BY ps.fecha_actualizacion DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener todos los perfiles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca perfiles por tipo de sangre (útil para emergencias)
     */
    public function buscarPorTipoSangre($tipo_sangre) {
        try {
            $sql = "SELECT ps.*, p.nombre, p.apellidos, p.telefono 
                    FROM perfiles_salud ps 
                    JOIN pacientes p ON ps.id_paciente = p.id_paciente 
                    WHERE ps.tipo_sangre = :tipo_sangre 
                    AND ps.activo = true AND p.activo = true 
                    ORDER BY p.nombre, p.apellidos";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':tipo_sangre' => $tipo_sangre]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al buscar por tipo de sangre: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas de perfiles de salud
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_perfiles,
                        AVG(peso_kg) as peso_promedio,
                        AVG(altura_cm) as altura_promedio,
                        tipo_sangre,
                        COUNT(*) as cantidad_tipo_sangre
                    FROM perfiles_salud 
                    WHERE activo = true AND peso_kg IS NOT NULL AND altura_cm IS NOT NULL
                    GROUP BY tipo_sangre
                    ORDER BY cantidad_tipo_sangre DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Borrado lógico (desactivar perfil - para cumplimiento GDPR/LOPD)
     */
    public function darDeBaja($id_perfil, $id_responsable) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE perfiles_salud 
                    SET activo = false, fecha_actualizacion = CURRENT_TIMESTAMP 
                    WHERE id_perfil = :id_perfil";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([':id_perfil' => $id_perfil]);

            // Registrar en auditoría
            $this->registrarAuditoria('DELETE', 'perfiles_salud', $id_perfil, 
                                    'Borrado lógico de perfil de salud (cumplimiento GDPR/LOPD)', $id_responsable);

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en borrado lógico de perfil: " . $e->getMessage());
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