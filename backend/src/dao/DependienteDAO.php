<?php
/**
 * Archivo: DependienteDAO.php
 * Descripción: Data Access Object para la entidad Paciente Dependiente
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/DependienteVO.php';

class DependienteDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Inserta un nuevo dependiente en la base de datos
     * Con asignación automática de pediatra
     */
    public function insertar(DependienteVO $dependiente) {
        try {
            $this->db->beginTransaction();

            // Generar un DNI sintético para el dependiente (clave primaria de pacientes)
            $dni_dep = 'DEP' . time() . rand(100,999);

            $sql = "INSERT INTO pacientes
                (dni, nombre, apellidos, email, contrasena_hash, telefono, direccion, fecha_nacimiento, num_seguridad_social, id_medico_general, activo, es_dependiente, dni_tutor, id_pediatra, grupo_sanguineo, alergias, observaciones)
                VALUES (:dni, :nombre, :apellidos, :email, :contrasena_hash, :telefono, :direccion, :fecha_nacimiento, :num_seguridad_social, :id_medico_general, TRUE, TRUE, :dni_tutor, :id_pediatra, :grupo_sanguineo, :alergias, :observaciones)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dni', $dni_dep);
            $stmt->bindValue(':nombre', $dependiente->getNombre());
            $stmt->bindValue(':apellidos', $dependiente->getApellidos());
            // email y otros campos pueden quedar nulos o con valores por defecto
            $stmt->bindValue(':email', $dependiente->getDniTutor() . '+' . $dni_dep . '@dependiente.local');
            $stmt->bindValue(':contrasena_hash', '$2y$10$1ra02Z0q35KcTdfr.5HF1OCWBTgj.vrth1IsghM0TiB2f5V59oJVO');
            $stmt->bindValue(':telefono', null);
            $stmt->bindValue(':direccion', null);
            $stmt->bindValue(':fecha_nacimiento', $dependiente->getFechaNacimiento());
            $stmt->bindValue(':num_seguridad_social', $dependiente->getNumSeguridadSocial());
            $stmt->bindValue(':id_medico_general', null);
            $stmt->bindValue(':dni_tutor', $dependiente->getDniTutor());
            $stmt->bindValue(':id_pediatra', $dependiente->getIdPediatra());
            $stmt->bindValue(':grupo_sanguineo', $dependiente->getGrupoSanguineo());
            $stmt->bindValue(':alergias', $dependiente->getAlergias());
            $stmt->bindValue(':observaciones', $dependiente->getObservaciones());

            $stmt->execute();
            $id_dependiente = $dni_dep;

            // Incrementar contador de dependientes asignados al pediatra
            if ($dependiente->getIdPediatra()) {
                $this->incrementarDependientesAsignados($dependiente->getIdPediatra());
            }

            // Crear perfil de salud inicial (en tabla perfiles_salud)
            $this->crearPerfilSaludInicial($id_dependiente, $dependiente);

            $this->db->commit();
            $this->registrarAuditoria('CREAR_DEPENDIENTE', 'pacientes_dependientes', $id_dependiente, $dependiente->getDniTutor());

            return $id_dependiente;

        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Error al insertar dependiente: " . $e->getMessage());
        }
    }

    /**
     * Crea el perfil de salud inicial del dependiente
     */
    private function crearPerfilSaludInicial($id_dependiente, DependienteVO $dependiente) {
        $sql = "INSERT INTO perfiles_salud (id_paciente, peso_kg, altura_cm, alergias, enfermedades, fecha_creacion)
            VALUES (:id_paciente, NULL, NULL, :alergias, NULL, CURRENT_TIMESTAMP)
            ON CONFLICT (id_paciente) DO NOTHING";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id_paciente', $id_dependiente);
        $stmt->bindValue(':alergias', $dependiente->getAlergias());
        $stmt->execute();
    }

    /**
     * Actualiza un dependiente existente
     */
    public function actualizar(DependienteVO $dependiente) {
        try {
            $sql = "UPDATE pacientes SET
                    nombre = :nombre,
                    apellidos = :apellidos,
                    fecha_nacimiento = :fecha_nacimiento,
                    num_seguridad_social = :num_seguridad_social,
                    grupo_sanguineo = :grupo_sanguineo,
                    alergias = :alergias,
                    observaciones = :observaciones
                    WHERE dni = :id_dependiente AND es_dependiente = TRUE AND activo = TRUE";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nombre', $dependiente->getNombre());
            $stmt->bindValue(':apellidos', $dependiente->getApellidos());
            $stmt->bindValue(':fecha_nacimiento', $dependiente->getFechaNacimiento());
            $stmt->bindValue(':num_seguridad_social', $dependiente->getNumSeguridadSocial());
            $stmt->bindValue(':grupo_sanguineo', $dependiente->getGrupoSanguineo());
            $stmt->bindValue(':alergias', $dependiente->getAlergias());
            $stmt->bindValue(':observaciones', $dependiente->getObservaciones());
            $stmt->bindValue(':id_dependiente', $dependiente->getIdDependiente());

            $resultado = $stmt->execute();

            if ($resultado) {
                $this->registrarAuditoria('ACTUALIZAR_DEPENDIENTE', 'pacientes_dependientes',
                    $dependiente->getIdDependiente(), $dependiente->getDniTutor());
            }

            return $resultado;

        } catch (PDOException $e) {
            throw new Exception("Error al actualizar dependiente: " . $e->getMessage());
        }
    }

    /**
     * Realiza borrado lógico del dependiente
     */
    public function darDeBaja($id_dependiente, $dni_tutor = null) {
        try {
            // Obtener el dependiente para decrementar el contador del pediatra
            $dependiente = $this->obtenerPorId($id_dependiente);

            $sql = "UPDATE pacientes
                    SET activo = FALSE, fecha_baja = CURRENT_TIMESTAMP
                    WHERE dni = :id_dependiente AND es_dependiente = TRUE";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);

            $resultado = $stmt->execute();

            if ($resultado) {
                // Decrementar contador del pediatra
                if ($dependiente && $dependiente->getIdPediatra()) {
                    $this->decrementarDependientesAsignados($dependiente->getIdPediatra());
                }
                $this->registrarAuditoria('BAJA_DEPENDIENTE', 'pacientes_dependientes', $id_dependiente, $dni_tutor);
            }

            return $resultado;

        } catch (PDOException $e) {
            throw new Exception("Error al dar de baja al dependiente: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un dependiente por ID (solo activos)
     */
    public function obtenerPorId($id_dependiente) {
        try {
            $sql = "SELECT p.*, m.nombre as pediatra_nombre, m.apellidos as pediatra_apellidos,
                    t.nombre as tutor_nombre, t.apellidos as tutor_apellidos
                    FROM pacientes p
                    LEFT JOIN medicos m ON p.id_pediatra = m.id_medico
                    LEFT JOIN pacientes t ON p.dni_tutor = t.dni
                    WHERE p.dni = :id_dependiente AND p.es_dependiente = TRUE AND p.activo = TRUE";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);
            $stmt->execute();

            $resultado = $stmt->fetch();

            if ($resultado) {
                if (isset($resultado['dni'])) $resultado['id_dependiente'] = $resultado['dni'];
                return new DependienteVO($resultado);
            }

            return null;

        } catch (PDOException $e) {
            throw new Exception("Error al obtener dependiente: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los dependientes de un tutor
     */
    public function obtenerPorTutor($dni_tutor) {
        try {
            $sql = "SELECT p.*, m.nombre as pediatra_nombre, m.apellidos as pediatra_apellidos,
                    t.nombre as tutor_nombre, t.apellidos as tutor_apellidos
                    FROM pacientes p
                    LEFT JOIN medicos m ON p.id_pediatra = m.id_medico
                    LEFT JOIN pacientes t ON p.dni_tutor = t.dni
                    WHERE p.dni_tutor = :dni_tutor AND p.es_dependiente = TRUE AND p.activo = TRUE
                    ORDER BY p.nombre, p.apellidos";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':dni_tutor', $dni_tutor);
            $stmt->execute();

            $dependientes = [];
            while ($fila = $stmt->fetch()) {
                // Map dni to id_dependiente for compatibility with VO
                if (isset($fila['dni'])) $fila['id_dependiente'] = $fila['dni'];
                $dependientes[] = new DependienteVO($fila);
            }

            return $dependientes;

        } catch (PDOException $e) {
            throw new Exception("Error al obtener dependientes del tutor: " . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los dependientes asignados a un pediatra
     */
    public function obtenerPorPediatra($id_pediatra) {
        try {
            $sql = "SELECT p.*, m.nombre as pediatra_nombre, m.apellidos as pediatra_apellidos,
                    t.nombre as tutor_nombre, t.apellidos as tutor_apellidos
                    FROM pacientes p
                    LEFT JOIN medicos m ON p.id_pediatra = m.id_medico
                    LEFT JOIN pacientes t ON p.dni_tutor = t.dni
                    WHERE p.id_pediatra = :id_pediatra AND p.es_dependiente = TRUE AND p.activo = TRUE
                    ORDER BY p.apellidos, p.nombre";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_pediatra', $id_pediatra);
            $stmt->execute();

            $dependientes = [];
            while ($fila = $stmt->fetch()) {
                if (isset($fila['dni'])) $fila['id_dependiente'] = $fila['dni'];
                $dependientes[] = new DependienteVO($fila);
            }

            return $dependientes;

        } catch (PDOException $e) {
            throw new Exception("Error al obtener dependientes del pediatra: " . $e->getMessage());
        }
    }

    /**
     * Obtiene un pediatra disponible (con menos dependientes asignados)
     */
    public function obtenerPediatraDisponible() {
        try {
            $sql = "SELECT m.*
                    FROM medicos m
                    INNER JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE me.especialidad = 'Pediatra' AND m.activo = TRUE
                    ORDER BY me.dependientes_asignados ASC, RANDOM()
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            $resultado = $stmt->fetch();

            return $resultado ? $resultado : null;

        } catch (PDOException $e) {
            throw new Exception("Error al obtener pediatra disponible: " . $e->getMessage());
        }
    }

    /**
     * Incrementa el contador de dependientes asignados a un pediatra
     */
    public function incrementarDependientesAsignados($id_pediatra) {
        try {
            $sql = "UPDATE medicos_especialistas
                    SET dependientes_asignados = dependientes_asignados + 1
                    WHERE id_medico = :id_medico";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_medico', $id_pediatra);

            return $stmt->execute();

        } catch (PDOException $e) {
            throw new Exception("Error al incrementar dependientes asignados: " . $e->getMessage());
        }
    }

    /**
     * Decrementa el contador de dependientes asignados a un pediatra
     */
    public function decrementarDependientesAsignados($id_pediatra) {
        try {
            $sql = "UPDATE medicos_especialistas
                    SET dependientes_asignados = GREATEST(0, dependientes_asignados - 1)
                    WHERE id_medico = :id_medico";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_medico', $id_pediatra);

            return $stmt->execute();

        } catch (PDOException $e) {
            throw new Exception("Error al decrementar dependientes asignados: " . $e->getMessage());
        }
    }

    /**
     * Obtiene el perfil de salud de un dependiente
     */
    public function obtenerPerfilSalud($id_dependiente) {
        try {

            $sql = "SELECT * FROM perfiles_salud
                    WHERE id_paciente = :id_dependiente";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);
            $stmt->execute();

            return $stmt->fetch();

        } catch (PDOException $e) {
            throw new Exception("Error al obtener perfil de salud: " . $e->getMessage());
        }
    }

    /**
     * Actualiza el perfil de salud de un dependiente
     */
    public function actualizarPerfilSalud($id_dependiente, $datos) {
        try {
            $sql = "UPDATE perfiles_salud SET
                    peso_kg = :peso_kg,
                    altura_cm = :altura_cm,
                    alergias = :alergias,
                    enfermedades = :enfermedades,
                    grupo_sanguineo = :grupo_sanguineo,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_paciente = :id_dependiente";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':peso_kg', $datos['peso_kg'] ?? null);
            $stmt->bindValue(':altura_cm', $datos['altura_cm'] ?? null);
            $stmt->bindValue(':alergias', $datos['alergias'] ?? null);
            $stmt->bindValue(':enfermedades', $datos['enfermedades'] ?? null);
            $stmt->bindValue(':grupo_sanguineo', $datos['grupo_sanguineo'] ?? null);
            $stmt->bindValue(':id_dependiente', $id_dependiente);

            return $stmt->execute();

        } catch (PDOException $e) {
            throw new Exception("Error al actualizar perfil de salud: " . $e->getMessage());
        }
    }

    /**
     * Obtiene las vacunas registradas en la cartilla de un dependiente
     */
    public function obtenerVacunas($id_dependiente) {
        try {
            $sql = "SELECT cv.* FROM cartilla_vacunas cv WHERE cv.id_paciente = :id_dependiente ORDER BY cv.fecha_administracion DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error al obtener cartilla de vacunas: " . $e->getMessage());
        }
    }

    /**
     * Verifica si el tutor tiene acceso al dependiente
     */
    public function verificarAccesoTutor($id_dependiente, $dni_tutor) {
        try {
            $sql = "SELECT COUNT(*) FROM pacientes
                    WHERE dni = :id_dependiente
                    AND dni_tutor = :dni_tutor
                    AND es_dependiente = TRUE
                    AND activo = TRUE";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);
            $stmt->bindValue(':dni_tutor', $dni_tutor);
            $stmt->execute();

            return $stmt->fetchColumn() > 0;

        } catch (PDOException $e) {
            throw new Exception("Error al verificar acceso del tutor: " . $e->getMessage());
        }
    }

    /**
     * Obtiene los antecedentes familiares de un dependiente
     */
    public function obtenerAntecedentes($id_dependiente) {
        try {
            $sql = "SELECT af.*, ec.nombre_patologia
                    FROM antecedentes_familiares af
                    INNER JOIN enfermedades_catalogo ec ON af.id_enfermedad = ec.id_enfermedad
                    WHERE af.id_paciente = :id_dependiente AND af.activo = TRUE
                    ORDER BY af.fecha_registro DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            throw new Exception("Error al obtener antecedentes: " . $e->getMessage());
        }
    }

    /**
     * Obtiene las consultas de un dependiente
     */
    public function obtenerConsultas($id_dependiente, $fecha_desde = null, $fecha_hasta = null) {
        try {
            $sql = "SELECT c.*, m.nombre as medico_nombre, m.apellidos as medico_apellidos,
                    me.especialidad
                    FROM consultas c
                    INNER JOIN medicos m ON c.id_medico = m.id_medico
                    LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                    WHERE c.id_paciente = :id_dependiente";

            if ($fecha_desde) {
                $sql .= " AND c.fecha >= :fecha_desde";
            }
            if ($fecha_hasta) {
                $sql .= " AND c.fecha <= :fecha_hasta";
            }

            $sql .= " ORDER BY c.fecha DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_dependiente', $id_dependiente);

            if ($fecha_desde) {
                $stmt->bindValue(':fecha_desde', $fecha_desde);
            }
            if ($fecha_hasta) {
                $stmt->bindValue(':fecha_hasta', $fecha_hasta);
            }

            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {
            throw new Exception("Error al obtener consultas: " . $e->getMessage());
        }
    }

    /**
     * Registra acción en auditoría
     */
    private function registrarAuditoria($accion, $tabla, $registro_id, $id_paciente = null) {
        try {
            $sql = "INSERT INTO auditoria_logs (id_paciente, accion, tabla_afectada, registro_id, detalles)
                    VALUES (:id_paciente, :accion, :tabla, :registro_id, :detalles)";

            $detalles = json_encode(['accion' => $accion, 'timestamp' => date('Y-m-d H:i:s')]);

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_paciente', $id_paciente);
            $stmt->bindValue(':accion', $accion);
            $stmt->bindValue(':tabla', $tabla);
            $stmt->bindValue(':registro_id', $registro_id);
            $stmt->bindValue(':detalles', $detalles);

            $stmt->execute();

        } catch (PDOException $e) {
            error_log("Error en auditoría: " . $e->getMessage());
        }
    }
}
