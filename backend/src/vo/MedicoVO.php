<?php
/**
 * Archivo: MedicoVO.php
 * Descripción: Value Object para la entidad Médico
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class MedicoVO {
    private $id_medico;
    private $nombre;
    private $apellidos;
    private $email;
    private $contrasena_hash;
    private $telefono;
    private $direccion;
    private $num_colegiado;
    private $tipo_medico; // 'general' o 'especialista'
    private $especialidad; // Solo para especialistas
    private $activo;
    private $fecha_baja;
    
    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->id_medico = $datos['id_medico'] ?? null;
            $this->nombre = $datos['nombre'] ?? '';
            $this->apellidos = $datos['apellidos'] ?? '';
            $this->email = $datos['email'] ?? '';
            $this->contrasena_hash = $datos['contrasena_hash'] ?? '';
            $this->telefono = $datos['telefono'] ?? '';
            $this->direccion = $datos['direccion'] ?? '';
            $this->num_colegiado = $datos['num_colegiado'] ?? '';
            $this->tipo_medico = $datos['tipo_medico'] ?? 'general';
            $this->especialidad = $datos['especialidad'] ?? null;
            $this->activo = $datos['activo'] ?? true;
            $this->fecha_baja = $datos['fecha_baja'] ?? null;
        }
    }
    
    // Getters
    public function getIdMedico() { return $this->id_medico; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getEmail() { return $this->email; }
    public function getContrasenaHash() { return $this->contrasena_hash; }
    public function getTelefono() { return $this->telefono; }
    public function getDireccion() { return $this->direccion; }
    public function getNumColegiado() { return $this->num_colegiado; }
    public function getTipoMedico() { return $this->tipo_medico; }
    public function getEspecialidad() { return $this->especialidad; }
    public function getActivo() { return $this->activo; }
    public function getFechaBaja() { return $this->fecha_baja; }
    
    // Setters
    public function setIdMedico($id) { $this->id_medico = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setEmail($email) { $this->email = $email; }
    public function setContrasenaHash($hash) { $this->contrasena_hash = $hash; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setDireccion($direccion) { $this->direccion = $direccion; }
    public function setNumColegiado($num) { $this->num_colegiado = $num; }
    public function setTipoMedico($tipo) { $this->tipo_medico = $tipo; }
    public function setEspecialidad($especialidad) { $this->especialidad = $especialidad; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setFechaBaja($fecha) { $this->fecha_baja = $fecha; }
    
    public function getNombreCompleto() {
        return 'Dr/a. ' . $this->nombre . ' ' . $this->apellidos;
    }
    
    public function esGeneral() {
        return $this->tipo_medico === 'general';
    }
    
    public function esEspecialista() {
        return $this->tipo_medico === 'especialista';
    }
    
    public function toArray() {
        return [
            'id_medico' => $this->id_medico,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'num_colegiado' => $this->num_colegiado,
            'tipo_medico' => $this->tipo_medico,
            'especialidad' => $this->especialidad,
            'activo' => $this->activo,
            'fecha_baja' => $this->fecha_baja
        ];
    }
}