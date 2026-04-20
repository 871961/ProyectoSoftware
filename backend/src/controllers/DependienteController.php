<?php
/**
 * Archivo: DependienteController.php
 * Descripción: Controlador para la gestión de pacientes dependientes (menores)
 * Fecha: Marzo 2026
 * Autoras: Yousra y Claudia
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../dao/DependienteDAO.php';
require_once '../vo/DependienteVO.php';

/**
 * Valida que el usuario esté autenticado y sea paciente
 */
function validarSesionPaciente() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'paciente') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'No autorizado. Debe iniciar sesión como paciente.'
        ]);
        exit();
    }
    return $_SESSION['user_id'];
}

/**
 * Valida que el usuario sea médico (pediatra) autenticado
 */
function validarSesionMedico() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'medico') {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'mensaje' => 'No autorizado. Debe iniciar sesión como médico.'
        ]);
        exit();
    }
    return $_SESSION['user_id'];
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['accion'] ?? '';

    $dependienteDAO = new DependienteDAO();

    switch ($accion) {
        // =====================================
        // ACCIONES PARA PACIENTES (TUTORES)
        // =====================================

        case 'listar':
            // Listar dependientes del tutor actual
            $dni_tutor = validarSesionPaciente();
            $dependientes = $dependienteDAO->obtenerPorTutor($dni_tutor);

            $resultado = array_map(function($dep) {
                return $dep->toArray();
            }, $dependientes);

            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);
            break;

        case 'obtener':
            // Obtener un dependiente específico
            $dni_tutor = validarSesionPaciente();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $dependiente = $dependienteDAO->obtenerPorId($id_dependiente);

            if (!$dependiente) {
                http_response_code(404);
                throw new Exception('Dependiente no encontrado');
            }

            echo json_encode([
                'success' => true,
                'data' => $dependiente->toArray()
            ]);
            break;

        case 'crear':
            // Crear nuevo dependiente
            if ($metodo !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $dni_tutor = validarSesionPaciente();
            $input = json_decode(file_get_contents('php://input'), true);

            // Validaciones
            $nombre = trim($input['nombre'] ?? '');
            $apellidos = trim($input['apellidos'] ?? '');
            $fecha_nacimiento = $input['fecha_nacimiento'] ?? '';
            $grupo_sanguineo = $input['grupo_sanguineo'] ?? null;
            $alergias = trim($input['alergias'] ?? '');
            $observaciones = trim($input['observaciones'] ?? '');
            $num_seguridad_social = trim($input['num_seguridad_social'] ?? '');

            if (empty($nombre)) {
                throw new Exception('El nombre es requerido');
            }

            if (empty($apellidos)) {
                throw new Exception('Los apellidos son requeridos');
            }

            if (empty($fecha_nacimiento)) {
                throw new Exception('La fecha de nacimiento es requerida');
            }

            // Validar formato de fecha
            $fecha = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if (!$fecha || $fecha->format('Y-m-d') !== $fecha_nacimiento) {
                throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
            }

            // Validar que sea menor de 18 años
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha)->y;
            if ($edad >= 18) {
                throw new Exception('El dependiente debe ser menor de 18 años');
            }

            // Obtener pediatra disponible
            $pediatra = $dependienteDAO->obtenerPediatraDisponible();
            if (!$pediatra) {
                throw new Exception('No hay pediatras disponibles. Contacte al administrador.');
            }

            // Crear el dependiente
            $dependiente = new DependienteVO([
                'nombre' => $nombre,
                'apellidos' => $apellidos,
                'fecha_nacimiento' => $fecha_nacimiento,
                'num_seguridad_social' => $num_seguridad_social ?: null,
                'dni_tutor' => $dni_tutor,
                'id_pediatra' => $pediatra['id_medico'],
                'grupo_sanguineo' => $grupo_sanguineo,
                'alergias' => $alergias ?: null,
                'observaciones' => $observaciones ?: null
            ]);

            $id_dependiente = $dependienteDAO->insertar($dependiente);

            if ($id_dependiente) {
                // Obtener el dependiente creado con todos los datos
                $dependienteCreado = $dependienteDAO->obtenerPorId($id_dependiente);

                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Dependiente registrado correctamente',
                    'data' => $dependienteCreado->toArray()
                ]);
            } else {
                throw new Exception('Error al crear el dependiente');
            }
            break;

        case 'actualizar':
            // Actualizar dependiente
            if ($metodo !== 'POST' && $metodo !== 'PUT') {
                throw new Exception('Método no permitido');
            }

            $dni_tutor = validarSesionPaciente();
            $input = json_decode(file_get_contents('php://input'), true);

            $id_dependiente = $input['id_dependiente'] ?? null;
            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $dependienteActual = $dependienteDAO->obtenerPorId($id_dependiente);
            if (!$dependienteActual) {
                http_response_code(404);
                throw new Exception('Dependiente no encontrado');
            }

            // Actualizar campos
            $dependienteActual->setNombre(trim($input['nombre'] ?? $dependienteActual->getNombre()));
            $dependienteActual->setApellidos(trim($input['apellidos'] ?? $dependienteActual->getApellidos()));
            $dependienteActual->setFechaNacimiento($input['fecha_nacimiento'] ?? $dependienteActual->getFechaNacimiento());
            $dependienteActual->setGrupoSanguineo($input['grupo_sanguineo'] ?? $dependienteActual->getGrupoSanguineo());
            $dependienteActual->setAlergias(trim($input['alergias'] ?? $dependienteActual->getAlergias()));
            $dependienteActual->setObservaciones(trim($input['observaciones'] ?? $dependienteActual->getObservaciones()));
            $dependienteActual->setNumSeguridadSocial(trim($input['num_seguridad_social'] ?? $dependienteActual->getNumSeguridadSocial()));

            $resultado = $dependienteDAO->actualizar($dependienteActual);

            if ($resultado) {
                $dependienteActualizado = $dependienteDAO->obtenerPorId($id_dependiente);
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Dependiente actualizado correctamente',
                    'data' => $dependienteActualizado->toArray()
                ]);
            } else {
                throw new Exception('Error al actualizar el dependiente');
            }
            break;

        case 'eliminar':
            // Dar de baja dependiente (borrado lógico)
            if ($metodo !== 'POST' && $metodo !== 'DELETE') {
                throw new Exception('Método no permitido');
            }

            $dni_tutor = validarSesionPaciente();
            $input = json_decode(file_get_contents('php://input'), true);

            $id_dependiente = $input['id_dependiente'] ?? $_GET['id'] ?? null;
            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $resultado = $dependienteDAO->darDeBaja($id_dependiente, $dni_tutor);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Dependiente dado de baja correctamente'
                ]);
            } else {
                throw new Exception('Error al dar de baja al dependiente');
            }
            break;

        case 'obtener_perfil_salud':
            // Obtener perfil de salud de un dependiente
            $dni_tutor = validarSesionPaciente();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $perfil = $dependienteDAO->obtenerPerfilSalud($id_dependiente);

            echo json_encode([
                'success' => true,
                'data' => $perfil ?: []
            ]);
            break;

        case 'obtener_antecedentes':
            // Obtener antecedentes familiares de un dependiente
            $dni_tutor = validarSesionPaciente();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $antecedentes = $dependienteDAO->obtenerAntecedentes($id_dependiente);

            echo json_encode([
                'success' => true,
                'data' => $antecedentes ?: []
            ]);
            break;

        case 'obtener_consultas':
            // Obtener consultas de un dependiente
            $dni_tutor = validarSesionPaciente();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $fecha_desde = $_GET['fecha_desde'] ?? null;
            $fecha_hasta = $_GET['fecha_hasta'] ?? null;

            $consultas = $dependienteDAO->obtenerConsultas($id_dependiente, $fecha_desde, $fecha_hasta);

            echo json_encode([
                'success' => true,
                'data' => $consultas ?: []
            ]);
            break;

        case 'obtener_vacunas':
            // Obtener cartilla de vacunas de un dependiente (acceso tutor)
            $dni_tutor = validarSesionPaciente();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar acceso
            if (!$dependienteDAO->verificarAccesoTutor($id_dependiente, $dni_tutor)) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $vacunas = $dependienteDAO->obtenerVacunas($id_dependiente);

            echo json_encode([
                'success' => true,
                'data' => $vacunas ?: []
            ]);
            break;

        // =====================================
        // ACCIONES PARA MÉDICOS (PEDIATRAS)
        // =====================================

        case 'listar_mis_dependientes':
            // Listar dependientes asignados al pediatra
            $id_medico = validarSesionMedico();
            $dependientes = $dependienteDAO->obtenerPorPediatra($id_medico);

            $resultado = array_map(function($dep) {
                return $dep->toArray();
            }, $dependientes);

            echo json_encode([
                'success' => true,
                'data' => $resultado
            ]);
            break;

        case 'obtener_dependiente_medico':
            // Obtener un dependiente (acceso médico)
            $id_medico = validarSesionMedico();
            $id_dependiente = $_GET['id'] ?? null;

            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            $dependiente = $dependienteDAO->obtenerPorId($id_dependiente);

            if (!$dependiente) {
                http_response_code(404);
                throw new Exception('Dependiente no encontrado');
            }

            // Verificar que el pediatra tenga asignado este dependiente
            if ($dependiente->getIdPediatra() != $id_medico) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            echo json_encode([
                'success' => true,
                'data' => $dependiente->toArray()
            ]);
            break;

        case 'actualizar_perfil_salud_medico':
            // Actualizar perfil de salud (solo médico/pediatra)
            if ($metodo !== 'POST') {
                throw new Exception('Método no permitido');
            }

            $id_medico = validarSesionMedico();
            $input = json_decode(file_get_contents('php://input'), true);

            $id_dependiente = $input['id_dependiente'] ?? null;
            if (!$id_dependiente) {
                throw new Exception('ID de dependiente requerido');
            }

            // Verificar que el pediatra tenga asignado este dependiente
            $dependiente = $dependienteDAO->obtenerPorId($id_dependiente);
            if (!$dependiente || $dependiente->getIdPediatra() != $id_medico) {
                http_response_code(403);
                throw new Exception('No tiene acceso a este dependiente');
            }

            $datos = [
                'peso_kg' => $input['peso_kg'] ?? null,
                'altura_cm' => $input['altura_cm'] ?? null,
                'alergias' => $input['alergias'] ?? null,
                'enfermedades' => $input['enfermedades'] ?? null,
                'grupo_sanguineo' => $input['grupo_sanguineo'] ?? null
            ];

            $resultado = $dependienteDAO->actualizarPerfilSalud($id_dependiente, $datos);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'mensaje' => 'Perfil de salud actualizado correctamente'
                ]);
            } else {
                throw new Exception('Error al actualizar el perfil de salud');
            }
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Acción no reconocida: ' . $accion
            ]);
            break;
    }

} catch (Exception $e) {
    if (http_response_code() === 200) {
        http_response_code(400);
    }
    echo json_encode([
        'success' => false,
        'mensaje' => $e->getMessage()
    ]);
}
