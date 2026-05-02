<?php
/**
 * Archivo: CitaVO.php
 * Descripcion: Value Object para el módulo de citas médicas
 * Fecha: Mayo 2026
 */

class CitaVO {
    private $id_cita;
    private $id_paciente;
    private $id_medico;
    private $fecha_hora;
    private $motivo;
    private $estado;
    private $tipo;
    private $notas_cancelacion;
    private $cancelada_por;

    const ESTADO_PENDIENTE  = 'Pendiente';
    const ESTADO_CONFIRMADA = 'Confirmada';
    const ESTADO_CANCELADA  = 'Cancelada';
    const ESTADO_COMPLETADA = 'Completada';

    const TIPO_PRESENCIAL = 'Presencial';
    const TIPO_TELEMATICA = 'Telematica';

    public function __construct($data = []) {
        $this->id_cita           = $data['id_cita'] ?? null;
        $this->id_paciente       = $data['id_paciente'] ?? null;
        $this->id_medico         = $data['id_medico'] ?? null;
        $this->fecha_hora        = $data['fecha_hora'] ?? null;
        $this->motivo            = $data['motivo'] ?? null;
        $this->estado            = $data['estado'] ?? self::ESTADO_PENDIENTE;
        $this->tipo              = $data['tipo'] ?? self::TIPO_PRESENCIAL;
        $this->notas_cancelacion = $data['notas_cancelacion'] ?? null;
        $this->cancelada_por     = $data['cancelada_por'] ?? null;
    }

    public function getIdCita()           { return $this->id_cita; }
    public function getIdPaciente()       { return $this->id_paciente; }
    public function getIdMedico()         { return $this->id_medico; }
    public function getFechaHora()        { return $this->fecha_hora; }
    public function getMotivo()           { return $this->motivo; }
    public function getEstado()           { return $this->estado; }
    public function getTipo()             { return $this->tipo; }
    public function getNotasCancelacion() { return $this->notas_cancelacion; }
    public function getCanceladaPor()     { return $this->cancelada_por; }

    public function setIdCita($v)           { $this->id_cita = $v; }
    public function setIdPaciente($v)       { $this->id_paciente = $v; }
    public function setIdMedico($v)         { $this->id_medico = $v; }
    public function setFechaHora($v)        { $this->fecha_hora = $v; }
    public function setMotivo($v)           { $this->motivo = $v; }
    public function setEstado($v)           { $this->estado = $v; }
    public function setTipo($v)             { $this->tipo = $v; }
    public function setNotasCancelacion($v) { $this->notas_cancelacion = $v; }
    public function setCanceladaPor($v)     { $this->cancelada_por = $v; }

    public function validar() {
        $errores = [];

        if (!$this->id_paciente) {
            $errores[] = "El paciente es obligatorio";
        }
        if (!$this->id_medico) {
            $errores[] = "El médico es obligatorio";
        }
        if (!$this->motivo || strlen(trim($this->motivo)) < 3) {
            $errores[] = "El motivo debe tener al menos 3 caracteres";
        }
        if (!$this->fecha_hora) {
            $errores[] = "La fecha y hora son obligatorias";
        } elseif (!DateTime::createFromFormat('Y-m-d H:i:s', $this->fecha_hora)) {
            $errores[] = "Formato de fecha/hora inválido (YYYY-MM-DD HH:MM:SS)";
        }

        $estados = [self::ESTADO_PENDIENTE, self::ESTADO_CONFIRMADA, self::ESTADO_CANCELADA, self::ESTADO_COMPLETADA];
        if (!in_array($this->estado, $estados)) {
            $errores[] = "Estado no válido";
        }

        $tipos = [self::TIPO_PRESENCIAL, self::TIPO_TELEMATICA];
        if (!in_array($this->tipo, $tipos)) {
            $errores[] = "Tipo de cita no válido";
        }

        return $errores;
    }

    public function toArray() {
        return [
            'id_cita'           => $this->id_cita,
            'id_paciente'       => $this->id_paciente,
            'id_medico'         => $this->id_medico,
            'fecha_hora'        => $this->fecha_hora,
            'motivo'            => $this->motivo,
            'estado'            => $this->estado,
            'tipo'              => $this->tipo,
            'notas_cancelacion' => $this->notas_cancelacion,
            'cancelada_por'     => $this->cancelada_por,
        ];
    }
}
?>
