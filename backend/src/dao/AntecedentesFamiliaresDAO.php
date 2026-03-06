<?php
/**
 * Archivo: AntecedentesFamiliaresDAO.php
 * Descripción: Data Access Object para manejar antecedentes familiares en PostgreSQL
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once 'config/database.php';
require_once 'vo/AntecedentesFamiliaresVO.php';

class AntecedentesFamiliaresDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo antecedente familiar
     */
    public function insertar(AntecedentesFamiliaresVO $antecedente) {
        try {
            // Validar antes de insertar
            $errores = $antecedente->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "INSERT INTO antecedentes_familiares 
                    (id_paciente, id_enfermedad, parentesco, lado_familiar, 
                     edad_diagnostico, notas_adicionales) 
                    VALUES (:id_paciente, :id_enfermedad, :parentesco, :lado_familiar,
                            :edad_diagnostico, :notas_adicionales) 
                    RETURNING id_antecedente";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':id_paciente' => $antecedente->getIdPaciente(),
                ':id_enfermedad' => $antecedente->getIdEnfermedad(),
                ':parentesco' => $antecedente->getParentesco(),
                ':lado_familiar' => $antecedente->getLadoFamiliar(),
                ':edad_diagnostico' => $antecedente->getEdadDiagnostico(),
                ':notas_adicionales' => $antecedente->getNotasAdicionales()
            ]);

            $id_antecedente = $stmt->fetchColumn();
            $antecedente->setIdAntecedente($id_antecedente);

            // Registrar en auditoría
            $this->registrarAuditoria('INSERT', 'antecedentes_familiares', $id_antecedente, 
                                    'Registro de antecedente familiar para paciente ID: ' . $antecedente->getIdPaciente());

            $this->db->commit();
            return $id_antecedente;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al insertar antecedente familiar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene todos los antecedentes familiares de un paciente
     */
    public function obtenerAntecedentesPorPaciente($id_paciente) {
        try {
            $sql = "SELECT af.*, ec.nombre as enfermedad_nombre, 
                           ec.categoria, ec.nivel_gravedad,
                           p.nombre as paciente_nombre, p.apellidos as paciente_apellidos
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    JOIN pacientes p ON af.id_paciente = p.dni
                    WHERE af.id_paciente = :id_paciente AND af.activo = true
                    ORDER BY ec.nivel_gravedad DESC, af.parentesco, ec.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_paciente' => $id_paciente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener antecedentes por paciente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene antecedentes de un paciente con información completa para API
     * (alias para compatibilidad con controladores)
     */
    public function obtenerPorPaciente($dni_paciente) {
        try {
            $sql = "SELECT 
                        af.id_antecedente,
                        af.id_paciente,
                        af.id_enfermedad,
                        af.parentesco,
                        af.lado_familiar,
                        af.edad_diagnostico,
                        af.notas_adicionales,
                        af.fecha_registro,
                        af.activo,
                        ec.nombre_patologia
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    WHERE af.id_paciente = :dni_paciente 
                      AND af.activo = TRUE
                    ORDER BY af.parentesco, ec.nombre_patologia";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':dni_paciente' => $dni_paciente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener antecedentes por paciente (DNI): " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene antecedentes por enfermedad específica
     */
    public function obtenerPorEnfermedad($id_enfermedad) {
        try {
            $sql = "SELECT af.*, ec.nombre as enfermedad_nombre,
                           p.nombre as paciente_nombre, p.apellidos as paciente_apellidos
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    JOIN pacientes p ON af.id_paciente = p.dni
                    WHERE af.id_enfermedad = :id_enfermedad 
                    AND af.activo = true AND p.activo = true
                    ORDER BY af.parentesco, p.apellidos, p.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_enfermedad' => $id_enfermedad]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener antecedentes por enfermedad: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene un resumen de riesgo genético por paciente
     */
    public function obtenerResumenRiesgo($id_paciente) {
        try {
            $sql = "SELECT 
                        ec.categoria,
                        COUNT(*) as cantidad_antecedentes,
                        STRING_AGG(DISTINCT af.parentesco, ', ') as parentescos,
                        CASE 
                            WHEN COUNT(CASE WHEN af.parentesco IN ('padre', 'madre', 'hermano', 'hermana') THEN 1 END) > 0 THEN 'ALTO'
                            WHEN COUNT(CASE WHEN af.parentesco IN ('abuelo_paterno', 'abuela_paterna', 'abuelo_materno', 'abuela_materna', 'tio', 'tia') THEN 1 END) > 0 THEN 'MEDIO'
                            ELSE 'BAJO'
                        END as nivel_riesgo_categoria
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    WHERE af.id_paciente = :id_paciente AND af.activo = true
                    GROUP BY ec.categoria
                    ORDER BY nivel_riesgo_categoria DESC, cantidad_antecedentes DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_paciente' => $id_paciente]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener resumen de riesgo: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Busca pacientes con antecedentes similares (para estudios epidemiológicos)
     */
    public function buscarAntecedentesComunes($id_enfermedad, $parentesco = null) {
        try {
            $condicion_parentesco = $parentesco ? "AND af.parentesco = :parentesco" : "";
            
            $sql = "SELECT af.*, ec.nombre as enfermedad_nombre,
                           p.nombre as paciente_nombre, p.apellidos as paciente_apellidos,
                           p.fecha_nacimiento,
                           EXTRACT(YEAR FROM AGE(p.fecha_nacimiento)) as edad_actual
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    JOIN pacientes p ON af.id_paciente = p.id_paciente
                    WHERE af.id_enfermedad = :id_enfermedad
                    AND af.activo = true AND p.activo = true
                    $condicion_parentesco
                    ORDER BY p.fecha_nacimiento DESC";

            $stmt = $this->db->prepare($sql);
            $params = [':id_enfermedad' => $id_enfermedad];
            if ($parentesco) {
                $params[':parentesco'] = $parentesco;
            }
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al buscar antecedentes comunes: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza un antecedente familiar
     */
    public function actualizar(AntecedentesFamiliaresVO $antecedente) {
        try {
            // Validar antes de actualizar
            $errores = $antecedente->validar();
            if (!empty($errores)) {
                throw new Exception("Datos inválidos: " . implode(", ", $errores));
            }

            $this->db->beginTransaction();

            $sql = "UPDATE antecedentes_familiares 
                    SET id_enfermedad = :id_enfermedad,
                        parentesco = :parentesco,
                        lado_familiar = :lado_familiar,
                        edad_diagnostico = :edad_diagnostico,
                        notas_adicionales = :notas_adicionales
                    WHERE id_antecedente = :id_antecedente AND activo = true";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([
                ':id_enfermedad' => $antecedente->getIdEnfermedad(),
                ':parentesco' => $antecedente->getParentesco(),
                ':lado_familiar' => $antecedente->getLadoFamiliar(),
                ':edad_diagnostico' => $antecedente->getEdadDiagnostico(),
                ':notas_adicionales' => $antecedente->getNotasAdicionales(),
                ':id_antecedente' => $antecedente->getIdAntecedente()
            ]);

            // Registrar en auditoría
            $this->registrarAuditoria('UPDATE', 'antecedentes_familiares', $antecedente->getIdAntecedente(), 
                                    'Actualización de antecedente familiar');

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error al actualizar antecedente familiar: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene estadísticas de antecedentes familiares
     */
    public function obtenerEstadisticas() {
        try {
            $sql = "SELECT 
                        ec.categoria,
                        ec.nombre as enfermedad,
                        COUNT(*) as total_casos,
                        af.parentesco,
                        COUNT(af.parentesco) as casos_por_parentesco
                    FROM antecedentes_familiares af
                    JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    WHERE af.activo = true
                    GROUP BY ec.categoria, ec.nombre, af.parentesco
                    ORDER BY total_casos DESC, ec.categoria";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Borrado lógico (desactivar antecedente)
     */
    public function darDeBaja($id_antecedente, $id_responsable) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE antecedentes_familiares 
                    SET activo = false 
                    WHERE id_antecedente = :id_antecedente";

            $stmt = $this->db->prepare($sql);
            $resultado = $stmt->execute([':id_antecedente' => $id_antecedente]);

            // Registrar en auditoría
            $this->registrarAuditoria('DELETE', 'antecedentes_familiares', $id_antecedente, 
                                    'Borrado lógico de antecedente familiar', $id_responsable);

            $this->db->commit();
            return $resultado;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en borrado lógico de antecedente: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Eliminar (desactivar) un antecedente - Alias para compatibilidad con controladores
     */
    public function eliminar($id_antecedente) {
        return $this->darDeBaja($id_antecedente, $_SESSION['user_id'] ?? 'Sistema');
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