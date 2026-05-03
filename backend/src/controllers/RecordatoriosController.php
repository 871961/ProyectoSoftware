<?php
/**
 * Archivo: RecordatoriosController.php
 * Descripcion: Endpoints para crear y gestionar recordatorios de pacientes
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
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJson(['success' => true], 200);
}

require_once '../dao/RecordatorioDAO.php';
require_once '../vo/RecordatorioVO.php';
require_once '../dao/PacienteDAO.php';
require_once '../dao/ConsultaDAO.php';

function requireSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
        sendJson(['success' => false, 'mensaje' => 'Sesion no valida'], 401);
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
                'email' => $_SESSION['user_email'] ?? ''
            ]
        ]);
    }

    requireSession();
    $dao = new RecordatorioDAO();

    switch ($accion) {
        case 'crear':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }

            $idConsulta = (int) ($input['id_consulta'] ?? 0);
            $fecha = trim($input['fecha_recordatorio'] ?? '');
            $hora = trim($input['hora_recordatorio'] ?? '');
            $tipo = $input['tipo'] ?? RecordatorioVO::TIPO_OTRO;
            $razon = trim($input['titulo'] ?? $input['razon'] ?? '');

            if ($idConsulta <= 0 || $razon === '' || $fecha === '') {
                throw new Exception('Consulta, título y fecha son obligatorios');
            }

            $consultaDAO = new ConsultaDAO();
            $consulta = $consultaDAO->obtenerPorId($idConsulta);
            if (!$consulta) {
                throw new Exception('Consulta no encontrada');
            }
            if ((int)$consulta->getIdMedico() !== (int)$_SESSION['user_id']) {
                throw new Exception('Solo el médico que creó la consulta puede añadir recordatorios');
            }

            // Normalizar hora
            if ($hora && preg_match('/^\\d{2}:\\d{2}$/', $hora)) {
                $hora .= ':00';
            }
            $fechaHora = $fecha . ' ' . ($hora ?: '00:00:00');

            $recordatorio = new RecordatorioVO([
                'id_consulta' => $idConsulta,
                'fecha_hora' => $fechaHora,
                'tipo_recordatorio' => mapTipo($tipo),
                'razon' => $razon,
                'estado' => RecordatorioVO::ESTADO_PENDIENTE
            ]);

            $idNuevo = $dao->insertar(
                $recordatorio,
                $consulta->getIdMedico(),
                $consulta->getIdPaciente()
            );

            $rec = $dao->obtenerPorId($idNuevo);
            sendJson([
                'success' => true,
                'mensaje' => 'Recordatorio creado',
                'id_recordatorio' => $idNuevo,
                'recordatorio' => $rec
            ]);
            break;

        case 'listar_paciente':
            if ($_SESSION['user_tipo'] !== 'paciente') {
                throw new Exception('No autorizado');
            }
            $soloPendientes = ($_GET['pendientes'] ?? '0') === '1';
            $data = $dao->obtenerPorPaciente($_SESSION['user_id'], $soloPendientes);

            sendJson([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'listar_medico':
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $fecha = trim($_GET['fecha'] ?? '');
            $fecha = $fecha !== '' ? $fecha : null;
            $data = $dao->obtenerPorMedicoYFecha($_SESSION['user_id'], $fecha);
            sendJson([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'listar_por_consulta':
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $idConsulta = (int) ($_GET['id_consulta'] ?? 0);
            if ($idConsulta <= 0) {
                throw new Exception('ID de consulta requerido');
            }
            $data = $dao->obtenerPorConsulta($idConsulta, $_SESSION['user_id']);
            sendJson([
                'success' => true,
                'data' => $data
            ]);
            break;

        case 'completar':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            $idRecordatorio = (int) ($input['id_recordatorio'] ?? 0);
            if ($idRecordatorio <= 0) {
                throw new Exception('ID de recordatorio requerido');
            }

            $rec = $dao->obtenerPorId($idRecordatorio);
            if (!$rec) {
                throw new Exception('Recordatorio no encontrado');
            }

            $esPaciente = $_SESSION['user_tipo'] === 'paciente' && $rec['id_paciente'] === $_SESSION['user_id'];
            $esMedico = $_SESSION['user_tipo'] === 'medico' && (int)$rec['id_medico'] === (int)$_SESSION['user_id'];
            if (!$esPaciente && !$esMedico) {
                throw new Exception('No autorizado a modificar este recordatorio');
            }

            $ok = $dao->actualizarEstado($idRecordatorio, RecordatorioVO::ESTADO_COMPLETADO);
            sendJson([
                'success' => (bool)$ok,
                'mensaje' => 'Recordatorio marcado como completado'
            ]);
            break;

        case 'actualizar':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $idRecordatorio = (int) ($input['id_recordatorio'] ?? 0);
            $fecha = trim($input['fecha_recordatorio'] ?? '');
            $hora = trim($input['hora_recordatorio'] ?? '');
            $tipo = $input['tipo'] ?? RecordatorioVO::TIPO_OTRO;
            $razon = trim($input['titulo'] ?? $input['razon'] ?? '');
            if ($idRecordatorio <= 0 || $fecha === '' || $razon === '') {
                throw new Exception('ID, fecha y título son obligatorios');
            }
            $rec = $dao->obtenerPorId($idRecordatorio);
            if (!$rec) throw new Exception('Recordatorio no encontrado');
            if ((int)$rec['id_medico'] !== (int)$_SESSION['user_id']) {
                throw new Exception('No autorizado');
            }
            if ($hora && preg_match('/^\\d{2}:\\d{2}$/', $hora)) $hora .= ':00';
            $fechaHora = $fecha . ' ' . ($hora ?: '00:00:00');
            $r = new RecordatorioVO([
                'id_recordatorio' => $idRecordatorio,
                'id_consulta' => $rec['id_consulta'],
                'fecha_hora' => $fechaHora,
                'tipo_recordatorio' => mapTipo($tipo),
                'razon' => $razon,
                'estado' => $rec['estado']
            ]);
            $dao->actualizar($r);
            sendJson(['success' => true, 'mensaje' => 'Recordatorio actualizado']);
            break;

        case 'eliminar':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $idRecordatorio = (int) ($input['id_recordatorio'] ?? 0);
            if ($idRecordatorio <= 0) throw new Exception('ID de recordatorio requerido');
            $rec = $dao->obtenerPorId($idRecordatorio);
            if (!$rec) throw new Exception('Recordatorio no encontrado');
            if ((int)$rec['id_medico'] !== (int)$_SESSION['user_id']) {
                throw new Exception('No autorizado');
            }
            $dao->eliminar($idRecordatorio, $_SESSION['user_id']);
            sendJson(['success' => true, 'mensaje' => 'Recordatorio eliminado']);
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

// Helpers
function mapTipo($tipoEntrada) {
    $map = [
        'medicamento' => RecordatorioVO::TIPO_MEDICACION,
        'medicacion' => RecordatorioVO::TIPO_MEDICACION,
        'medicación' => RecordatorioVO::TIPO_MEDICACION,
        'control' => RecordatorioVO::TIPO_CONTROL,
        'cita' => RecordatorioVO::TIPO_CITA,
        'examen' => RecordatorioVO::TIPO_CITA
    ];
    return $map[strtolower($tipoEntrada)] ?? RecordatorioVO::TIPO_OTRO;
}
?>
