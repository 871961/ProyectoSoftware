<?php
/**
 * Archivo: AuthController.php
 * Descripción: Controlador para autenticación (login) de pacientes y médicos
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../dao/PacienteDAO.php';
require_once '../dao/MedicoDAO.php';
require_once '../dao/AdministradorDAO.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    
    if ($metodo !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'paciente'; // Default a paciente
    
    if (empty($email) || empty($password)) {
        throw new Exception('Email y contraseña son requeridos');
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }
    
    $usuario = null;
    $tipoUsuario = '';
    
    // Intentar autenticar según el rol
    if ($role === 'paciente') {
        $pacienteDAO = new PacienteDAO();
        $paciente = $pacienteDAO->buscarPorEmail($email);
        
        if ($paciente && $paciente->getActivo()) {
            // Verificar contraseña
            if (password_verify($password, $paciente->getContrasenaHash())) {
                $usuario = $paciente;
                $tipoUsuario = 'paciente';
                
                $_SESSION['user_id'] = $paciente->getIdPaciente();
                $_SESSION['user_tipo'] = 'paciente';
                $_SESSION['user_nombre'] = $paciente->getNombre() . ' ' . $paciente->getApellidos();
                $_SESSION['user_email'] = $paciente->getEmail();
            }
        }
    } elseif ($role === 'medico') {
        $medicoDAO = new MedicoDAO();
        $medico = $medicoDAO->buscarPorEmail($email);
        
        if ($medico && $medico->getActivo()) {
            // Verificar contraseña
            if (password_verify($password, $medico->getContrasenaHash())) {
                $usuario = $medico;
                $tipoUsuario = 'medico';
                
                $_SESSION['user_id'] = $medico->getIdMedico();
                $_SESSION['user_tipo'] = 'medico';
                $_SESSION['user_nombre'] = $medico->getNombre() . ' ' . $medico->getApellidos();
                $_SESSION['user_email'] = $medico->getEmail();
                $_SESSION['user_especialidad'] = $medico->getEspecialidad();
            }
        }
    } elseif ($role === 'admin') {
        // Los administradores también pueden iniciar sesión aquí
        $adminDAO = new AdministradorDAO();
        $admin = $adminDAO->buscarPorEmail($email);
        
        if ($admin && $admin->getActivo()) {
            // Verificar contraseña
            if (password_verify($password, $admin->getContrasenaHash())) {
                $usuario = $admin;
                $tipoUsuario = 'admin';
                
                $_SESSION['user_id'] = $admin->getIdAdmin();
                $_SESSION['user_tipo'] = 'admin';
                $_SESSION['user_nombre'] = $admin->getNombreCompleto();
                $_SESSION['user_email'] = $admin->getEmail();
            }
        }
    }
    
    // Verificar si la autenticación fue exitosa
    if ($usuario) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Login exitoso',
            'usuario' => [
                'id' => $_SESSION['user_id'],
                'nombre' => $_SESSION['user_nombre'],
                'email' => $_SESSION['user_email'],
                'tipo' => $tipoUsuario
            ],
            'redirect' => $tipoUsuario === 'paciente' ? 'paciente.html' : 
                         ($tipoUsuario === 'medico' ? 'medico.html' : 'admin.html')
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Credenciales inválidas o usuario inactivo'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
