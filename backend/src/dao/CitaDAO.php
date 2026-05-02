<?php
/**
 * Archivo: CitaDAO.php
 * Descripcion: Data Access Object para el módulo de citas médicas
 * Fecha: Mayo 2026
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../vo/CitaVO.php';

class CitaDAO {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function insertar(CitaVO $cita) {
        $errores = $cita->validar();
        if (!empty($errores)) {
            throw new Exception("Datos inválidos: " . implode(", ", $errores));
        }

        $sql = "INSERT INTO citas (id_paciente, id_medico, fecha_hora, motivo, estado, tipo)
                VALUES (:id_paciente, :id_medico, :fecha_hora, :motivo, :estado, :tipo)
                RETURNING id_cita";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_paciente' => $cita->getIdPaciente(),
            ':id_medico'   => $cita->getIdMedico(),
            ':fecha_hora'  => $cita->getFechaHora(),
            ':motivo'      => $cita->getMotivo(),
            ':estado'      => $cita->getEstado(),
            ':tipo'        => $cita->getTipo(),
        ]);

        $id = $stmt->fetchColumn();
        $cita->setIdCita($id);
        return $id;
    }

    public function obtenerPorPaciente($id_paciente, $solo_activas = false) {
        $cond = $solo_activas
            ? "AND c.activo = true AND c.estado IN ('Pendiente', 'Confirmada')"
            : "AND c.activo = true";

        $sql = "SELECT c.*,
                       m.nombre AS medico_nombre, m.apellidos AS medico_apellidos,
                       m.tipo_medico,
                       me.especialidad AS medico_especialidad,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos
                FROM citas c
                JOIN medicos m ON c.id_medico = m.id_medico
                LEFT JOIN medicos_especialistas me ON m.id_medico = me.id_medico
                JOIN pacientes p ON c.id_paciente = p.dni
                WHERE c.id_paciente = :id_paciente
                $cond
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_paciente' => $id_paciente]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorMedico($id_medico, $solo_pendientes = false, $fecha = null) {
        $cond = "AND c.activo = true";
        if ($solo_pendientes) {
            $cond .= " AND c.estado = 'Pendiente'";
        }
        if ($fecha) {
            $cond .= " AND DATE(c.fecha_hora) = :fecha";
        }

        $sql = "SELECT c.*,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos,
                       p.dni AS paciente_dni, p.email AS paciente_email
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.dni
                WHERE c.id_medico = :id_medico
                $cond
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->db->prepare($sql);
        $params = [':id_medico' => $id_medico];
        if ($fecha) $params[':fecha'] = $fecha;
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id_cita) {
        $sql = "SELECT c.*,
                       m.nombre AS medico_nombre, m.apellidos AS medico_apellidos,
                       p.nombre AS paciente_nombre, p.apellidos AS paciente_apellidos
                FROM citas c
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN pacientes p ON c.id_paciente = p.dni
                WHERE c.id_cita = :id_cita";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_cita' => $id_cita]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id_cita, $nuevo_estado, $cancelada_por = null, $notas = null) {
        $sql = "UPDATE citas SET estado = :estado, fecha_actualizacion = NOW()";
        $params = [':estado' => $nuevo_estado, ':id_cita' => $id_cita];

        if ($cancelada_por !== null) {
            $sql .= ", cancelada_por = :cancelada_por";
            $params[':cancelada_por'] = $cancelada_por;
        }
        if ($notas !== null) {
            $sql .= ", notas_cancelacion = :notas";
            $params[':notas'] = $notas;
        }

        $sql .= " WHERE id_cita = :id_cita";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function contarPorMedico($id_medico, $estado = null) {
        $cond = $estado ? "AND estado = :estado" : "";
        $sql  = "SELECT COUNT(*) FROM citas WHERE id_medico = :id_medico AND activo = true $cond";
        $stmt = $this->db->prepare($sql);
        $params = [':id_medico' => $id_medico];
        if ($estado) $params[':estado'] = $estado;
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
?>
