<?php
/**
 * Archivo: AuthController.php
 * Descripcion: Controlador para autenticacion (login) de pacientes, medicos y administradores
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../dao/PacienteDAO.php';
require_once '../dao/MedicoDAO.php';
require_once '../dao/AdministradorDAO.php';

function responderError(int $codigo, string $mensaje, ?string $campo = null): void
{
    http_response_code($codigo);

    $respuesta = [
        'success' => false,
        'mensaje' => $mensaje
    ];

    if ($campo !== null) {
        $respuesta['campo'] = $campo;
    }

    echo json_encode($respuesta);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metodo no permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'paciente';

    if ($email === '') {
        responderError(400, 'El email es obligatorio', 'email');
    }

    if ($password === '') {
        responderError(400, 'La contrasena es obligatoria', 'password');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responderError(400, 'Introduce un email valido', 'email');
    }

    $usuario = null;
    $tipoUsuario = '';

    if ($role === 'paciente') {
        $pacienteDAO = new PacienteDAO();
        $paciente = $pacienteDAO->buscarPorEmail($email);

        if (!$paciente || !$paciente->getActivo()) {
            responderError(401, 'No existe ninguna cuenta activa con ese email', 'email');
        }

        if (!password_verify($password, $paciente->getContrasenaHash())) {
            responderError(401, 'La contrasena no es correcta', 'password');
        }

        $usuario = $paciente;
        $tipoUsuario = 'paciente';

        $_SESSION['user_id'] = $paciente->getIdPaciente();
        $_SESSION['user_tipo'] = 'paciente';
        $_SESSION['user_nombre'] = $paciente->getNombre() . ' ' . $paciente->getApellidos();
        $_SESSION['user_email'] = $paciente->getEmail();
    } elseif ($role === 'medico') {
        $medicoDAO = new MedicoDAO();
        $medico = $medicoDAO->buscarPorEmail($email);

        if (!$medico || !$medico->getActivo()) {
            responderError(401, 'No existe ninguna cuenta activa con ese email', 'email');
        }

        if (!password_verify($password, $medico->getContrasenaHash())) {
            responderError(401, 'La contrasena no es correcta', 'password');
        }

        $usuario = $medico;
        $tipoUsuario = 'medico';

        $_SESSION['user_id'] = $medico->getIdMedico();
        $_SESSION['user_tipo'] = 'medico';
        $_SESSION['user_nombre'] = $medico->getNombre() . ' ' . $medico->getApellidos();
        $_SESSION['user_email'] = $medico->getEmail();
        $_SESSION['user_especialidad'] = $medico->getEspecialidad();
        $_SESSION['user_tipo_medico'] = $medico->getTipoMedico();
    } elseif ($role === 'admin') {
        $adminDAO = new AdministradorDAO();
        $admin = $adminDAO->buscarPorEmail($email);

        if (!$admin || !$admin->getActivo()) {
            responderError(401, 'No existe ninguna cuenta activa con ese email', 'email');
        }

        if (!password_verify($password, $admin->getContrasenaHash())) {
            responderError(401, 'La contrasena no es correcta', 'password');
        }

        $usuario = $admin;
        $tipoUsuario = 'admin';

        $_SESSION['user_id'] = $admin->getIdAdmin();
        $_SESSION['user_tipo'] = 'admin';
        $_SESSION['user_nombre'] = $admin->getNombreCompleto();
        $_SESSION['user_email'] = $admin->getEmail();
    } else {
        responderError(400, 'Tipo de usuario no valido');
    }

    echo json_encode([
        'success' => true,
        'mensaje' => 'Login exitoso',
        'usuario' => [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'email' => $_SESSION['user_email'],
            'tipo' => $tipoUsuario
        ],
        'redirect' => $tipoUsuario === 'paciente'
            ? 'paciente.html'
            : ($tipoUsuario === 'medico' ? 'medico.html' : 'admin.html')
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
