<?php
/**
 * Archivo: RegistroController.php
 * Descripción: Controlador para el registro de nuevos pacientes
 * Nota: Solo los pacientes pueden auto-registrarse. Los médicos y administradores 
 *       son registrados por administradores directamente.
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
require_once '../vo/PacienteVO.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    
    if ($metodo !== 'POST') {
        throw new Exception('Método no permitido');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar datos requeridos
    $dni = trim($input['dni'] ?? '');
    $nombre = trim($input['nombre'] ?? '');
    $apellidos = trim($input['apellidos'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $telefono = trim($input['telefono'] ?? '');
    $direccion = trim($input['direccion'] ?? '');
    $fecha_nacimiento = $input['fecha_nacimiento'] ?? '';
    
    // Validaciones
    if (empty($dni)) {
        throw new Exception('El DNI/NIE es requerido');
    }
    
    // Validar formato DNI/NIE español
    if (!preg_match('/^[0-9XYZ][0-9]{7}[A-Z]$/', strtoupper($dni))) {
        throw new Exception('Formato de DNI/NIE inválido. Debe tener 8 dígitos y una letra');
    }
    
    if (empty($nombre)) {
        throw new Exception('El nombre es requerido');
    }
    
    if (empty($apellidos)) {
        throw new Exception('Los apellidos son requeridos');
    }
    
    if (empty($email)) {
        throw new Exception('El email es requerido');
    }
    
    // Validar formato de email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }
    
    if (empty($password)) {
        throw new Exception('La contraseña es requerida');
    }
    
    // Validar fortaleza de contraseña
    if (strlen($password) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        throw new Exception('La contraseña debe contener al menos una minúscula');
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        throw new Exception('La contraseña debe contener al menos una mayúscula');
    }
    
    if (!preg_match('/\d/', $password)) {
        throw new Exception('La contraseña debe contener al menos un número');
    }
    
    if (empty($telefono)) {
        throw new Exception('El teléfono es requerido');
    }
    
    if (empty($direccion)) {
        throw new Exception('La dirección es requerida');
    }
    
    if (empty($fecha_nacimiento)) {
        throw new Exception('La fecha de nacimiento es requerida');
    }
    
    // Validar formato de fecha
    $fecha = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    if (!$fecha || $fecha->format('Y-m-d') !== $fecha_nacimiento) {
        throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
    }
    
    // Validar edad mínima (debe ser mayor de 18 años)
    $hoy = new DateTime();
    $edad = $hoy->diff($fecha)->y;
    if ($edad < 18) {
        throw new Exception('Debes tener al menos 18 años para registrarte');
    }
    
    // Verificar si el DNI ya existe
    $pacienteDAO = new PacienteDAO();
    $pacientePorDni = $pacienteDAO->buscarPorId($dni);
    
    if ($pacientePorDni) {
        http_response_code(409); // Conflict
        throw new Exception('Este DNI/NIE ya está registrado. Por favor, verifica tus datos o inicia sesión.');
    }
    
    // Verificar si el email ya existe (buscar activos e inactivos)
    $pacienteExistente = $pacienteDAO->buscarPorEmailInclusoInactivos($email);
    
    if ($pacienteExistente) {
        http_response_code(409); // Conflict
        throw new Exception('Este email ya está registrado. Por favor, utiliza otro email o inicia sesión.');
    }
    
    // Hash de la contraseña
    $contrasenaHash = password_hash($password, PASSWORD_BCRYPT);
    
    // Crear el objeto PacienteVO
    $paciente = new PacienteVO([
        'dni' => strtoupper($dni),
        'nombre' => $nombre,
        'apellidos' => $apellidos,
        'email' => $email,
        'contrasena_hash' => $contrasenaHash,
        'telefono' => $telefono,
        'direccion' => $direccion,
        'fecha_nacimiento' => $fecha_nacimiento,
        'num_seguridad_social' => null,
        'activo' => true,
        'fecha_baja' => null
    ]);
    
    // Insertar el paciente en la base de datos
    $dniPaciente = $pacienteDAO->insertar($paciente);
    
    if ($dniPaciente) {
        // Registro exitoso - crear sesión automáticamente
        $_SESSION['user_id'] = $dniPaciente;
        $_SESSION['user_tipo'] = 'paciente';
        $_SESSION['user_nombre'] = $nombre . ' ' . $apellidos;
        $_SESSION['user_email'] = $email;
        
        echo json_encode([
            'success' => true,
            'mensaje' => '¡Cuenta creada correctamente!',
            'usuario' => [
                'id' => $dniPaciente,
                'nombre' => $nombre . ' ' . $apellidos,
                'email' => $email,
                'tipo' => 'paciente'
            ],
            'redirect' => 'index.html'
        ]);
    } else {
        throw new Exception('Error al crear la cuenta. Por favor, intenta de nuevo.');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
