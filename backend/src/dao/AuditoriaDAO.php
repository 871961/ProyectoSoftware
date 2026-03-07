<?php
/**
 * Archivo: AuditoriaDAO.php
 * Descripción: Data Access Object para manejar logs de auditoría en PostgreSQL
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/AuditoriaVO.php';

class AuditoriaDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo log de auditoría
     */
    public function insertar(AuditoriaVO $auditoria) {
        try {
            // Validar antes de insertar
            $errores = $auditoria->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $sql = "INSERT INTO auditoria_logs 
                    (accion, tabla_afectada, registro_id, detalles, usuario_responsable, ip_usuario, user_agent) 
                    VALUES (:accion, :tabla_afectada, :registro_id, :detalles, :usuario_responsable, :ip_usuario, :user_agent) 
                    RETURNING id_log";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':accion' => $auditoria->getAccion(),
                ':tabla_afectada' => $auditoria->getTablaAfectada(),
                ':registro_id' => $auditoria->getRegistroId(),
                ':detalles' => $auditoria->getDetalles(),
                ':usuario_responsable' => $auditoria->getUsuarioResponsable(),
                ':ip_usuario' => $auditoria->getIpUsuario(),
                ':user_agent' => $auditoria->getUserAgent()
            ]);

            $id_log = $stmt->fetchColumn();
            $auditoria->setIdLog($id_log);

            return $id_log;

        } catch (Exception $e) {
            error_log("Error al insertar log de auditoría: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene los últimos logs de auditoría
     */
    public function obtenerUltimosLogs($limite = 100) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener últimos logs: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene logs por usuario
     */
    public function obtenerPorUsuario($usuario_responsable, $limite = 50) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE usuario_responsable = :usuario_responsable 
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':usuario_responsable', $usuario_responsable);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener logs por usuario: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene logs por acción específica
     */
    public function obtenerPorAccion($accion, $limite = 50) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE accion = :accion 
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':accion', $accion);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener logs por acción: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene logs por tabla afectada
     */
    public function obtenerPorTabla($tabla_afectada, $limite = 50) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE tabla_afectada = :tabla_afectada 
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':tabla_afectada', $tabla_afectada);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener logs por tabla: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene logs por rango de fechas
     */
    public function obtenerPorRangoFechas($fecha_inicio, $fecha_fin, $limite = 200) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE DATE(fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin 
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':fecha_inicio', $fecha_inicio);
            $stmt->bindValue(':fecha_fin', $fecha_fin);
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener logs por rango de fechas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas de auditoría
     */
    public function obtenerEstadisticas($dias = 30) {
        try {
            $sql = "SELECT 
                        accion,
                        COUNT(*) as total_eventos,
                        COUNT(DISTINCT usuario_responsable) as usuarios_distintos,
                        COUNT(DISTINCT tabla_afectada) as tablas_afectadas,
                        MIN(fecha_hora) as primer_evento,
                        MAX(fecha_hora) as ultimo_evento
                    FROM auditoria_logs 
                    WHERE fecha_hora >= CURRENT_DATE - INTERVAL '$dias days'
                    GROUP BY accion
                    ORDER BY total_eventos DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas de auditoría: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene logs críticos (eliminaciones y fallos de login)
     */
    public function obtenerEventosCriticos($dias = 7) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE (accion = 'DELETE' OR accion = 'LOGIN_FAILED')
                    AND fecha_hora >= CURRENT_DATE - INTERVAL '$dias days'
                    ORDER BY fecha_hora DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener eventos críticos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene actividad por hora del día (para gráficos de análisis)
     */
    public function obtenerActividadPorHora($fecha = null) {
        try {
            $condicion_fecha = $fecha ? "AND DATE(fecha_hora) = :fecha" : "AND DATE(fecha_hora) = CURRENT_DATE";
            
            $sql = "SELECT 
                        EXTRACT(HOUR FROM fecha_hora) as hora,
                        COUNT(*) as cantidad_eventos,
                        COUNT(DISTINCT usuario_responsable) as usuarios_activos
                    FROM auditoria_logs 
                    WHERE 1=1 $condicion_fecha
                    GROUP BY EXTRACT(HOUR FROM fecha_hora)
                    ORDER BY hora";

            $stmt = $this->db->prepare($sql);
            if ($fecha) {
                $stmt->execute([':fecha' => $fecha]);
            } else {
                $stmt->execute();
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener actividad por hora: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca logs por término en detalles
     */
    public function buscarPorDetalles($termino, $limite = 50) {
        try {
            $sql = "SELECT * FROM auditoria_logs 
                    WHERE LOWER(detalles) LIKE LOWER(:termino)
                    OR LOWER(usuario_responsable) LIKE LOWER(:termino)
                    ORDER BY fecha_hora DESC 
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':termino', "%$termino%");
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al buscar logs por detalles: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Limpia logs antiguos (para mantener rendimiento)
     * Se ejecuta habitualmente para logs muy antiguos
     */
    public function limpiarLogsAntiguos($dias_antiguedad = 365) {
        try {
            $this->db->beginTransaction();

            $sql = "DELETE FROM auditoria_logs 
                    WHERE fecha_hora < CURRENT_DATE - INTERVAL '$dias_antiguedad days'
                    AND accion NOT IN ('LOGIN_FAILED', 'DELETE')"; // Conservar eventos críticos

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $eliminados = $stmt->rowCount();

            // Registrar la limpieza
            $this->insertarLogSimple('DELETE', 'auditoria_logs', null, 
                                   "Limpieza automática: eliminados $eliminados logs antiguos");

            $this->db->commit();
            return $eliminados;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al limpiar logs antiguos: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Método auxiliar para insertar logs de sistema sin validaciones complejas
     */
    public function insertarLogSimple($accion, $tabla_afectada, $registro_id, $detalles, $usuario_responsable = 'Sistema') {
        try {
            $sql = "INSERT INTO auditoria_logs 
                    (accion, tabla_afectada, registro_id, detalles, usuario_responsable, ip_usuario) 
                    VALUES (:accion, :tabla_afectada, :registro_id, :detalles, :usuario_responsable, :ip_usuario)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':accion' => $accion,
                ':tabla_afectada' => $tabla_afectada,
                ':registro_id' => $registro_id,
                ':detalles' => $detalles,
                ':usuario_responsable' => $usuario_responsable,
                ':ip_usuario' => $_SERVER['REMOTE_ADDR'] ?? 'localhost'
            ]);

            return true;

        } catch (Exception $e) {
            error_log("Error al insertar log simple: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Exporta logs para cumplimiento legal (GDPR/LOPD)
     */
    public function exportarLogsLegales($fecha_inicio, $fecha_fin) {
        try {
            $sql = "SELECT 
                        fecha_hora,
                        accion,
                        tabla_afectada,
                        registro_id,
                        detalles,
                        usuario_responsable
                    FROM auditoria_logs 
                    WHERE DATE(fecha_hora) BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY fecha_hora ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':fecha_inicio' => $fecha_inicio,
                ':fecha_fin' => $fecha_fin
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al exportar logs legales: " . $e->getMessage());
            throw $e;
        }
    }
}
?>