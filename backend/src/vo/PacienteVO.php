<?php
/**
 * Archivo: PacienteVO.php
 * Descripción: Value Object para la entidad Paciente
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class PacienteVO {
    private $dni;
    private $nombre;
    private $apellidos;
    private $email;
    private $contrasena_hash;
    private $telefono;
    private $direccion;
    private $fecha_nacimiento;
    private $num_seguridad_social;
    private $id_medico_general; // Médico de cabecera asignado
    private $activo;
    private $fecha_baja;
    
    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->dni = $datos['dni'] ?? null;
            $this->nombre = $datos['nombre'] ?? '';
            $this->apellidos = $datos['apellidos'] ?? '';
            $this->email = $datos['email'] ?? '';
            $this->contrasena_hash = $datos['contrasena_hash'] ?? '';
            $this->telefono = $datos['telefono'] ?? '';
            $this->direccion = $datos['direccion'] ?? '';
            $this->fecha_nacimiento = $datos['fecha_nacimiento'] ?? '';
            $this->num_seguridad_social = $datos['num_seguridad_social'] ?? null;
            $this->id_medico_general = $datos['id_medico_general'] ?? null;
            $this->activo = $datos['activo'] ?? true;
            $this->fecha_baja = $datos['fecha_baja'] ?? null;
        }
    }
    
    // Getters
    public function getDni() { return $this->dni; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getEmail() { return $this->email; }
    public function getContrasenaHash() { return $this->contrasena_hash; }
    public function getTelefono() { return $this->telefono; }
    public function getDireccion() { return $this->direccion; }
    public function getFechaNacimiento() { return $this->fecha_nacimiento; }
    public function getNumSeguridadSocial() { return $this->num_seguridad_social; }
    public function getIdMedicoGeneral() { return $this->id_medico_general; }
    public function getActivo() { return $this->activo; }
    public function getFechaBaja() { return $this->fecha_baja; }
    
    // Setters
    public function setDni($dni) { $this->dni = $dni; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setEmail($email) { $this->email = $email; }
    public function setContrasenaHash($hash) { $this->contrasena_hash = $hash; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setDireccion($direccion) { $this->direccion = $direccion; }
    public function setFechaNacimiento($fecha) { $this->fecha_nacimiento = $fecha; }
    public function setNumSeguridadSocial($num) { $this->num_seguridad_social = $num; }
    public function setIdMedicoGeneral($id) { $this->id_medico_general = $id; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setFechaBaja($fecha) { $this->fecha_baja = $fecha; }
    
    public function getNombreCompleto() {
        return $this->nombre . ' ' . $this->apellidos;
    }
    
    public function toArray() {
        return [
            'dni' => $this->dni,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'direccion' => $this->direccion,
            'fecha_nacimiento' => $this->fecha_nacimiento,
            'num_seguridad_social' => $this->num_seguridad_social,
            'id_medico_general' => $this->id_medico_general,
            'activo' => $this->activo,
            'fecha_baja' => $this->fecha_baja
        ];
    }
    
    // Mantener compatibilidad con código existente que usa id_paciente
    public function getIdPaciente() { return $this->dni; }
    public function setIdPaciente($id) { $this->dni = $id; }
}