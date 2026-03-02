<?php
/**
 * Archivo: RecordatorioVO.php
 * Descripción: Value Object para manejar recordatorios de pacientes
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class RecordatorioVO {
    private $id_recordatorio;
    private $id_paciente;
    private $id_medico;
    private $tipo;
    private $titulo;
    private $descripcion;
    private $fecha_recordatorio;
    private $hora_recordatorio;
    private $estado;
    private $prioridad;
    private $notas_adicionales;
    private $fecha_creacion;
    private $fecha_completado;
    private $activo;

    // Constantes para tipos de recordatorio
    const TIPO_MEDICAMENTO = 'medicamento';
    const TIPO_CITA = 'cita';
    const TIPO_EJERCICIO = 'ejercicio';
    const TIPO_DIETA = 'dieta';
    const TIPO_EXAMEN = 'examen';
    const TIPO_SEGUIMIENTO = 'seguimiento';
    const TIPO_OTRO = 'otro';

    // Constantes para estados
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_COMPLETADO = 'completado';
    const ESTADO_CANCELADO = 'cancelado';
    const ESTADO_VENCIDO = 'vencido';

    // Constantes para prioridades
    const PRIORIDAD_BAJA = 'baja';
    const PRIORIDAD_MEDIA = 'media';
    const PRIORIDAD_ALTA = 'alta';
    const PRIORIDAD_URGENTE = 'urgente';

    public function __construct($data = []) {
        $this->id_recordatorio = $data['id_recordatorio'] ?? null;
        $this->id_paciente = $data['id_paciente'] ?? null;
        $this->id_medico = $data['id_medico'] ?? null;
        $this->tipo = $data['tipo'] ?? self::TIPO_OTRO;
        $this->titulo = $data['titulo'] ?? null;
        $this->descripcion = $data['descripcion'] ?? null;
        $this->fecha_recordatorio = $data['fecha_recordatorio'] ?? null;
        $this->hora_recordatorio = $data['hora_recordatorio'] ?? null;
        $this->estado = $data['estado'] ?? self::ESTADO_PENDIENTE;
        $this->prioridad = $data['prioridad'] ?? self::PRIORIDAD_MEDIA;
        $this->notas_adicionales = $data['notas_adicionales'] ?? null;
        $this->fecha_creacion = $data['fecha_creacion'] ?? null;
        $this->fecha_completado = $data['fecha_completado'] ?? null;
        $this->activo = $data['activo'] ?? true;
    }

    // Getters
    public function getIdRecordatorio() { return $this->id_recordatorio; }
    public function getIdPaciente() { return $this->id_paciente; }
    public function getIdMedico() { return $this->id_medico; }
    public function getTipo() { return $this->tipo; }
    public function getTitulo() { return $this->titulo; }
    public function getDescripcion() { return $this->descripcion; }
    public function getFechaRecordatorio() { return $this->fecha_recordatorio; }
    public function getHoraRecordatorio() { return $this->hora_recordatorio; }
    public function getEstado() { return $this->estado; }
    public function getPrioridad() { return $this->prioridad; }
    public function getNotasAdicionales() { return $this->notas_adicionales; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getFechaCompletado() { return $this->fecha_completado; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setIdRecordatorio($id_recordatorio) { $this->id_recordatorio = $id_recordatorio; }
    public function setIdPaciente($id_paciente) { $this->id_paciente = $id_paciente; }
    public function setIdMedico($id_medico) { $this->id_medico = $id_medico; }
    public function setTipo($tipo) { $this->tipo = $tipo; }
    public function setTitulo($titulo) { $this->titulo = $titulo; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setFechaRecordatorio($fecha_recordatorio) { $this->fecha_recordatorio = $fecha_recordatorio; }
    public function setHoraRecordatorio($hora_recordatorio) { $this->hora_recordatorio = $hora_recordatorio; }
    public function setEstado($estado) { $this->estado = $estado; }
    public function setPrioridad($prioridad) { $this->prioridad = $prioridad; }
    public function setNotasAdicionales($notas_adicionales) { $this->notas_adicionales = $notas_adicionales; }
    public function setFechaCreacion($fecha_creacion) { $this->fecha_creacion = $fecha_creacion; }
    public function setFechaCompletado($fecha_completado) { $this->fecha_completado = $fecha_completado; }
    public function setActivo($activo) { $this->activo = $activo; }

    /**
     * Verifica si el recordatorio está vencido
     */
    public function estaVencido() {
        if ($this->estado === self::ESTADO_COMPLETADO || $this->estado === self::ESTADO_CANCELADO) {
            return false;
        }

        $fechaHoraRecordatorio = $this->fecha_recordatorio;
        if ($this->hora_recordatorio) {
            $fechaHoraRecordatorio .= ' ' . $this->hora_recordatorio;
        }

        return strtotime($fechaHoraRecordatorio) < time();
    }

    /**
     * Verifica si el recordatorio es para hoy
     */
    public function esParaHoy() {
        return date('Y-m-d') === $this->fecha_recordatorio;
    }

    /**
     * Verifica si el recordatorio es urgente
     */
    public function esUrgente() {
        return $this->prioridad === self::PRIORIDAD_URGENTE;
    }

    /**
     * Obtiene el tiempo restante hasta el recordatorio
     */
    public function getTiempoRestante() {
        if ($this->estado === self::ESTADO_COMPLETADO || $this->estado === self::ESTADO_CANCELADO) {
            return null;
        }

        $fechaHoraRecordatorio = $this->fecha_recordatorio;
        if ($this->hora_recordatorio) {
            $fechaHoraRecordatorio .= ' ' . $this->hora_recordatorio;
        }

        $timestamp = strtotime($fechaHoraRecordatorio);
        $diferencia = $timestamp - time();

        if ($diferencia < 0) {
            return 'Vencido';
        }

        $dias = floor($diferencia / (24 * 60 * 60));
        $horas = floor(($diferencia % (24 * 60 * 60)) / (60 * 60));
        $minutos = floor(($diferencia % (60 * 60)) / 60);

        if ($dias > 0) {
            return $dias . ' día' . ($dias > 1 ? 's' : '');
        } elseif ($horas > 0) {
            return $horas . ' hora' . ($horas > 1 ? 's' : '');
        } else {
            return $minutos . ' minuto' . ($minutos > 1 ? 's' : '');
        }
    }

    /**
     * Obtiene el icono CSS según el tipo de recordatorio
     */
    public function getIcono() {
        $iconos = [
            self::TIPO_MEDICAMENTO => 'fas fa-pills',
            self::TIPO_CITA => 'fas fa-calendar-day',
            self::TIPO_EJERCICIO => 'fas fa-running',
            self::TIPO_DIETA => 'fas fa-apple-alt',
            self::TIPO_EXAMEN => 'fas fa-vial',
            self::TIPO_SEGUIMIENTO => 'fas fa-stethoscope',
            self::TIPO_OTRO => 'fas fa-bell'
        ];

        return $iconos[$this->tipo] ?? $iconos[self::TIPO_OTRO];
    }

    /**
     * Obtiene el color según la prioridad
     */
    public function getColorPrioridad() {
        $colores = [
            self::PRIORIDAD_BAJA => '#10b981',      // Verde
            self::PRIORIDAD_MEDIA => '#f59e0b',     // Amarillo
            self::PRIORIDAD_ALTA => '#f97316',      // Naranja
            self::PRIORIDAD_URGENTE => '#ef4444'    // Rojo
        ];

        return $colores[$this->prioridad] ?? $colores[self::PRIORIDAD_MEDIA];
    }

    /**
     * Valida los datos del recordatorio
     */
    public function validar() {
        $errores = [];

        if (!$this->id_paciente) {
            $errores[] = "ID de paciente es obligatorio";
        }

        if (!$this->titulo || strlen(trim($this->titulo)) < 3) {
            $errores[] = "El título debe tener at least 3 caracteres";
        }

        if (!$this->fecha_recordatorio) {
            $errores[] = "La fecha de recordatorio es obligatoria";
        } elseif (!DateTime::createFromFormat('Y-m-d', $this->fecha_recordatorio)) {
            $errores[] = "Formato de fecha inválido (usar YYYY-MM-DD)";
        }

        if ($this->hora_recordatorio && !DateTime::createFromFormat('H:i:s', $this->hora_recordatorio)) {
            $errores[] = "Formato de hora inválido (usar HH:MM:SS)";
        }

        $tipos_validos = [
            self::TIPO_MEDICAMENTO,
            self::TIPO_CITA,
            self::TIPO_EJERCICIO,
            self::TIPO_DIETA,
            self::TIPO_EXAMEN,
            self::TIPO_SEGUIMIENTO,
            self::TIPO_OTRO
        ];
        if (!in_array($this->tipo, $tipos_validos)) {
            $errores[] = "Tipo de recordatorio no válido";
        }

        $estados_validos = [
            self::ESTADO_PENDIENTE,
            self::ESTADO_COMPLETADO,
            self::ESTADO_CANCELADO,
            self::ESTADO_VENCIDO
        ];
        if (!in_array($this->estado, $estados_validos)) {
            $errores[] = "Estado de recordatorio no válido";
        }

        $prioridades_validas = [
            self::PRIORIDAD_BAJA,
            self::PRIORIDAD_MEDIA,
            self::PRIORIDAD_ALTA,
            self::PRIORIDAD_URGENTE
        ];
        if (!in_array($this->prioridad, $prioridades_validas)) {
            $errores[] = "Prioridad no válida";
        }

        return $errores;
    }

    /**
     * Convierte el objeto a array
     */
    public function toArray() {
        return [
            'id_recordatorio' => $this->id_recordatorio,
            'id_paciente' => $this->id_paciente,
            'id_medico' => $this->id_medico,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'fecha_recordatorio' => $this->fecha_recordatorio,
            'hora_recordatorio' => $this->hora_recordatorio,
            'estado' => $this->estado,
            'prioridad' => $this->prioridad,
            'notas_adicionales' => $this->notas_adicionales,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_completado' => $this->fecha_completado,
            'activo' => $this->activo,
            'esta_vencido' => $this->estaVencido(),
            'es_para_hoy' => $this->esParaHoy(),
            'es_urgente' => $this->esUrgente(),
            'tiempo_restante' => $this->getTiempoRestante(),
            'icono' => $this->getIcono(),
            'color_prioridad' => $this->getColorPrioridad()
        ];
    }
}
?>