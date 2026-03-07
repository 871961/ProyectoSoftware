<?php
/**
 * Archivo: EnfermedadesCatalogoDAO.php
 * Descripción: Data Access Object para el catálogo de enfermedades
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/EnfermedadesCatalogoVO.php';

class EnfermedadesCatalogoDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtiene todas las enfermedades activas (para dropdowns)
     */
    public function listarTodas() {
        try {
            $sql = "SELECT * FROM enfermedades_catalogo 
                    WHERE activo = true 
                    ORDER BY categoria, nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al listar enfermedades: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene enfermedades por categoría
     */
    public function obtenerPorCategoria($categoria) {
        try {
            $sql = "SELECT * FROM enfermedades_catalogo 
                    WHERE categoria = :categoria AND activo = true 
                    ORDER BY nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':categoria' => $categoria]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener enfermedades por categoría: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca enfermedades por nombre o síntomas
     */
    public function buscar($termino) {
        try {
            $sql = "SELECT * FROM enfermedades_catalogo 
                    WHERE (LOWER(nombre) LIKE LOWER(:termino) 
                           OR LOWER(descripcion) LIKE LOWER(:termino)
                           OR LOWER(sintomas_comunes) LIKE LOWER(:termino))
                    AND activo = true 
                    ORDER BY 
                        CASE WHEN LOWER(nombre) LIKE LOWER(:termino_exacto) THEN 1 ELSE 2 END,
                        nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':termino' => "%$termino%",
                ':termino_exacto' => $termino
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al buscar enfermedades: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene enfermedades hereditarias
     */
    public function obtenerHereditarias() {
        try {
            $sql = "SELECT * FROM enfermedades_catalogo 
                    WHERE es_hereditaria = true AND activo = true 
                    ORDER BY nivel_gravedad DESC, categoria, nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener enfermedades hereditarias: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene una enfermedad por ID
     */
    public function obtenerPorId($id_enfermedad) {
        try {
            $sql = "SELECT * FROM enfermedades_catalogo 
                    WHERE id_enfermedad = :id_enfermedad AND activo = true";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_enfermedad' => $id_enfermedad]);

            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (Exception $e) {
            error_log("Error al obtener enfermedad por ID: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas del catálogo
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        categoria,
                        COUNT(*) as total_enfermedades,
                        COUNT(CASE WHEN es_hereditaria = true THEN 1 END) as hereditarias,
                        COUNT(CASE WHEN requiere_seguimiento = true THEN 1 END) as requieren_seguimiento,
                        COUNT(CASE WHEN nivel_gravedad = 'critica' THEN 1 END) as criticas,
                        COUNT(CASE WHEN nivel_gravedad = 'grave' THEN 1 END) as graves
                    FROM enfermedades_catalogo 
                    WHERE activo = true
                    GROUP BY categoria
                    ORDER BY total_enfermedades DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Inserta una nueva enfermedad (solo para administradores)
     */
    public function insertar(EnfermedadesCatalogoVO $enfermedad) {
        try {
            // Validar antes de insertar
            $errores = $enfermedad->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO enfermedades_catalogo 
                    (nombre, descripcion, categoria, codigo_cie10, sintomas_comunes, 
                     factores_riesgo, nivel_gravedad, es_hereditaria, requiere_seguimiento) 
                    VALUES (:nombre, :descripcion, :categoria, :codigo_cie10, :sintomas_comunes,
                            :factores_riesgo, :nivel_gravedad, :es_hereditaria, :requiere_seguimiento) 
                    RETURNING id_enfermedad";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':nombre' => $enfermedad->getNombre(),
                ':descripcion' => $enfermedad->getDescripcion(),
                ':categoria' => $enfermedad->getCategoria(),
                ':codigo_cie10' => $enfermedad->getCodigoCie10(),
                ':sintomas_comunes' => $enfermedad->getSintomasComunes(),
                ':factores_riesgo' => $enfermedad->getFactoresRiesgo(),
                ':nivel_gravedad' => $enfermedad->getNivelGravedad(),
                ':es_hereditaria' => $enfermedad->getEsHereditaria(),
                ':requiere_seguimiento' => $enfermedad->getRequiereSeguimiento()
            ]);

            $id_enfermedad = $stmt->fetchColumn();
            $enfermedad->setIdEnfermedad($id_enfermedad);

            // Registrar en auditoría
            $this->registrarAuditoria('INSERT', 'enfermedades_catalogo', $id_enfermedad, 
                                    'Nueva enfermedad agregada: ' . $enfermedad->getNombre());

            $this->db->commit();
            return $id_enfermedad;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al insertar enfermedad: " . $e->getMessage());
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
        }
    }
}
?>