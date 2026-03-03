<?php
/**
 * Archivo: ConsultaVO.php
 * Descripción: Value Object para la entidad Consulta
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class ConsultaVO {
    private $id_consulta;
    private $id_paciente;
    private $id_medico;
    private $fecha;
    private $diagnostico;
    private $tratamiento;
    private $resultados;
    private $observaciones;
    private $medico_nombre;
    private $medico_apellidos;
    private $especialidad;
    private $paciente_nombre;
    private $paciente_apellidos;
    
    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->id_consulta = $datos['id_consulta'] ?? null;
            $this->id_paciente = $datos['id_paciente'] ?? null;
            $this->id_medico = $datos['id_medico'] ?? null;
            $this->fecha = $datos['fecha'] ?? null;
            $this->diagnostico = $datos['diagnostico'] ?? '';
            $this->tratamiento = $datos['tratamiento'] ?? '';
            $this->resultados = $datos['resultados'] ?? '';
            $this->observaciones = $datos['observaciones'] ?? '';
            $this->medico_nombre = $datos['medico_nombre'] ?? '';
            $this->medico_apellidos = $datos['medico_apellidos'] ?? '';
            $this->especialidad = $datos['especialidad'] ?? '';
            $this->paciente_nombre = $datos['paciente_nombre'] ?? '';
            $this->paciente_apellidos = $datos['paciente_apellidos'] ?? '';
        }
    }
    
    // Getters
    public function getIdConsulta() { return $this->id_consulta; }
    public function getIdPaciente() { return $this->id_paciente; }
    public function getIdMedico() { return $this->id_medico; }
    public function getFecha() { return $this->fecha; }
    public function getDiagnostico() { return $this->diagnostico; }
    public function getTratamiento() { return $this->tratamiento; }
    public function getResultados() { return $this->resultados; }
    public function getObservaciones() { return $this->observaciones; }
    public function getMedicoNombre() { return $this->medico_nombre; }
    public function getMedicoApellidos() { return $this->medico_apellidos; }
    public function getEspecialidad() { return $this->especialidad; }
    public function getPacienteNombre() { return $this->paciente_nombre; }
    public function getPacienteApellidos() { return $this->paciente_apellidos; }
    
    // Setters
    public function setIdConsulta($id) { $this->id_consulta = $id; }
    public function setIdPaciente($id) { $this->id_paciente = $id; }
    public function setIdMedico($id) { $this->id_medico = $id; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function setDiagnostico($diagnostico) { $this->diagnostico = $diagnostico; }
    public function setTratamiento($tratamiento) { $this->tratamiento = $tratamiento; }
    public function setResultados($resultados) { $this->resultados = $resultados; }
    public function setObservaciones($observaciones) { $this->observaciones = $observaciones; }
    public function setMedicoNombre($nombre) { $this->medico_nombre = $nombre; }
    public function setMedicoApellidos($apellidos) { $this->medico_apellidos = $apellidos; }
    public function setEspecialidad($especialidad) { $this->especialidad = $especialidad; }
    public function setPacienteNombre($nombre) { $this->paciente_nombre = $nombre; }
    public function setPacienteApellidos($apellidos) { $this->paciente_apellidos = $apellidos; }
    
    public function toArray() {
        return [
            'id_consulta' => $this->id_consulta,
            'id_paciente' => $this->id_paciente,
            'id_medico' => $this->id_medico,
            'fecha' => $this->fecha,
            'diagnostico' => $this->diagnostico,
            'tratamiento' => $this->tratamiento,
            'resultados' => $this->resultados,
            'observaciones' => $this->observaciones,
            'medico_nombre' => $this->medico_nombre,
            'medico_apellidos' => $this->medico_apellidos,
            'especialidad' => $this->especialidad,
            'paciente_nombre' => $this->paciente_nombre,
            'paciente_apellidos' => $this->paciente_apellidos
        ];
    }
}
