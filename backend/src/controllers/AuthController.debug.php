<?php
/**
 * DEBUG VERSION of AuthController
 * Registra todos los pasos del login para diagnóstico
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

$debug = [];

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $debug['input_recibido'] = $input;

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $role = $input['role'] ?? 'paciente';

    $debug['validación_básica'] = [
        'email_vacío' => empty($email),
        'password_vacío' => empty($password),
        'email_válido' => filter_var($email, FILTER_VALIDATE_EMAIL)
    ];

    if (empty($email) || empty($password)) {
        throw new Exception('Email y contraseña son requeridos');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }

    $usuario = null;
    $tipoUsuario = '';

    // DEBUG para médicos
    if ($role === 'medico') {
        $debug['rol'] = 'medico';
        $medicoDAO = new MedicoDAO();
        $medico = $medicoDAO->buscarPorEmail($email);

        if ($medico) {
            $debug['médico_encontrado'] = true;
            $debug['médico_activo'] = $medico->getActivo();
            $debug['médico_id'] = $medico->getIdMedico();
            $debug['médico_nombre'] = $medico->getNombre();
            $debug['medico_email'] = $medico->getEmail();

            $hash_almacenado = $medico->getContrasenaHash();
            $debug['hash_almacenado'] = substr($hash_almacenado, 0, 20) . '...';
            $debug['hash_longitud'] = strlen($hash_almacenado);

            if ($medico->getActivo()) {
                $debug['ejecutando_password_verify'] = true;
                $verify_result = password_verify($password, $hash_almacenado);
                $debug['password_verify_resultado'] = $verify_result;

                if ($verify_result) {
                    $usuario = $medico;
                    $tipoUsuario = 'medico';
                } else {
                    $debug['error'] = 'password_verify retornó false';
                }
            } else {
                $debug['error'] = 'médico inactivo';
            }
        } else {
            $debug['médico_encontrado'] = false;
            $debug['error'] = 'médico no encontrado en base de datos';
        }
    }

    if ($usuario) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Login exitoso',
            'usuario' => [
                'id' => $_SESSION['user_id'] ?? $medico->getIdMedico(),
                'nombre' => $_SESSION['user_nombre'] ?? $medico->getNombre(),
                'email' => $_SESSION['user_email'] ?? $medico->getEmail(),
                'tipo' => $tipoUsuario
            ],
            'redirect' => 'medico.html'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'Credenciales inválidas o usuario inactivo',
            'debug' => $debug
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage(),
        'debug' => $debug
    ]);
}
