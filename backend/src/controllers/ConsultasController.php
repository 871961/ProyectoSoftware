<?php
/**
 * Archivo: ConsultasController.php
 * Descripcion: Endpoints de sesion y modulo de consultas para medico/paciente
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

ini_set('display_errors', '0');
ob_start();

function sendJson($payload, $status = 200) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    echo json_encode($payload);
    exit();
}

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJson(['success' => true], 200);
}

require_once '../dao/ConsultaDAO.php';
require_once '../dao/PacienteDAO.php';
require_once '../vo/ConsultaVO.php';

function requireSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
        sendJson([
            'success' => false,
            'mensaje' => 'Sesion no valida'
        ], 401);
    }
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['accion'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    if ($accion === 'sesion') {
        requireSession();
        sendJson([
            'success' => true,
            'usuario' => [
                'id' => $_SESSION['user_id'],
                'tipo' => $_SESSION['user_tipo'],
                'nombre' => $_SESSION['user_nombre'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'especialidad' => $_SESSION['user_especialidad'] ?? '',
                'tipo_medico' => $_SESSION['user_tipo_medico'] ?? null
            ]
        ]);
    }

    if ($accion === 'logout') {
        if ($metodo !== 'POST') {
            throw new Exception('Metodo no permitido');
        }
        session_destroy();
        sendJson([
            'success' => true,
            'mensaje' => 'Sesion cerrada'
        ]);
    }

    requireSession();
    $consultaDAO = new ConsultaDAO();

    switch ($accion) {
        case 'listar_pacientes':
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }

            $pacienteDAO = new PacienteDAO();
            
            // Si es médico general, solo mostrar sus pacientes asignados
            if (isset($_SESSION['user_tipo_medico']) && $_SESSION['user_tipo_medico'] === 'general') {
                $pacientes = $pacienteDAO->obtenerPorMedicoGeneral($_SESSION['user_id']);
            } else {
                // Si es especialista, puede ver todos los pacientes
                $pacientes = $pacienteDAO->obtenerTodos();
            }
            
            $activos = array_values(array_filter($pacientes, function ($paciente) {
                return $paciente->getActivo();
            }));

            sendJson([
                'success' => true,
                'data' => array_map(function ($paciente) {
                    return $paciente->toArray();
                }, $activos)
            ]);
            break;

        case 'crear_consulta':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }

            $idPaciente = trim($input['id_paciente'] ?? '');
            $diagnostico = trim($input['diagnostico'] ?? '');
            if ($idPaciente === '' || $diagnostico === '') {
                throw new Exception('Paciente y diagnostico son obligatorios');
            }

            $pacienteDAO = new PacienteDAO();
            $paciente = $pacienteDAO->buscarPorId($idPaciente);
            if (!$paciente) {
                throw new Exception('Paciente no encontrado o inactivo');
            }

            $consulta = new ConsultaVO([
                'id_paciente' => $idPaciente,
                'id_medico' => $_SESSION['user_id'],
                'fecha' => !empty($input['fecha']) ? $input['fecha'] : date('Y-m-d H:i:s'),
                'diagnostico' => $diagnostico,
                'tratamiento' => trim($input['tratamiento'] ?? ''),
                'observaciones' => trim($input['observaciones'] ?? '')
            ]);

            $ok = $consultaDAO->insertar($consulta);
            sendJson([
                'success' => (bool) $ok,
                'mensaje' => $ok ? 'Consulta registrada' : 'No se pudo registrar la consulta'
            ]);
            break;

        case 'actualizar_consulta':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }

            $idConsulta = (int) ($input['id_consulta'] ?? 0);
            $diagnostico = trim($input['diagnostico'] ?? '');
            if ($idConsulta <= 0 || $diagnostico === '') {
                throw new Exception('ID de consulta y diagnostico son obligatorios');
            }

            $consultaExistente = $consultaDAO->obtenerPorId($idConsulta);
            if (!$consultaExistente) {
                throw new Exception('Consulta no encontrada');
            }

            $consulta = new ConsultaVO([
                'id_consulta' => $idConsulta,
                'id_paciente' => $consultaExistente->getIdPaciente(),
                'id_medico' => $_SESSION['user_id'],
                'fecha' => !empty($input['fecha']) ? $input['fecha'] : $consultaExistente->getFecha(),
                'diagnostico' => $diagnostico,
                'tratamiento' => trim($input['tratamiento'] ?? ''),
                'observaciones' => trim($input['observaciones'] ?? '')
            ]);

            $ok = $consultaDAO->actualizarPorMedico($consulta, $_SESSION['user_id']);
            if (!$ok) {
                throw new Exception('No se pudo actualizar la consulta (puede no pertenecer al medico actual)');
            }

            sendJson([
                'success' => true,
                'mensaje' => 'Consulta actualizada'
            ]);
            break;

        case 'eliminar_consulta':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }

            $idConsulta = (int) ($input['id_consulta'] ?? 0);
            if ($idConsulta <= 0) {
                throw new Exception('ID de consulta obligatorio');
            }

            $ok = $consultaDAO->eliminarPorMedico($idConsulta, $_SESSION['user_id']);
            if (!$ok) {
                throw new Exception('No se pudo eliminar la consulta (puede no pertenecer al medico actual)');
            }

            sendJson([
                'success' => true,
                'mensaje' => 'Consulta eliminada'
            ]);
            break;

        case 'mis_consultas':
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            if ($fechaDesde !== '' && $fechaHasta !== '') {
                $fechaDesde .= ' 00:00:00';
                $fechaHasta .= ' 23:59:59';
            } else {
                $fechaDesde = null;
                $fechaHasta = null;
            }

            if ($_SESSION['user_tipo'] === 'medico') {
                $consultas = $consultaDAO->obtenerPorMedico($_SESSION['user_id'], $fechaDesde, $fechaHasta);
            } elseif ($_SESSION['user_tipo'] === 'paciente') {
                $consultas = $consultaDAO->obtenerPorPaciente($_SESSION['user_id']);
                if ($fechaDesde && $fechaHasta) {
                    $consultas = array_values(array_filter($consultas, function ($consulta) use ($fechaDesde, $fechaHasta) {
                        $f = strtotime($consulta->getFecha());
                        return $f >= strtotime($fechaDesde) && $f <= strtotime($fechaHasta);
                    }));
                }
            } else {
                throw new Exception('No autorizado');
            }

            sendJson([
                'success' => true,
                'data' => array_map(function ($consulta) {
                    return $consulta->toArray();
                }, $consultas)
            ]);
            break;

        default:
            throw new Exception('Accion no valida');
    }
} catch (Throwable $e) {
    sendJson([
        'success' => false,
        'mensaje' => $e->getMessage()
    ], 400);
}

