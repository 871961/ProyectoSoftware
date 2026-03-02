<?php
/**
 * Archivo: AdministradorVO.php
 * Descripción: Value Object para la entidad Administrador
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

class AdministradorVO {
    private $id_admin;
    private $nombre;
    private $apellidos;
    private $email;
    private $contrasena_hash;
    private $activo;
    private $fecha_baja;
    
    public function __construct($datos = []) {
        if (!empty($datos)) {
            $this->id_admin = $datos['id_admin'] ?? null;
            $this->nombre = $datos['nombre'] ?? '';
            $this->apellidos = $datos['apellidos'] ?? '';
            $this->email = $datos['email'] ?? '';
            $this->contrasena_hash = $datos['contrasena_hash'] ?? '';
            $this->activo = $datos['activo'] ?? true;
            $this->fecha_baja = $datos['fecha_baja'] ?? null;
        }
    }
    
    // Getters
    public function getIdAdmin() { return $this->id_admin; }
    public function getNombre() { return $this->nombre; }
    public function getApellidos() { return $this->apellidos; }
    public function getEmail() { return $this->email; }
    public function getContrasenaHash() { return $this->contrasena_hash; }
    public function getActivo() { return $this->activo; }
    public function getFechaBaja() { return $this->fecha_baja; }
    
    // Setters
    public function setIdAdmin($id) { $this->id_admin = $id; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellidos($apellidos) { $this->apellidos = $apellidos; }
    public function setEmail($email) { $this->email = $email; }
    public function setContrasenaHash($hash) { $this->contrasena_hash = $hash; }
    public function setActivo($activo) { $this->activo = $activo; }
    public function setFechaBaja($fecha) { $this->fecha_baja = $fecha; }
    
    public function getNombreCompleto() {
        return $this->nombre . ' ' . $this->apellidos;
    }
    
    public function toArray() {
        return [
            'id_admin' => $this->id_admin,
            'nombre' => $this->nombre,
            'apellidos' => $this->apellidos,
            'email' => $this->email,
            'activo' => $this->activo,
            'fecha_baja' => $this->fecha_baja
        ];
    }
}