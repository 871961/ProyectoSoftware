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
    
    // Setters
    public function setIdConsulta($id) { $this->id_consulta = $id; }
    public function setIdPaciente($id) { $this->id_paciente = $id; }
    public function setIdMedico($id) { $this->id_medico = $id; }
    public function setFecha($fecha) { $this->fecha = $fecha; }
    public function setDiagnostico($diagnostico) { $this->diagnostico = $diagnostico; }
    public function setTratamiento($tratamiento) { $this->tratamiento = $tratamiento; }
    public function setResultados($resultados) { $this->resultados = $resultados; }
    public function setObservaciones($observaciones) { $this->observaciones = $observaciones; }
    
    public function toArray() {
        return [
            'id_consulta' => $this->id_consulta,
            'id_paciente' => $this->id_paciente,
            'id_medico' => $this->id_medico,
            'fecha' => $this->fecha,
            'diagnostico' => $this->diagnostico,
            'tratamiento' => $this->tratamiento,
            'resultados' => $this->resultados,
            'observaciones' => $this->observaciones
        ];
    }
}