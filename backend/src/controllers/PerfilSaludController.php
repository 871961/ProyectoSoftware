<?php
/**
 * Archivo: PerfilSaludController.php
 * Descripcion: Endpoints para gestionar el perfil de salud (RF4)
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

require_once '../config/database.php';

function requireSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
        sendJson([
            'success' => false,
            'mensaje' => 'Sesion no valida'
        ], 401);
    }
}

function parseNullableNumber($value) {
    if ($value === null || $value === '') {
        return null;
    }
    if (!is_numeric($value)) {
        throw new Exception('Los campos numericos deben ser validos');
    }
    return round((float) $value, 2);
}

function clasificarImc($imc) {
    if ($imc === null) return 'datos insuficientes';
    if ($imc < 18.5) return 'bajo peso';
    if ($imc < 25) return 'normal';
    return 'sobrepeso';
}

function calcularImc($pesoKg, $alturaCm) {
    if ($pesoKg === null || $alturaCm === null || $alturaCm <= 0) return null;
    $alturaM = $alturaCm / 100;
    return round($pesoKg / ($alturaM * $alturaM), 2);
}

function sanitizeText($value, $maxLen = 5000) {
    if ($value === null) return null;
    $trimmed = trim((string) $value);
    if ($trimmed === '') return null;
    if (strlen($trimmed) > $maxLen) {
        throw new Exception('Uno de los campos de texto supera la longitud permitida');
    }
    return $trimmed;
}

function ensurePerfilSchema(PDO $db) {
    $db->exec("
        CREATE TABLE IF NOT EXISTS perfiles_salud (
            id_perfil SERIAL PRIMARY KEY,
            id_paciente VARCHAR(20) UNIQUE NOT NULL REFERENCES pacientes(dni),
            altura_cm NUMERIC(5,2),
            peso_kg NUMERIC(5,2),
            alergias TEXT,
            enfermedades TEXT,
            consumo_tabaco VARCHAR(100),
            consumo_alcohol VARCHAR(100),
            actividad_fisica VARCHAR(150),
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS altura_cm NUMERIC(5,2)");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS peso_kg NUMERIC(5,2)");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS alergias TEXT");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS enfermedades TEXT");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS consumo_tabaco VARCHAR(100)");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS consumo_alcohol VARCHAR(100)");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS actividad_fisica VARCHAR(150)");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
    $db->exec("ALTER TABLE perfiles_salud ADD COLUMN IF NOT EXISTS fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

function obtenerPerfil(PDO $db, $idPaciente) {
    $sql = "SELECT
                ps.id_paciente,
                COALESCE(ps.altura_cm, ps.altura * 100) AS altura_cm,
                COALESCE(ps.peso_kg, ps.peso) AS peso_kg,
                ps.alergias,
                ps.enfermedades,
                ps.consumo_tabaco,
                ps.consumo_alcohol,
                ps.actividad_fisica,
                ps.fecha_actualizacion
            FROM perfiles_salud ps
            WHERE ps.id_paciente = :id_paciente
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id_paciente' => $idPaciente]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [
            'id_paciente' => $idPaciente,
            'altura_cm' => null,
            'peso_kg' => null,
            'alergias' => null,
            'enfermedades' => null,
            'consumo_tabaco' => null,
            'consumo_alcohol' => null,
            'actividad_fisica' => null,
            'imc' => null,
            'clasificacion_imc' => 'datos insuficientes'
        ];
    }

    $altura = $row['altura_cm'] !== null ? (float) $row['altura_cm'] : null;
    $peso = $row['peso_kg'] !== null ? (float) $row['peso_kg'] : null;
    $imc = calcularImc($peso, $altura);

    return [
        'id_paciente' => $row['id_paciente'],
        'altura_cm' => $altura,
        'peso_kg' => $peso,
        'alergias' => $row['alergias'],
        'enfermedades' => $row['enfermedades'],
        'consumo_tabaco' => $row['consumo_tabaco'],
        'consumo_alcohol' => $row['consumo_alcohol'],
        'actividad_fisica' => $row['actividad_fisica'],
        'fecha_actualizacion' => $row['fecha_actualizacion'] ?? null,
        'imc' => $imc,
        'clasificacion_imc' => clasificarImc($imc)
    ];
}

function upsertPerfilCompleto(PDO $db, $idPaciente, array $payload) {
    $alturaCm = parseNullableNumber($payload['altura_cm'] ?? null);
    $pesoKg = parseNullableNumber($payload['peso_kg'] ?? null);
    $alergias = sanitizeText($payload['alergias'] ?? null);
    $enfermedades = sanitizeText($payload['enfermedades'] ?? null);
    $consumoTabaco = sanitizeText($payload['consumo_tabaco'] ?? null, 100);
    $consumoAlcohol = sanitizeText($payload['consumo_alcohol'] ?? null, 100);
    $actividadFisica = sanitizeText($payload['actividad_fisica'] ?? null, 150);

    if ($alturaCm !== null && ($alturaCm < 30 || $alturaCm > 260)) {
        throw new Exception('La altura debe estar entre 30 y 260 cm');
    }
    if ($pesoKg !== null && ($pesoKg < 1 || $pesoKg > 400)) {
        throw new Exception('El peso debe estar entre 1 y 400 kg');
    }

    $sql = "INSERT INTO perfiles_salud
                (id_paciente, altura_cm, peso_kg, alergias, enfermedades, consumo_tabaco, consumo_alcohol, actividad_fisica, fecha_actualizacion)
            VALUES
                (:id_paciente, :altura_cm, :peso_kg, :alergias, :enfermedades, :consumo_tabaco, :consumo_alcohol, :actividad_fisica, CURRENT_TIMESTAMP)
            ON CONFLICT (id_paciente) DO UPDATE SET
                altura_cm = EXCLUDED.altura_cm,
                peso_kg = EXCLUDED.peso_kg,
                alergias = EXCLUDED.alergias,
                enfermedades = EXCLUDED.enfermedades,
                consumo_tabaco = EXCLUDED.consumo_tabaco,
                consumo_alcohol = EXCLUDED.consumo_alcohol,
                actividad_fisica = EXCLUDED.actividad_fisica,
                fecha_actualizacion = CURRENT_TIMESTAMP";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id_paciente' => $idPaciente,
        ':altura_cm' => $alturaCm,
        ':peso_kg' => $pesoKg,
        ':alergias' => $alergias,
        ':enfermedades' => $enfermedades,
        ':consumo_tabaco' => $consumoTabaco,
        ':consumo_alcohol' => $consumoAlcohol,
        ':actividad_fisica' => $actividadFisica
    ]);
}

function upsertPerfilPaciente(PDO $db, $idPaciente, array $payload) {
    $alturaCm = parseNullableNumber($payload['altura_cm'] ?? null);
    $pesoKg = parseNullableNumber($payload['peso_kg'] ?? null);
    $consumoTabaco = sanitizeText($payload['consumo_tabaco'] ?? null, 100);
    $consumoAlcohol = sanitizeText($payload['consumo_alcohol'] ?? null, 100);
    $actividadFisica = sanitizeText($payload['actividad_fisica'] ?? null, 150);

    if ($alturaCm !== null && ($alturaCm < 30 || $alturaCm > 260)) {
        throw new Exception('La altura debe estar entre 30 y 260 cm');
    }
    if ($pesoKg !== null && ($pesoKg < 1 || $pesoKg > 400)) {
        throw new Exception('El peso debe estar entre 1 y 400 kg');
    }

    $sql = "INSERT INTO perfiles_salud
                (id_paciente, altura_cm, peso_kg, consumo_tabaco, consumo_alcohol, actividad_fisica, fecha_actualizacion)
            VALUES
                (:id_paciente, :altura_cm, :peso_kg, :consumo_tabaco, :consumo_alcohol, :actividad_fisica, CURRENT_TIMESTAMP)
            ON CONFLICT (id_paciente) DO UPDATE SET
                altura_cm = EXCLUDED.altura_cm,
                peso_kg = EXCLUDED.peso_kg,
                consumo_tabaco = EXCLUDED.consumo_tabaco,
                consumo_alcohol = EXCLUDED.consumo_alcohol,
                actividad_fisica = EXCLUDED.actividad_fisica,
                fecha_actualizacion = CURRENT_TIMESTAMP";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':id_paciente' => $idPaciente,
        ':altura_cm' => $alturaCm,
        ':peso_kg' => $pesoKg,
        ':consumo_tabaco' => $consumoTabaco,
        ':consumo_alcohol' => $consumoAlcohol,
        ':actividad_fisica' => $actividadFisica
    ]);
}

try {
    requireSession();
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['accion'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $db = Database::getInstance()->getConnection();
    ensurePerfilSchema($db);

    switch ($accion) {
        case 'obtener_mi_perfil':
            if ($_SESSION['user_tipo'] !== 'paciente') {
                throw new Exception('No autorizado');
            }
            $perfil = obtenerPerfil($db, $_SESSION['user_id']);
            sendJson(['success' => true, 'data' => $perfil]);
            break;

        case 'obtener_por_paciente':
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $idPaciente = trim($_GET['id_paciente'] ?? '');
            if ($idPaciente === '') {
                throw new Exception('ID de paciente obligatorio');
            }
            $perfil = obtenerPerfil($db, $idPaciente);
            sendJson(['success' => true, 'data' => $perfil]);
            break;

        case 'guardar_por_medico':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'medico') {
                throw new Exception('No autorizado');
            }
            $idPaciente = trim((string) ($input['id_paciente'] ?? ''));
            if ($idPaciente === '') {
                throw new Exception('ID de paciente obligatorio');
            }
            upsertPerfilCompleto($db, $idPaciente, $input);
            $perfil = obtenerPerfil($db, $idPaciente);
            sendJson(['success' => true, 'mensaje' => 'Perfil de salud guardado', 'data' => $perfil]);
            break;

        case 'actualizar_mi_perfil':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            if ($_SESSION['user_tipo'] !== 'paciente') {
                throw new Exception('No autorizado');
            }
            upsertPerfilPaciente($db, $_SESSION['user_id'], $input);
            $perfil = obtenerPerfil($db, $_SESSION['user_id']);
            sendJson(['success' => true, 'mensaje' => 'Perfil de salud actualizado', 'data' => $perfil]);
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
