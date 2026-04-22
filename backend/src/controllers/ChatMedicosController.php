<?php
/**
 * Archivo: ChatMedicosController.php
 * Descripcion: Endpoints de chat seguro entre medicos
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

function isHttpsRequest() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

function normalizeHost($host) {
    return strtolower((string) preg_replace('/:\\d+$/', '', trim((string) $host)));
}

function isTrustedOriginUrl($url) {
    if (!is_string($url) || trim($url) === '') {
        return false;
    }

    $originHost = parse_url($url, PHP_URL_HOST);
    $requestHost = normalizeHost($_SERVER['HTTP_HOST'] ?? '');
    return $originHost && normalizeHost($originHost) === $requestHost;
}

function applyCorsForTrustedOrigin() {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (isTrustedOriginUrl($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }
}

function requireSameOriginForPost() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    if ($origin !== '' && !isTrustedOriginUrl($origin)) {
        sendJson(['success' => false, 'mensaje' => 'Origen no permitido'], 403);
    }
    if ($origin === '' && $referer !== '' && !isTrustedOriginUrl($referer)) {
        sendJson(['success' => false, 'mensaje' => 'Origen no permitido'], 403);
    }
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => isHttpsRequest(),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
applyCorsForTrustedOrigin();
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJson(['success' => true], 200);
}

require_once '../dao/ChatMedicoDAO.php';
require_once '../config/chat_crypto.php';

function requireSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_tipo'])) {
        sendJson(['success' => false, 'mensaje' => 'Sesion no valida'], 401);
    }
}

function requireMedico() {
    requireSession();
    if (($_SESSION['user_tipo'] ?? '') !== 'medico') {
        sendJson(['success' => false, 'mensaje' => 'No autorizado'], 403);
    }
}

function aplicarRateLimitChat() {
    $ahora = time();
    if (!isset($_SESSION['chat_rate']) || !is_array($_SESSION['chat_rate'])) {
        $_SESSION['chat_rate'] = [];
    }

    $_SESSION['chat_rate'] = array_values(array_filter($_SESSION['chat_rate'], function($ts) use ($ahora) {
        return ($ahora - (int)$ts) <= 60;
    }));

    if (count($_SESSION['chat_rate']) >= 20) {
        throw new Exception('Demasiados mensajes en poco tiempo. Espera un minuto.');
    }

    $_SESSION['chat_rate'][] = $ahora;
}

try {
    $metodo = $_SERVER['REQUEST_METHOD'];
    $accion = $_GET['accion'] ?? '';
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    requireMedico();
    requireSameOriginForPost();
    $idMedico = (int) $_SESSION['user_id'];
    $dao = new ChatMedicoDAO();

    switch ($accion) {
        case 'listar_medicos':
            $medicos = $dao->listarMedicosDisponibles($idMedico);
            sendJson(['success' => true, 'data' => $medicos]);
            break;

        case 'listar_conversaciones':
            $resumen = $dao->listarResumenConversaciones($idMedico);
            sendJson(['success' => true, 'data' => $resumen]);
            break;

        case 'listar_conversacion':
            $idOtro = (int) ($_GET['id_medico'] ?? 0);
            $limite = (int) ($_GET['limite'] ?? 100);

            if ($idOtro <= 0 || $idOtro === $idMedico) {
                throw new Exception('Medico destino no valido');
            }
            if (!$dao->medicoActivoPorId($idOtro)) {
                throw new Exception('El medico seleccionado no esta disponible');
            }

            $mensajes = $dao->obtenerConversacion($idMedico, $idOtro, $limite);
            $mensajesPlano = [];
            foreach ($mensajes as $m) {
                $texto = '';
                try {
                    if (($m['algoritmo'] ?? '') !== 'aes-256-gcm') {
                        throw new Exception('Algoritmo no soportado');
                    }
                    $texto = decryptChatMessage($m['mensaje_cifrado'], $m['nonce'], $m['tag']);
                } catch (Exception $e) {
                    $texto = '[mensaje no disponible]';
                }

                $mensajesPlano[] = [
                    'id_mensaje' => (int)$m['id_mensaje'],
                    'id_emisor' => (int)$m['id_emisor'],
                    'id_receptor' => (int)$m['id_receptor'],
                    'mensaje' => $texto,
                    'enviado_en' => $m['enviado_en'],
                    'leido_en' => $m['leido_en']
                ];
            }

            $leidos = $dao->marcarLeidos($idMedico, $idOtro);
            $dao->registrarEventoChat($idMedico, 'CHAT_READ', null, [
                'id_contacto' => $idOtro,
                'mensajes' => count($mensajesPlano),
                'marcados_leidos' => (int) $leidos
            ]);

            sendJson([
                'success' => true,
                'data' => $mensajesPlano,
                'meta' => [
                    'id_contacto' => $idOtro,
                    'mensajes_marcados_leidos' => $leidos
                ]
            ]);
            break;

        case 'enviar':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }

            aplicarRateLimitChat();

            $idReceptor = (int) ($input['id_receptor'] ?? 0);
            $mensaje = trim((string) ($input['mensaje'] ?? ''));

            if ($idReceptor <= 0 || $idReceptor === $idMedico) {
                throw new Exception('Receptor no valido');
            }
            if (!$dao->medicoActivoPorId($idReceptor)) {
                throw new Exception('El medico receptor no esta disponible');
            }
            if ($mensaje === '') {
                throw new Exception('El mensaje es obligatorio');
            }
            if (mb_strlen($mensaje) > 2000) {
                throw new Exception('El mensaje excede el limite de 2000 caracteres');
            }

            $cifrado = encryptChatMessage($mensaje);
            $nuevo = $dao->crearMensaje(
                $idMedico,
                $idReceptor,
                $cifrado['mensaje_cifrado'],
                $cifrado['nonce'],
                $cifrado['tag'],
                $cifrado['algoritmo']
            );

            sendJson([
                'success' => true,
                'mensaje' => 'Mensaje enviado',
                'data' => [
                    'id_mensaje' => (int)$nuevo['id_mensaje'],
                    'id_emisor' => $idMedico,
                    'id_receptor' => $idReceptor,
                    'mensaje' => $mensaje,
                    'enviado_en' => $nuevo['enviado_en'],
                    'leido_en' => null
                ]
            ]);
            break;

        case 'marcar_leidos':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }
            $idOtro = (int) ($input['id_medico'] ?? 0);
            if ($idOtro <= 0 || $idOtro === $idMedico) {
                throw new Exception('Medico destino no valido');
            }
            $cantidad = $dao->marcarLeidos($idMedico, $idOtro);
            sendJson([
                'success' => true,
                'mensaje' => 'Mensajes actualizados',
                'data' => ['actualizados' => $cantidad]
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
