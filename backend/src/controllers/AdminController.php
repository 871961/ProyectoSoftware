<?php
/**
 * Archivo: AdminController.php
 * Descripción: Controlador para las operaciones del panel de administrador
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../dao/AdministradorDAO.php';
require_once '../dao/PacienteDAO.php';
require_once '../dao/MedicoDAO.php';
require_once '../vo/AdministradorVO.php';
require_once '../vo/PacienteVO.php';
require_once '../vo/MedicoVO.php';

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    $accion = $_GET['accion'] ?? '';
    
    $adminDAO = new AdministradorDAO();
    $pacienteDAO = new PacienteDAO();
    $medicoDAO = new MedicoDAO();
    
    switch ($accion) {
        case 'login':
            if ($metodo === 'POST') {
                $email = $input['email'] ?? '';
                $contrasena = $input['contrasena'] ?? '';
                
                if (empty($email) || empty($contrasena)) {
                    throw new Exception('Email y contraseña son requeridos');
                }
                
                $admin = $adminDAO->autenticar($email, $contrasena);
                
                if ($admin) {
                    $_SESSION['admin_id'] = $admin->getIdAdmin();
                    $_SESSION['admin_nombre'] = $admin->getNombreCompleto();
                    
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Login exitoso',
                        'admin' => $admin->toArray()
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Credenciales inválidas'
                    ]);
                }
            }
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true, 'mensaje' => 'Logout exitoso']);
            break;
            
        case 'estadisticas':
            if (!isset($_SESSION['admin_id'])) {
                throw new Exception('No autorizado');
            }
            
            $estadisticas = $adminDAO->obtenerEstadisticas();
            echo json_encode(['success' => true, 'data' => $estadisticas]);
            break;
            
        case 'logs':
            if (!isset($_SESSION['admin_id'])) {
                throw new Exception('No autorizado');
            }
            
            $logs = $adminDAO->obtenerLogsRecientes();
            echo json_encode(['success' => true, 'data' => $logs]);
            break;
            
        case 'crear_medico':
            if ($metodo === 'POST' && isset($_SESSION['admin_id'])) {
                $datos = $input;
                $datos['contrasena_hash'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
                
                $medico = new MedicoVO($datos);
                $resultado = $medicoDAO->insertar($medico, $_SESSION['admin_id']);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Médico creado exitosamente',
                        'medico' => $medico->toArray()
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al crear médico'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        case 'crear_paciente':
            if ($metodo === 'POST' && isset($_SESSION['admin_id'])) {
                $datos = $input;
                $datos['contrasena_hash'] = password_hash($datos['contrasena'], PASSWORD_DEFAULT);
                
                $paciente = new PacienteVO($datos);
                $resultado = $pacienteDAO->insertar($paciente);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Paciente creado exitosamente',
                        'paciente' => $paciente->toArray()
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al crear paciente'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        case 'listar_medicos':
            if (!isset($_SESSION['admin_id'])) {
                throw new Exception('No autorizado');
            }
            
            $medicos = $medicoDAO->obtenerTodos();
            $medicosArray = array_map(function($medico) {
                return $medico->toArray();
            }, $medicos);
            
            echo json_encode(['success' => true, 'data' => $medicosArray]);
            break;
            
        case 'listar_pacientes':
            if (!isset($_SESSION['admin_id'])) {
                throw new Exception('No autorizado');
            }
            
            $pacientes = $pacienteDAO->obtenerTodos();
            $pacientesArray = array_map(function($paciente) {
                return $paciente->toArray();
            }, $pacientes);
            
            echo json_encode(['success' => true, 'data' => $pacientesArray]);
            break;
            
        case 'dar_baja_medico':
            if ($metodo === 'DELETE' && isset($_SESSION['admin_id'])) {
                $id_medico = $_GET['id'] ?? null;
                if (!$id_medico) {
                    throw new Exception('ID de médico requerido');
                }
                
                $resultado = $medicoDAO->darDeBaja($id_medico, $_SESSION['admin_id']);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Médico dado de baja exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al dar de baja al médico'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        case 'dar_baja_paciente':
            if ($metodo === 'DELETE' && isset($_SESSION['admin_id'])) {
                $id_paciente = $_GET['id'] ?? null;
                if (!$id_paciente) {
                    throw new Exception('ID de paciente requerido');
                }
                
                $resultado = $pacienteDAO->darDeBaja($id_paciente, $_SESSION['admin_id']);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Paciente dado de baja exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al dar de baja al paciente'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        case 'dar_alta_medico':
            if ($metodo === 'POST' && isset($_SESSION['admin_id'])) {
                $id_medico = $_GET['id'] ?? null;
                if (!$id_medico) {
                    throw new Exception('ID de médico requerido');
                }
                
                $resultado = $medicoDAO->darDeAlta($id_medico, $_SESSION['admin_id']);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Médico dado de alta exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al dar de alta al médico'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        case 'dar_alta_paciente':
            if ($metodo === 'POST' && isset($_SESSION['admin_id'])) {
                $id_paciente = $_GET['id'] ?? null;
                if (!$id_paciente) {
                    throw new Exception('ID de paciente requerido');
                }
                
                $resultado = $pacienteDAO->darDeAlta($id_paciente, $_SESSION['admin_id']);
                
                if ($resultado) {
                    echo json_encode([
                        'success' => true,
                        'mensaje' => 'Paciente dado de alta exitosamente'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'Error al dar de alta al paciente'
                    ]);
                }
            } else {
                throw new Exception('No autorizado o método no permitido');
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'mensaje' => 'Acción no válida'
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}