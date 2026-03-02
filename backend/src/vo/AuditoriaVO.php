<?php
/**
 * Archivo: AuditoriaVO.php
 * Descripción: Value Object para manejar logs de auditoría del sistema
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class AuditoriaVO {
    private $id_log;
    private $accion;
    private $tabla_afectada;
    private $registro_id;
    private $detalles;
    private $usuario_responsable;
    private $fecha_hora;
    private $ip_usuario;
    private $user_agent;

    // Constantes para acciones
    const ACCION_INSERT = 'INSERT';
    const ACCION_UPDATE = 'UPDATE';
    const ACCION_DELETE = 'DELETE';
    const ACCION_SELECT = 'SELECT';
    const ACCION_LOGIN = 'LOGIN';
    const ACCION_LOGOUT = 'LOGOUT';
    const ACCION_LOGIN_FAILED = 'LOGIN_FAILED';

    public function __construct($data = []) {
        $this->id_log = $data['id_log'] ?? null;
        $this->accion = $data['accion'] ?? null;
        $this->tabla_afectada = $data['tabla_afectada'] ?? null;
        $this->registro_id = $data['registro_id'] ?? null;
        $this->detalles = $data['detalles'] ?? null;
        $this->usuario_responsable = $data['usuario_responsable'] ?? null;
        $this->fecha_hora = $data['fecha_hora'] ?? null;
        $this->ip_usuario = $data['ip_usuario'] ?? null;
        $this->user_agent = $data['user_agent'] ?? null;
    }

    // Getters
    public function getIdLog() { return $this->id_log; }
    public function getAccion() { return $this->accion; }
    public function getTablaAfectada() { return $this->tabla_afectada; }
    public function getRegistroId() { return $this->registro_id; }
    public function getDetalles() { return $this->detalles; }
    public function getUsuarioResponsable() { return $this->usuario_responsable; }
    public function getFechaHora() { return $this->fecha_hora; }
    public function getIpUsuario() { return $this->ip_usuario; }
    public function getUserAgent() { return $this->user_agent; }

    // Setters
    public function setIdLog($id_log) { $this->id_log = $id_log; }
    public function setAccion($accion) { $this->accion = $accion; }
    public function setTablaAfectada($tabla_afectada) { $this->tabla_afectada = $tabla_afectada; }
    public function setRegistroId($registro_id) { $this->registro_id = $registro_id; }
    public function setDetalles($detalles) { $this->detalles = $detalles; }
    public function setUsuarioResponsable($usuario_responsable) { $this->usuario_responsable = $usuario_responsable; }
    public function setFechaHora($fecha_hora) { $this->fecha_hora = $fecha_hora; }
    public function setIpUsuario($ip_usuario) { $this->ip_usuario = $ip_usuario; }
    public function setUserAgent($user_agent) { $this->user_agent = $user_agent; }

    /**
     * Obtiene el color asociado a la acción para la interfaz
     */
    public function getColorAccion() {
        $colores = [
            self::ACCION_INSERT => '#10b981',     // Verde
            self::ACCION_UPDATE => '#3b82f6',     // Azul
            self::ACCION_DELETE => '#ef4444',     // Rojo
            self::ACCION_SELECT => '#6b7280',     // Gris
            self::ACCION_LOGIN => '#059669',      // Verde oscuro
            self::ACCION_LOGOUT => '#f59e0b',     // Amarillo
            self::ACCION_LOGIN_FAILED => '#dc2626' // Rojo oscuro
        ];

        return $colores[$this->accion] ?? '#6b7280';
    }

    /**
     * Obtiene el icono asociado a la acción
     */
    public function getIconoAccion() {
        $iconos = [
            self::ACCION_INSERT => 'fas fa-plus-circle',
            self::ACCION_UPDATE => 'fas fa-edit',
            self::ACCION_DELETE => 'fas fa-trash-alt',
            self::ACCION_SELECT => 'fas fa-eye',
            self::ACCION_LOGIN => 'fas fa-sign-in-alt',
            self::ACCION_LOGOUT => 'fas fa-sign-out-alt',
            self::ACCION_LOGIN_FAILED => 'fas fa-exclamation-triangle'
        ];

        return $iconos[$this->accion] ?? 'fas fa-info-circle';
    }

    /**
     * Determina el nivel de criticidad del evento
     */
    public function getNivelCriticidad() {
        switch ($this->accion) {
            case self::ACCION_DELETE:
                return 'ALTA';
            case self::ACCION_UPDATE:
            case self::ACCION_LOGIN_FAILED:
                return 'MEDIA';
            case self::ACCION_INSERT:
            case self::ACCION_LOGIN:
            case self::ACCION_LOGOUT:
                return 'BAJA';
            case self::ACCION_SELECT:
                return 'INFORMATIVA';
            default:
                return 'MEDIA';
        }
    }

    /**
     * Obtiene el color del nivel de criticidad
     */
    public function getColorCriticidad() {
        switch ($this->getNivelCriticidad()) {
            case 'ALTA': return '#ef4444';
            case 'MEDIA': return '#f59e0b';
            case 'BAJA': return '#10b981';
            case 'INFORMATIVA': return '#6b7280';
            default: return '#6b7280';
        }
    }

    /**
     * Formatea la fecha y hora para mostrar
     */
    public function getFechaFormateada() {
        if (!$this->fecha_hora) return 'N/A';
        
        $fecha = new DateTime($this->fecha_hora);
        return $fecha->format('d/m/Y H:i:s');
    }

    /**
     * Obtiene el tiempo transcurrido desde el evento
     */
    public function getTiempoTranscurrido() {
        if (!$this->fecha_hora) return 'Desconocido';

        $ahora = new DateTime();
        $fecha_evento = new DateTime($this->fecha_hora);
        $diferencia = $ahora->diff($fecha_evento);

        if ($diferencia->days > 0) {
            return $diferencia->days . ' día' . ($diferencia->days > 1 ? 's' : '');
        } elseif ($diferencia->h > 0) {
            return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '');
        } elseif ($diferencia->i > 0) {
            return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '');
        } else {
            return 'Hace un momento';
        }
    }

    /**
     * Valida los datos del log de auditoría
     */
    public function validar() {
        $errores = [];

        if (!$this->accion) {
            $errores[] = "La acción es obligatoria";
        }

        $acciones_validas = [
            self::ACCION_INSERT, self::ACCION_UPDATE, self::ACCION_DELETE,
            self::ACCION_SELECT, self::ACCION_LOGIN, self::ACCION_LOGOUT,
            self::ACCION_LOGIN_FAILED
        ];

        if (!in_array($this->accion, $acciones_validas)) {
            $errores[] = "Acción no válida";
        }

        if (!$this->detalles || strlen(trim($this->detalles)) < 3) {
            $errores[] = "Los detalles deben tener al menos 3 caracteres";
        }

        return $errores;
    }

    /**
     * Convierte el objeto a array
     */
    public function toArray() {
        return [
            'id_log' => $this->id_log,
            'accion' => $this->accion,
            'tabla_afectada' => $this->tabla_afectada,
            'registro_id' => $this->registro_id,
            'detalles' => $this->detalles,
            'usuario_responsable' => $this->usuario_responsable,
            'fecha_hora' => $this->fecha_hora,
            'ip_usuario' => $this->ip_usuario,
            'user_agent' => $this->user_agent,
            'color_accion' => $this->getColorAccion(),
            'icono_accion' => $this->getIconoAccion(),
            'nivel_criticidad' => $this->getNivelCriticidad(),
            'color_criticidad' => $this->getColorCriticidad(),
            'fecha_formateada' => $this->getFechaFormateada(),
            'tiempo_transcurrido' => $this->getTiempoTranscurrido()
        ];
    }
}
?>