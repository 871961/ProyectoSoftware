<?php
/**
 * Archivo: DependienteVO.php
 * Descripción: Value Object para la entidad Paciente Dependiente (menor)
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class DependienteVO {
    private $id_dependiente;
    private $nombre;
    private $apellidos;
    private $fecha_nacimiento;
    private $num_seguridad_social;
    private $dni_tutor;
    private $id_pediatra;
    private $grupo_sanguineo;
    private $alergias;
    private $observaciones;
    private $fecha_registro;
    private $activo;
    private $fecha_baja;

    // Campos extra para JOINs
    private $pediatra_nombre;
    private $pediatra_apellidos;
    private $tutor_nombre;
    private $tutor_apellidos;

    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->id_dependiente = $datos['id_dependiente'] ?? null;
            $this->nombre = $datos['nombre'] ?? '';
            $this->apellidos = $datos['apellidos'] ?? '';
            $this->fecha_nacimiento = $datos['fecha_nacimiento'] ?? '';
            $this->num_seguridad_social = $datos['num_seguridad_social'] ?? null;
            $this->dni_tutor = $datos['dni_tutor'] ?? null;
            $this->id_pediatra = $datos['id_pediatra'] ?? null;
            $this->grupo_sanguineo = $datos['grupo_sanguineo'] ?? null;
            $this->alergias = $datos['alergias'] ?? null;
            $this->observaciones = $datos['observaciones'] ?? null;
            $this->fecha_registro = $datos['fecha_registro'] ?? null;
            $this->activo = $datos['activo'] ?? true;
            $this->fecha_baja = $datos['fecha_baja'] ?? null;

            // Campos de JOINs
            $this->pediatra_nombre = $datos['pediatra_nombre'] ?? null;
            $this->pediatra_apellidos = $datos['pediatra_apellidos'] ?? null;
            $this->tutor_nombre = $datos['tutor_nombre'] ?? null;
            $this->tutor_apellidos = $datos['tutor_apellidos'] ?? null;
        }
    }

    // Getters
    public function getIdDependiente() { return $this->id_dependiente; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getFechaNacimiento() { return $this->fecha_nacimiento; }
    public function getNumSeguridadSocial() { return $this->num_seguridad_social; }
    public function getDniTutor() { return $this->dni_tutor; }
    public function getIdPediatra() { return $this->id_pediatra; }
    public function getGrupoSanguineo() { return $this->grupo_sanguineo; }
    public function getAlergias() { return $this->alergias; }
    public function getObservaciones() { return $this->observaciones; }
    public function getFechaRegistro() { return $this->fecha_registro; }
    public function getActivo() { return $this->activo; }
    public function getFechaBaja() { return $this->fecha_baja; }
    public function getPediatraNombre() { return $this->pediatra_nombre; }
    public function getPediatraApellidos() { return $this->pediatra_apellidos; }
    public function getTutorNombre() { return $this->tutor_nombre; }
    public function getTutorApellidos() { return $this->tutor_apellidos; }

    // Setters
    public function setIdDependiente($id) { $this->id_dependiente = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setFechaNacimiento($fecha) { $this->fecha_nacimiento = $fecha; }
    public function setNumSeguridadSocial($num) { $this->num_seguridad_social = $num; }
    public function setDniTutor($dni) { $this->dni_tutor = $dni; }
    public function setIdPediatra($id) { $this->id_pediatra = $id; }
    public function setGrupoSanguineo($grupo) { $this->grupo_sanguineo = $grupo; }
    public function setAlergias($alergias) { $this->alergias = $alergias; }
    public function setObservaciones($obs) { $this->observaciones = $obs; }
    public function setFechaRegistro($fecha) { $this->fecha_registro = $fecha; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setFechaBaja($fecha) { $this->fecha_baja = $fecha; }

    public function getNombreCompleto() {
        return $this->nombre . ' ' . $this->apellidos;
    }

    public function getPediatraNombreCompleto() {
        return trim($this->pediatra_nombre . ' ' . $this->pediatra_apellidos);
    }

    public function getTutorNombreCompleto() {
        return trim($this->tutor_nombre . ' ' . $this->tutor_apellidos);
    }

    /**
     * Calcula la edad del dependiente en años
     */
    public function getEdad() {
        if (empty($this->fecha_nacimiento)) {
            return null;
        }
        $nacimiento = new DateTime($this->fecha_nacimiento);
        $hoy = new DateTime();
        return $nacimiento->diff($hoy)->y;
    }

    /**
     * Verifica si el dependiente es menor de edad (< 18 años)
     */
    public function esMenorDeEdad() {
        $edad = $this->getEdad();
        return $edad !== null && $edad < 18;
    }

    public function toArray() {
        return [
            'id_dependiente' => $this->id_dependiente,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'num_seguridad_social' => $this->num_seguridad_social,
            'dni_tutor' => $this->dni_tutor,
            'id_pediatra' => $this->id_pediatra,
            'grupo_sanguineo' => $this->grupo_sanguineo,
            'alergias' => $this->alergias,
            'observaciones' => $this->observaciones,
            'fecha_registro' => $this->fecha_registro,
            'activo' => $this->activo,
            'fecha_baja' => $this->fecha_baja,
            'edad' => $this->getEdad(),
            'nombre_completo' => $this->getNombreCompleto(),
            'pediatra_nombre' => $this->pediatra_nombre,
            'pediatra_apellidos' => $this->pediatra_apellidos,
            'pediatra_nombre_completo' => $this->getPediatraNombreCompleto(),
            'tutor_nombre' => $this->tutor_nombre,
            'tutor_apellidos' => $this->tutor_apellidos,
            'tutor_nombre_completo' => $this->getTutorNombreCompleto()
        ];
    }
}
