<?php
/**
 * Archivo: AntecedentesController.php
 * Descripción: Controlador para gestión de antecedentes familiares
 * Fecha: Marzo 2026
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once '../dao/AntecedentesFamiliaresDAO.php';
require_once '../dao/EnfermedadesCatalogoDAO.php';

class AntecedentesController {
    private $antecedentesDAO;
    private $enfermedadesDAO;

    public function __construct() {
        $this->antecedentesDAO = new AntecedentesFamiliaresDAO();
        $this->enfermedadesDAO = new EnfermedadesCatalogoDAO();
    }

    /**
     * Verifica que el usuario esté autenticado y sea médico
     */
    private function verificarAutenticacion() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'medico') {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }
    }

    /**
     * Verifica que el paciente esté asignado al médico actual
     */
    private function verificarAsignacionPaciente($dni_paciente) {
        require_once '../config/database.php';
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM pacientes 
                WHERE dni = :dni 
                  AND id_medico_general = :id_medico 
                  AND activo = TRUE";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':dni' => $dni_paciente,
            ':id_medico' => $_SESSION['user_id']
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Verifica que el antecedente pertenezca a un paciente asignado al médico actual
     */
    private function verificarPropiedadAntecedente($id_antecedente) {
        require_once '../config/database.php';
        $db = Database::getInstance()->getConnection();
        
        $sql = "SELECT COUNT(*) FROM antecedentes_familiares af
                JOIN pacientes p ON af.id_paciente = p.dni
                WHERE af.id_antecedente = :id_antecedente
                  AND p.id_medico_general = :id_medico 
                  AND p.activo = TRUE";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':id_antecedente' => $id_antecedente,
            ':id_medico' => $_SESSION['user_id']
        ]);
        
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene todos los antecedentes de un paciente (para médicos)
     */
    public function obtenerPorPaciente() {
        $this->verificarAutenticacion();

        $dni_paciente = $_GET['dni_paciente'] ?? null;

        if (!$dni_paciente) {
            http_response_code(400);
            echo json_encode(['error' => 'DNI del paciente requerido']);
            return;
        }

        try {
            // Verificar que el paciente esté asignado a este médico
            if (!$this->verificarAsignacionPaciente($dni_paciente)) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes acceso a este paciente']);
                return;
            }

            $antecedentes = $this->antecedentesDAO->obtenerPorPaciente($dni_paciente);
            echo json_encode(['success' => true, 'data' => $antecedentes]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener antecedentes: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene los antecedentes del paciente actual (para pacientes)
     */
    public function obtenerMisAntecedentes() {
        // Verificar que el usuario esté autenticado y sea paciente
        if (!isset($_SESSION['user_id']) || $_SESSION['user_tipo'] !== 'paciente') {
            http_response_code(401);
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        try {
            $dni_paciente = $_SESSION['user_id']; // El user_id de los pacientes es su DNI
            $antecedentes = $this->antecedentesDAO->obtenerPorPaciente($dni_paciente);
            echo json_encode(['success' => true, 'data' => $antecedentes]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener antecedentes: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene el catálogo de enfermedades
     */
    public function obtenerEnfermedades() {
        $this->verificarAutenticacion();

        try {
            $enfermedades = $this->enfermedadesDAO->listarTodas();
            echo json_encode(['success' => true, 'data' => $enfermedades]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener enfermedades: ' . $e->getMessage()]);
        }
    }

    /**
     * Registra un nuevo antecedente familiar
     */
    public function registrar() {
        $this->verificarAutenticacion();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos inválidos']);
                return;
            }

            // Validar campos requeridos
            $camposRequeridos = ['id_paciente', 'id_enfermedad', 'parentesco'];
            foreach ($camposRequeridos as $campo) {
                if (!isset($input[$campo]) || empty($input[$campo])) {
                    http_response_code(400);
                    echo json_encode(['error' => "Campo requerido: $campo"]);
                    return;
                }
            }

            // Verificar que el paciente esté asignado a este médico
            if (!$this->verificarAsignacionPaciente($input['id_paciente'])) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes acceso a este paciente']);
                return;
            }

            // Crear VO - soportar tanto edad_diagnostico como edad_diagnóstico para compatibilidad
            $edadDiagnostico = $input['edad_diagnostico'] ?? $input['edad_diagnóstico'] ?? null;
            
            $antecedente = new AntecedentesFamiliaresVO([
                'id_paciente' => $input['id_paciente'],
                'id_enfermedad' => $input['id_enfermedad'],
                'parentesco' => $input['parentesco'],
                'lado_familiar' => $input['lado_familiar'] ?? null,
                'edad_diagnostico' => $edadDiagnostico,
                'notas_adicionales' => $input['notas_adicionales'] ?? null
            ]);

            // Insertar
            $id = $this->antecedentesDAO->insertar($antecedente);

            echo json_encode([
                'success' => true,
                'message' => 'Antecedente registrado correctamente',
                'id_antecedente' => $id
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al registrar antecedente: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza un antecedente familiar
     */
    public function actualizar() {
        $this->verificarAutenticacion();

        try {
            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['id_antecedente'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de antecedente requerido']);
                return;
            }

            // Verificar que el antecedente pertenezca a un paciente del médico
            if (!$this->verificarPropiedadAntecedente($input['id_antecedente'])) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes acceso a este antecedente']);
                return;
            }

            $antecedente = new AntecedentesFamiliaresVO($input);
            $this->antecedentesDAO->actualizar($antecedente);

            echo json_encode([
                'success' => true,
                'message' => 'Antecedente actualizado correctamente'
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al actualizar antecedente: ' . $e->getMessage()]);
        }
    }

    /**
     * Elimina (desactiva) un antecedente familiar
     */
    public function eliminar() {
        $this->verificarAutenticacion();

        $id_antecedente = $_GET['id_antecedente'] ?? null;

        if (!$id_antecedente) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de antecedente requerido']);
            return;
        }

        try {
            // Verificar que el antecedente pertenezca a un paciente del médico
            if (!$this->verificarPropiedadAntecedente($id_antecedente)) {
                http_response_code(403);
                echo json_encode(['error' => 'No tienes acceso a este antecedente']);
                return;
            }

            $this->antecedentesDAO->eliminar($id_antecedente);
            echo json_encode([
                'success' => true,
                'message' => 'Antecedente eliminado correctamente'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar antecedente: ' . $e->getMessage()]);
        }
    }
}

// Manejo de rutas
$controller = new AntecedentesController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'obtenerPorPaciente':
        $controller->obtenerPorPaciente();
        break;

    case 'obtenerMisAntecedentes':
        $controller->obtenerMisAntecedentes();
        break;

    case 'obtenerEnfermedades':
        $controller->obtenerEnfermedades();
        break;

    case 'registrar':
        $controller->registrar();
        break;

    case 'actualizar':
        $controller->actualizar();
        break;

    case 'eliminar':
        $controller->eliminar();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
