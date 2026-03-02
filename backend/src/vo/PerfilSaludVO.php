<?php
/**
 * Archivo: PerfilSaludVO.php
 * Descripción: Value Object para manejar los perfiles de salud de los pacientes
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class PerfilSaludVO {
    private $id_perfil;
    private $id_paciente;
    private $peso_kg;
    private $altura_cm;
    private $tipo_sangre;
    private $alergias;
    private $medicamentos_actuales;
    private $observaciones_medicas;
    private $fecha_creacion;
    private $fecha_actualizacion;
    private $activo;

    public function __construct($data = []) {
        $this->id_perfil = $data['id_perfil'] ?? null;
        $this->id_paciente = $data['id_paciente'] ?? null;
        $this->peso_kg = $data['peso_kg'] ?? null;
        $this->altura_cm = $data['altura_cm'] ?? null;
        $this->tipo_sangre = $data['tipo_sangre'] ?? null;
        $this->alergias = $data['alergias'] ?? null;
        $this->medicamentos_actuales = $data['medicamentos_actuales'] ?? null;
        $this->observaciones_medicas = $data['observaciones_medicas'] ?? null;
        $this->fecha_creacion = $data['fecha_creacion'] ?? null;
        $this->fecha_actualizacion = $data['fecha_actualizacion'] ?? null;
        $this->activo = $data['activo'] ?? true;
    }

    // Getters
    public function getIdPerfil() { return $this->id_perfil; }
    public function getIdPaciente() { return $this->id_paciente; }
    public function getPesoKg() { return $this->peso_kg; }
    public function getAlturaCm() { return $this->altura_cm; }
    public function getTipoSangre() { return $this->tipo_sangre; }
    public function getAlergias() { return $this->alergias; }
    public function getMedicamentosActuales() { return $this->medicamentos_actuales; }
    public function getObservacionesMedicas() { return $this->observaciones_medicas; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getFechaActualizacion() { return $this->fecha_actualizacion; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setIdPerfil($id_perfil) { $this->id_perfil = $id_perfil; }
    public function setIdPaciente($id_paciente) { $this->id_paciente = $id_paciente; }
    public function setPesoKg($peso_kg) { $this->peso_kg = $peso_kg; }
    public function setAlturaCm($altura_cm) { $this->altura_cm = $altura_cm; }
    public function setTipoSangre($tipo_sangre) { $this->tipo_sangre = $tipo_sangre; }
    public function setAlergias($alergias) { $this->alergias = $alergias; }
    public function setMedicamentosActuales($medicamentos_actuales) { $this->medicamentos_actuales = $medicamentos_actuales; }
    public function setObservacionesMedicas($observaciones_medicas) { $this->observaciones_medicas = $observaciones_medicas; }
    public function setFechaCreacion($fecha_creacion) { $this->fecha_creacion = $fecha_creacion; }
    public function setFechaActualizacion($fecha_actualizacion) { $this->fecha_actualizacion = $fecha_actualizacion; }
    public function setActivo($activo) { $this->activo = $activo; }

    /**
     * Calcula el IMC (Índice de Masa Corporal) del paciente
     */
    public function calcularIMC() {
        if ($this->peso_kg && $this->altura_cm) {
            $altura_m = $this->altura_cm / 100;
            return round($this->peso_kg / ($altura_m * $altura_m), 2);
        }
        return null;
    }

    /**
     * Obtiene la clasificación del IMC
     */
    public function getClasificacionIMC() {
        $imc = $this->calcularIMC();
        if ($imc === null) return 'Datos insuficientes';
        
        if ($imc < 18.5) return 'Bajo peso';
        if ($imc < 25) return 'Peso normal';
        if ($imc < 30) return 'Sobrepeso';
        return 'Obesidad';
    }

    /**
     * Valida los datos del perfil
     */
    public function validar() {
        $errores = [];

        if (!$this->id_paciente) {
            $errores[] = "ID de paciente es obligatorio";
        }

        if ($this->peso_kg !== null && ($this->peso_kg <= 0 || $this->peso_kg > 1000)) {
            $errores[] = "El peso debe estar entre 0 y 1000 kg";
        }

        if ($this->altura_cm !== null && ($this->altura_cm <= 0 || $this->altura_cm > 300)) {
            $errores[] = "La altura debe estar entre 0 y 300 cm";
        }

        if ($this->tipo_sangre !== null && !in_array($this->tipo_sangre, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])) {
            $errores[] = "Tipo de sangre no válido";
        }

        return $errores;
    }

    /**
     * Convierte el objeto a array
     */
    public function toArray() {
        return [
            'id_perfil' => $this->id_perfil,
            'id_paciente' => $this->id_paciente,
            'peso_kg' => $this->peso_kg,
            'altura_cm' => $this->altura_cm,
            'tipo_sangre' => $this->tipo_sangre,
            'alergias' => $this->alergias,
            'medicamentos_actuales' => $this->medicamentos_actuales,
            'observaciones_medicas' => $this->observaciones_medicas,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'activo' => $this->activo,
            'imc' => $this->calcularIMC(),
            'clasificacion_imc' => $this->getClasificacionIMC()
        ];
    }
}
?>