<?php
/**
 * Archivo: EnfermedadesCatalogoVO.php
 * Descripción: Value Object para el catálogo de enfermedades
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class EnfermedadesCatalogoVO {
    private $id_enfermedad;
    private $nombre;
    private $descripcion;
    private $categoria;
    private $codigo_cie10;
    private $sintomas_comunes;
    private $factores_riesgo;
    private $nivel_gravedad;
    private $es_hereditaria;
    private $requiere_seguimiento;
    private $activo;

    // Constantes para categorías
    const CATEGORIA_CARDIOVASCULAR = 'cardiovascular';
    const CATEGORIA_RESPIRATORIA = 'respiratoria';
    const CATEGORIA_DIGESTIVA = 'digestiva';
    const CATEGORIA_NEUROLOGICA = 'neurologica';
    const CATEGORIA_ENDOCRINA = 'endocrina';
    const CATEGORIA_ONCOLOGICA = 'oncologica';
    const CATEGORIA_MENTAL = 'mental';
    const CATEGORIA_INFECCIOSA = 'infecciosa';
    const CATEGORIA_AUTOINMUNE = 'autoinmune';
    const CATEGORIA_GENETICA = 'genetica';
    const CATEGORIA_OTRA = 'otra';

    // Constantes para nivel de gravedad
    const GRAVEDAD_LEVE = 'leve';
    const GRAVEDAD_MODERADA = 'moderada';
    const GRAVEDAD_GRAVE = 'grave';
    const GRAVEDAD_CRITICA = 'critica';

    public function __construct($data = []) {
        $this->id_enfermedad = $data['id_enfermedad'] ?? null;
        $this->nombre = $data['nombre'] ?? null;
        $this->descripcion = $data['descripcion'] ?? null;
        $this->categoria = $data['categoria'] ?? self::CATEGORIA_OTRA;
        $this->codigo_cie10 = $data['codigo_cie10'] ?? null;
        $this->sintomas_comunes = $data['sintomas_comunes'] ?? null;
        $this->factores_riesgo = $data['factores_riesgo'] ?? null;
        $this->nivel_gravedad = $data['nivel_gravedad'] ?? self::GRAVEDAD_LEVE;
        $this->es_hereditaria = $data['es_hereditaria'] ?? false;
        $this->requiere_seguimiento = $data['requiere_seguimiento'] ?? false;
        $this->activo = $data['activo'] ?? true;
    }

    // Getters
    public function getIdEnfermedad() { return $this->id_enfermedad; }
    public function getNombre() { return $this->nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function getCategoria() { return $this->categoria; }
    public function getCodigoCie10() { return $this->codigo_cie10; }
    public function getSintomasComunes() { return $this->sintomas_comunes; }
    public function getFactoresRiesgo() { return $this->factores_riesgo; }
    public function getNivelGravedad() { return $this->nivel_gravedad; }
    public function getEsHereditaria() { return $this->es_hereditaria; }
    public function getRequiereSeguimiento() { return $this->requiere_seguimiento; }
    public function getActivo() { return $this->activo; }

    // Setters
    public function setIdEnfermedad($id_enfermedad) { $this->id_enfermedad = $id_enfermedad; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function setCategoria($categoria) { $this->categoria = $categoria; }
    public function setCodigoCie10($codigo_cie10) { $this->codigo_cie10 = $codigo_cie10; }
    public function setSintomasComunes($sintomas_comunes) { $this->sintomas_comunes = $sintomas_comunes; }
    public function setFactoresRiesgo($factores_riesgo) { $this->factores_riesgo = $factores_riesgo; }
    public function setNivelGravedad($nivel_gravedad) { $this->nivel_gravedad = $nivel_gravedad; }
    public function setEsHereditaria($es_hereditaria) { $this->es_hereditaria = $es_hereditaria; }
    public function setRequiereSeguimiento($requiere_seguimiento) { $this->requiere_seguimiento = $requiere_seguimiento; }
    public function setActivo($activo) { $this->activo = $activo; }

    /**
     * Obtiene el color asociado a la categoría
     */
    public function getColorCategoria() {
        $colores = [
            self::CATEGORIA_CARDIOVASCULAR => '#ef4444',
            self::CATEGORIA_RESPIRATORIA => '#3b82f6',
            self::CATEGORIA_DIGESTIVA => '#f59e0b',
            self::CATEGORIA_NEUROLOGICA => '#8b5cf6',
            self::CATEGORIA_ENDOCRINA => '#10b981',
            self::CATEGORIA_ONCOLOGICA => '#dc2626',
            self::CATEGORIA_MENTAL => '#06b6d4',
            self::CATEGORIA_INFECCIOSA => '#f97316',
            self::CATEGORIA_AUTOINMUNE => '#ec4899',
            self::CATEGORIA_GENETICA => '#6366f1',
            self::CATEGORIA_OTRA => '#6b7280'
        ];

        return $colores[$this->categoria] ?? $colores[self::CATEGORIA_OTRA];
    }

    /**
     * Obtiene el icono asociado a la categoría
     */
    public function getIconoCategoria() {
        $iconos = [
            self::CATEGORIA_CARDIOVASCULAR => 'fas fa-heartbeat',
            self::CATEGORIA_RESPIRATORIA => 'fas fa-lungs',
            self::CATEGORIA_DIGESTIVA => 'fas fa-stomach',
            self::CATEGORIA_NEUROLOGICA => 'fas fa-brain',
            self::CATEGORIA_ENDOCRINA => 'fas fa-dna',
            self::CATEGORIA_ONCOLOGICA => 'fas fa-ribbon',
            self::CATEGORIA_MENTAL => 'fas fa-head-side-virus',
            self::CATEGORIA_INFECCIOSA => 'fas fa-virus',
            self::CATEGORIA_AUTOINMUNE => 'fas fa-shield-virus',
            self::CATEGORIA_GENETICA => 'fas fa-dna',
            self::CATEGORIA_OTRA => 'fas fa-notes-medical'
        ];

        return $iconos[$this->categoria] ?? $iconos[self::CATEGORIA_OTRA];
    }

    /**
     * Obtiene el color del nivel de gravedad
     */
    public function getColorGravedad() {
        $colores = [
            self::GRAVEDAD_LEVE => '#10b981',
            self::GRAVEDAD_MODERADA => '#f59e0b',
            self::GRAVEDAD_GRAVE => '#f97316',
            self::GRAVEDAD_CRITICA => '#ef4444'
        ];

        return $colores[$this->nivel_gravedad] ?? $colores[self::GRAVEDAD_LEVE];
    }

    /**
     * Valida los datos de la enfermedad
     */
    public function validar() {
        $errores = [];

        if (!$this->nombre || strlen(trim($this->nombre)) < 3) {
            $errores[] = "El nombre debe tener al menos 3 caracteres";
        }

        $categorias_validas = [
            self::CATEGORIA_CARDIOVASCULAR, self::CATEGORIA_RESPIRATORIA,
            self::CATEGORIA_DIGESTIVA, self::CATEGORIA_NEUROLOGICA,
            self::CATEGORIA_ENDOCRINA, self::CATEGORIA_ONCOLOGICA,
            self::CATEGORIA_MENTAL, self::CATEGORIA_INFECCIOSA,
            self::CATEGORIA_AUTOINMUNE, self::CATEGORIA_GENETICA,
            self::CATEGORIA_OTRA
        ];

        if (!in_array($this->categoria, $categorias_validas)) {
            $errores[] = "Categoría no válida";
        }

        $gravedades_validas = [
            self::GRAVEDAD_LEVE, self::GRAVEDAD_MODERADA,
            self::GRAVEDAD_GRAVE, self::GRAVEDAD_CRITICA
        ];

        if (!in_array($this->nivel_gravedad, $gravedades_validas)) {
            $errores[] = "Nivel de gravedad no válido";
        }

        return $errores;
    }

    /**
     * Convierte el objeto a array
     */
    public function toArray() {
        return [
            'id_enfermedad' => $this->id_enfermedad,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'categoria' => $this->categoria,
            'codigo_cie10' => $this->codigo_cie10,
            'sintomas_comunes' => $this->sintomas_comunes,
            'factores_riesgo' => $this->factores_riesgo,
            'nivel_gravedad' => $this->nivel_gravedad,
            'es_hereditaria' => $this->es_hereditaria,
            'requiere_seguimiento' => $this->requiere_seguimiento,
            'activo' => $this->activo,
            'color_categoria' => $this->getColorCategoria(),
            'icono_categoria' => $this->getIconoCategoria(),
            'color_gravedad' => $this->getColorGravedad()
        ];
    }
}
?>