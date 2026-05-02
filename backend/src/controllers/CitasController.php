<?php
/**
 * Archivo: CitasController.php
 * Descripcion: Endpoints para el módulo de citas médicas
 * Fecha: Mayo 2026
 */

ini_set('display_errors', '0');
ob_start();

function sendJson($payload, $status = 200) {
    while (ob_get_level() > 0) ob_end_clean();
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

require_once '../dao/CitaDAO.php';
require_once '../vo/CitaVO.php';
require_once '../dao/MedicoDAO.php';
require_once '../dao/DependienteDAO.php';

function requireSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
        sendJson(['success' => false, 'mensaje' => 'Sesion no valida'], 401);
    }
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['accion'] ?? '';
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];

    if ($accion === 'sesion') {
        requireSession();
        sendJson([
            'success' => true,
            'usuario' => [
                'id'     => $_SESSION['user_id'],
                'tipo'   => $_SESSION['user_tipo'],
                'nombre' => $_SESSION['user_nombre'] ?? '',
            ]
        ]);
    }

    requireSession();
    $dao = new CitaDAO();

    switch ($accion) {

        // ── Paciente solicita cita ──────────────────────────────────────────────
        case 'solicitar':
            if ($metodo !== 'POST') throw new Exception('Método no permitido');
            if ($_SESSION['user_tipo'] !== 'paciente') throw new Exception('Solo pacientes pueden solicitar citas');

            $idMedico   = (int) ($input['id_medico'] ?? 0);
            $fecha      = trim($input['fecha'] ?? '');
            $hora       = trim($input['hora'] ?? '');
            $motivo     = trim($input['motivo'] ?? '');
            $tipo       = $input['tipo'] ?? CitaVO::TIPO_PRESENCIAL;
            $idPaciente = trim($input['id_paciente'] ?? $_SESSION['user_id']);

            if ($idMedico <= 0 || !$motivo || !$fecha || !$hora) {
                throw new Exception('Médico, motivo, fecha y hora son obligatorios');
            }

            // Si solicita para un dependiente, verificar que es su tutor
            if ($idPaciente !== $_SESSION['user_id']) {
                $depDAO = new DependienteDAO();
                if (!$depDAO->verificarAccesoTutor($idPaciente, $_SESSION['user_id'])) {
                    throw new Exception('No autorizado para solicitar cita para este paciente');
                }
            }

            if (preg_match('/^\d{2}:\d{2}$/', $hora)) $hora .= ':00';
            $fechaHora = $fecha . ' ' . $hora;

            $cita = new CitaVO([
                'id_paciente' => $idPaciente,
                'id_medico'   => $idMedico,
                'fecha_hora'  => $fechaHora,
                'motivo'      => $motivo,
                'tipo'        => $tipo,
            ]);

            $idNuevo  = $dao->insertar($cita);
            $citaData = $dao->obtenerPorId($idNuevo);

            sendJson([
                'success' => true,
                'mensaje' => 'Cita solicitada correctamente. El médico la confirmará en breve.',
                'id_cita' => $idNuevo,
                'cita'    => $citaData
            ]);
            break;

        // ── Médico confirma cita ────────────────────────────────────────────────
        case 'confirmar':
            if ($metodo !== 'POST') throw new Exception('Método no permitido');
            if ($_SESSION['user_tipo'] !== 'medico') throw new Exception('Solo médicos pueden confirmar citas');

            $idCita = (int) ($input['id_cita'] ?? 0);
            if ($idCita <= 0) throw new Exception('ID de cita requerido');

            $cita = $dao->obtenerPorId($idCita);
            if (!$cita) throw new Exception('Cita no encontrada');
            if ((int)$cita['id_medico'] !== (int)$_SESSION['user_id']) throw new Exception('No autorizado');
            if ($cita['estado'] !== CitaVO::ESTADO_PENDIENTE) throw new Exception('Solo se pueden confirmar citas pendientes');

            $dao->actualizarEstado($idCita, CitaVO::ESTADO_CONFIRMADA);
            sendJson(['success' => true, 'mensaje' => 'Cita confirmada']);
            break;

        // ── Cancelar cita (paciente o médico) ──────────────────────────────────
        case 'cancelar':
            if ($metodo !== 'POST') throw new Exception('Método no permitido');

            $idCita = (int) ($input['id_cita'] ?? 0);
            $notas  = trim($input['notas'] ?? '');
            if ($idCita <= 0) throw new Exception('ID de cita requerido');

            $cita = $dao->obtenerPorId($idCita);
            if (!$cita) throw new Exception('Cita no encontrada');

            $esPaciente = $_SESSION['user_tipo'] === 'paciente' && $cita['id_paciente'] === $_SESSION['user_id'];
            $esMedico   = $_SESSION['user_tipo'] === 'medico'   && (int)$cita['id_medico'] === (int)$_SESSION['user_id'];
            if (!$esPaciente && !$esMedico) throw new Exception('No autorizado');

            if (!in_array($cita['estado'], [CitaVO::ESTADO_PENDIENTE, CitaVO::ESTADO_CONFIRMADA])) {
                throw new Exception('No se puede cancelar una cita en estado: ' . $cita['estado']);
            }

            $canceladaPor = $esPaciente ? 'paciente' : 'medico';
            $dao->actualizarEstado($idCita, CitaVO::ESTADO_CANCELADA, $canceladaPor, $notas ?: null);
            sendJson(['success' => true, 'mensaje' => 'Cita cancelada']);
            break;

        // ── Médico marca cita como completada ──────────────────────────────────
        case 'completar':
            if ($metodo !== 'POST') throw new Exception('Método no permitido');
            if ($_SESSION['user_tipo'] !== 'medico') throw new Exception('Solo médicos pueden completar citas');

            $idCita = (int) ($input['id_cita'] ?? 0);
            if ($idCita <= 0) throw new Exception('ID de cita requerido');

            $cita = $dao->obtenerPorId($idCita);
            if (!$cita) throw new Exception('Cita no encontrada');
            if ((int)$cita['id_medico'] !== (int)$_SESSION['user_id']) throw new Exception('No autorizado');
            if ($cita['estado'] !== CitaVO::ESTADO_CONFIRMADA) throw new Exception('Solo se pueden completar citas confirmadas');

            $dao->actualizarEstado($idCita, CitaVO::ESTADO_COMPLETADA);
            sendJson(['success' => true, 'mensaje' => 'Cita marcada como completada']);
            break;

        // ── Listar citas del paciente ───────────────────────────────────────────
        case 'listar_paciente':
            if ($_SESSION['user_tipo'] !== 'paciente') throw new Exception('No autorizado');

            $soloActivas = ($_GET['activas'] ?? '0') === '1';
            $idPaciente  = trim($_GET['id_paciente'] ?? $_SESSION['user_id']);

            if ($idPaciente !== $_SESSION['user_id']) {
                $depDAO = new DependienteDAO();
                if (!$depDAO->verificarAccesoTutor($idPaciente, $_SESSION['user_id'])) {
                    throw new Exception('No autorizado');
                }
            }

            $data = $dao->obtenerPorPaciente($idPaciente, $soloActivas);
            sendJson(['success' => true, 'data' => $data]);
            break;

        // ── Listar citas del médico ─────────────────────────────────────────────
        case 'listar_medico':
            if ($_SESSION['user_tipo'] !== 'medico') throw new Exception('No autorizado');

            $soloPendientes = ($_GET['pendientes'] ?? '0') === '1';
            $fecha          = trim($_GET['fecha'] ?? '') ?: null;
            $data           = $dao->obtenerPorMedico($_SESSION['user_id'], $soloPendientes, $fecha);
            sendJson(['success' => true, 'data' => $data]);
            break;

        // ── Listar médicos disponibles (para el formulario de solicitud) ────────
        case 'obtener_medicos':
            if ($_SESSION['user_tipo'] !== 'paciente') throw new Exception('No autorizado');
            $medicoDAO = new MedicoDAO();
            $medicoVOs = $medicoDAO->obtenerTodos();
            $medicos   = array_map(fn($m) => $m->toArray(), $medicoVOs);
            sendJson(['success' => true, 'data' => $medicos]);
            break;

        default:
            throw new Exception('Acción no válida');
    }

} catch (Throwable $e) {
    sendJson(['success' => false, 'mensaje' => $e->getMessage()], 400);
}
?>
