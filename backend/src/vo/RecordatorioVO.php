<?php
/**
 * Archivo: RecordatorioVO.php
 * Descripción: Value Object alineado con el schema actual (recordatorios vinculados a consultas)
 * Fecha: Marzo 2026
 */

class RecordatorioVO {
    private $id_recordatorio;
    private $id_consulta;
    private $fecha_hora;
    private $tipo_recordatorio;
    private $razon;
    private $estado;

    // Tipos permitidos (según schema.sql)
    const TIPO_MEDICACION = 'Medicación';
    const TIPO_CONTROL    = 'Control';
    const TIPO_CITA       = 'Cita';
    const TIPO_OTRO       = 'Otro';

    // Estados permitidos
    const ESTADO_PENDIENTE   = 'Pendiente';
    const ESTADO_COMPLETADO  = 'Completado';

    public function __construct($data = []) {
        $this->id_recordatorio   = $data['id_recordatorio'] ?? null;
        $this->id_consulta       = $data['id_consulta'] ?? null;
        $this->fecha_hora        = $data['fecha_hora'] ?? null;
        $this->tipo_recordatorio = $data['tipo_recordatorio'] ?? self::TIPO_OTRO;
        $this->razon             = $data['razon'] ?? null;
        $this->estado            = $data['estado'] ?? self::ESTADO_PENDIENTE;
    }

    // Getters
    public function getIdRecordatorio()   { return $this->id_recordatorio; }
    public function getIdConsulta()       { return $this->id_consulta; }
    public function getFechaHora()        { return $this->fecha_hora; }
    public function getTipoRecordatorio() { return $this->tipo_recordatorio; }
    public function getRazon()            { return $this->razon; }
    public function getEstado()           { return $this->estado; }

    // Setters
    public function setIdRecordatorio($v)   { $this->id_recordatorio = $v; }
    public function setIdConsulta($v)       { $this->id_consulta = $v; }
    public function setFechaHora($v)        { $this->fecha_hora = $v; }
    public function setTipoRecordatorio($v) { $this->tipo_recordatorio = $v; }
    public function setRazon($v)            { $this->razon = $v; }
    public function setEstado($v)           { $this->estado = $v; }

    public function validar() {
        $errores = [];

        if (!$this->id_consulta) {
            $errores[] = "ID de consulta es obligatorio";
        }
        if (!$this->razon || strlen(trim($this->razon)) < 3) {
            $errores[] = "La razón/título debe tener al menos 3 caracteres";
        }
        if (!$this->fecha_hora) {
            $errores[] = "La fecha y hora son obligatorias";
        } elseif (!DateTime::createFromFormat('Y-m-d H:i:s', $this->fecha_hora)) {
            $errores[] = "Formato de fecha/hora inválido (YYYY-MM-DD HH:MM:SS)";
        }

        $tipos_validos = [self::TIPO_MEDICACION, self::TIPO_CONTROL, self::TIPO_CITA, self::TIPO_OTRO];
        if (!in_array($this->tipo_recordatorio, $tipos_validos)) {
            $errores[] = "Tipo de recordatorio no válido";
        }

        $estados_validos = [self::ESTADO_PENDIENTE, self::ESTADO_COMPLETADO];
        if (!in_array($this->estado, $estados_validos)) {
            $errores[] = "Estado de recordatorio no válido";
        }

        return $errores;
    }

    public function toArray() {
        $fecha = null;
        $hora = null;
        if ($this->fecha_hora) {
            [$fecha, $hora] = array_pad(explode(' ', $this->fecha_hora), 2, null);
        }
        return [
            'id_recordatorio' => $this->id_recordatorio,
            'id_consulta' => $this->id_consulta,
            'fecha_hora' => $this->fecha_hora,
            'fecha_recordatorio' => $fecha,
            'hora_recordatorio' => $hora,
            'tipo_recordatorio' => $this->tipo_recordatorio,
            'razon' => $this->razon,
            'estado' => $this->estado
        ];
    }
}
?>
