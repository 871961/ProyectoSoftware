<?php
/**
 * Archivo: AntecedentesFamiliaresVO.php
 * Descripción: Value Object para manejar antecedentes familiares de pacientes
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class AntecedentesFamiliaresVO {
    private $id_antecedente;
    private $id_paciente;
    private $id_enfermedad;
    private $parentesco;
    private $lado_familiar;
    private $edad_diagnóstico;
    private $notas_adicionales;
    private $fecha_registro;
    private $activo;

    // Constantes para parentesco
    const PARENTESCO_PADRE = 'padre';
    const PARENTESCO_MADRE = 'madre';
    const PARENTESCO_HERMANO = 'hermano';
    const PARENTESCO_HERMANA = 'hermana';
    const PARENTESCO_ABUELO_PATERNO = 'abuelo_paterno';
    const PARENTESCO_ABUELA_PATERNA = 'abuela_paterna';
    const PARENTESCO_ABUELO_MATERNO = 'abuelo_materno';
    const PARENTESCO_ABUELA_MATERNA = 'abuela_materna';
    const PARENTESCO_TIO = 'tio';
    const PARENTESCO_TIA = 'tia';
    const PARENTESCO_PRIMO = 'primo';
    const PARENTESCO_PRIMA = 'prima';
    const PARENTESCO_OTRO = 'otro';

    // Constantes para lado familiar
    const LADO_PATERNO = 'paterno';
    const LADO_MATERNO = 'materno';
    const LADO_AMBOS = 'ambos';

    public function __construct($data = []) {
        $this->id_antecedente = $data['id_antecedente'] ?? null;
        $this->id_paciente = $data['id_paciente'] ?? null;
        $this->id_enfermedad = $data['id_enfermedad'] ?? null;
        $this->parentesco = $data['parentesco'] ?? null;
        $this->lado_familiar = $data['lado_familiar'] ?? null;
        $this->edad_diagnóstico = $data['edad_diagnóstico'] ?? null;
        $this->notas_adicionales = $data['notas_adicionales'] ?? null;
        $this->fecha_registro = $data['fecha_registro'] ?? null;
        $this->activo = $data['activo'] ?? true;
    }

    // Getters
    public function getIdAntecedente() { return $this->id_antecedente; }
    public function getIdPaciente() { return $this->id_paciente; }
    public function getIdEnfermedad() { return $this->id_enfermedad; }
    public function getParentesco() { return $this->parentesco; }
    public function getLadoFamiliar() { return $this->lado_familiar; }
    public function getEdadDiagnostico() { return $this->edad_diagnóstico; }
    public function getNotasAdicionales() { return $this->notas_adicionales; }
    public function getFechaRegistro() { return $this->fecha_registro; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setIdAntecedente($id_antecedente) { $this->id_antecedente = $id_antecedente; }
    public function setIdPaciente($id_paciente) { $this->id_paciente = $id_paciente; }
    public function setIdEnfermedad($id_enfermedad) { $this->id_enfermedad = $id_enfermedad; }
    public function setParentesco($parentesco) { $this->parentesco = $parentesco; }
    public function setLadoFamiliar($lado_familiar) { $this->lado_familiar = $lado_familiar; }
    public function setEdadDiagnostico($edad_diagnóstico) { $this->edad_diagnóstico = $edad_diagnóstico; }
    public function setNotasAdicionales($notas_adicionales) { $this->notas_adicionales = $notas_adicionales; }
    public function setFechaRegistro($fecha_registro) { $this->fecha_registro = $fecha_registro; }
    public function setActivo($activo) { $this->activo = $activo; }

    /**
     * Obtiene la descripción legible del parentesco
     */
    public function getParentescoDescripcion() {
        $descripciones = [
            self::PARENTESCO_PADRE => 'Padre',
            self::PARENTESCO_MADRE => 'Madre',
            self::PARENTESCO_HERMANO => 'Hermano',
            self::PARENTESCO_HERMANA => 'Hermana',
            self::PARENTESCO_ABUELO_PATERNO => 'Abuelo Paterno',
            self::PARENTESCO_ABUELA_PATERNA => 'Abuela Paterna',
            self::PARENTESCO_ABUELO_MATERNO => 'Abuelo Materno',
            self::PARENTESCO_ABUELA_MATERNA => 'Abuela Materna',
            self::PARENTESCO_TIO => 'Tío',
            self::PARENTESCO_TIA => 'Tía',
            self::PARENTESCO_PRIMO => 'Primo',
            self::PARENTESCO_PRIMA => 'Prima',
            self::PARENTESCO_OTRO => 'Otro familiar'
        ];

        return $descripciones[$this->parentesco] ?? 'Desconocido';
    }

    /**
     * Determina el nivel de riesgo genético basado en el parentesco
     */
    public function getNivelRiesgo() {
        $riesgos_altos = [
            self::PARENTESCO_PADRE,
            self::PARENTESCO_MADRE,
            self::PARENTESCO_HERMANO,
            self::PARENTESCO_HERMANA
        ];

        $riesgos_medios = [
            self::PARENTESCO_ABUELO_PATERNO,
            self::PARENTESCO_ABUELA_PATERNA,
            self::PARENTESCO_ABUELO_MATERNO,
            self::PARENTESCO_ABUELA_MATERNA,
            self::PARENTESCO_TIO,
            self::PARENTESCO_TIA
        ];

        if (in_array($this->parentesco, $riesgos_altos)) {
            return 'ALTO';
        } elseif (in_array($this->parentesco, $riesgos_medios)) {
            return 'MEDIO';
        } else {
            return 'BAJO';
        }
    }

    /**
     * Obtiene el color asociado al nivel de riesgo
     */
    public function getColorRiesgo() {
        switch ($this->getNivelRiesgo()) {
            case 'ALTO': return '#ef4444';   // Rojo
            case 'MEDIO': return '#f59e0b';  // Amarillo
            case 'BAJO': return '#10b981';   // Verde
            default: return '#6b7280';       // Gris
        }
    }

    /**
     * Valida los datos del antecedente familiar
     */
    public function validar() {
        $errores = [];

        if (!$this->id_paciente) {
            $errores[] = "ID de paciente es obligatorio";
        }

        if (!$this->id_enfermedad) {
            $errores[] = "ID de enfermedad es obligatorio";
        }

        if (!$this->parentesco) {
            $errores[] = "Parentesco es obligatorio";
        }

        $parentescos_validos = [
            self::PARENTESCO_PADRE, self::PARENTESCO_MADRE,
            self::PARENTESCO_HERMANO, self::PARENTESCO_HERMANA,
            self::PARENTESCO_ABUELO_PATERNO, self::PARENTESCO_ABUELA_PATERNA,
            self::PARENTESCO_ABUELO_MATERNO, self::PARENTESCO_ABUELA_MATERNA,
            self::PARENTESCO_TIO, self::PARENTESCO_TIA,
            self::PARENTESCO_PRIMO, self::PARENTESCO_PRIMA,
            self::PARENTESCO_OTRO
        ];

        if (!in_array($this->parentesco, $parentescos_validos)) {
            $errores[] = "Parentesco no válido";
        }

        if ($this->lado_familiar && !in_array($this->lado_familiar, [self::LADO_PATERNO, self::LADO_MATERNO, self::LADO_AMBOS])) {
            $errores[] = "Lado familiar no válido";
        }

        if ($this->edad_diagnóstico !== null && ($this->edad_diagnóstico < 0 || $this->edad_diagnóstico > 150)) {
            $errores[] = "Edad de diagnóstico debe estar entre 0 y 150 años";
        }

        return $errores;
    }

    /**
     * Convierte el objeto a array
     */
    public function toArray() {
        return [
            'id_antecedente' => $this->id_antecedente,
            'id_paciente' => $this->id_paciente,
            'id_enfermedad' => $this->id_enfermedad,
            'parentesco' => $this->parentesco,
            'lado_familiar' => $this->lado_familiar,
            'edad_diagnóstico' => $this->edad_diagnóstico,
            'notas_adicionales' => $this->notas_adicionales,
            'fecha_registro' => $this->fecha_registro,
            'activo' => $this->activo,
            'parentesco_descripcion' => $this->getParentescoDescripcion(),
            'nivel_riesgo' => $this->getNivelRiesgo(),
            'color_riesgo' => $this->getColorRiesgo()
        ];
    }
}
?>