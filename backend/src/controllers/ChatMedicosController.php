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

function getChatUploadsBasePath() {
    return dirname(__DIR__, 3) . '/uploads/chat';
}

function ensureChatUploadDirectory($idMedico) {
    $base = getChatUploadsBasePath();
    if (!is_dir($base)) {
        @mkdir($base, 0775, true);
    }

    $folder = $base . '/medico_' . (int)$idMedico;
    if (!is_dir($folder)) {
        @mkdir($folder, 0775, true);
    }

    return $folder;
}

function getAllowedAttachmentMimeMap() {
    return [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'application/pdf' => 'pdf',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
    ];
}

function sanitizeFilename($name) {
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', (string)$name);
    return trim($name, '._-');
}

function streamAttachmentAndExit($absolutePath, $downloadName) {
    if (!is_file($absolutePath) || !is_readable($absolutePath)) {
        sendJson(['success' => false, 'mensaje' => 'Archivo no disponible'], 404);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $absolutePath) ?: 'application/octet-stream';
    finfo_close($finfo);

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($absolutePath));
    header('Content-Disposition: inline; filename="' . addslashes($downloadName) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($absolutePath);
    exit();
}

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

    if (!$dao->medicoActivoPorId($idMedico)) {
        sendJson(['success' => false, 'mensaje' => 'Tu cuenta medica esta inactiva. No puedes usar el chat.'], 403);
    }

    switch ($accion) {
        case 'listar_medicos':
            $q = trim((string) ($_GET['q'] ?? ''));
            $medicos = $dao->listarMedicosDisponibles($idMedico, $q);
            sendJson(['success' => true, 'data' => $medicos]);
            break;

        case 'listar_conversaciones':
            $resumen = $dao->listarResumenConversaciones($idMedico);
            sendJson(['success' => true, 'data' => $resumen]);
            break;

        case 'contar_no_leidos':
            $totalNoLeidos = $dao->contarNoLeidos($idMedico);
            sendJson([
                'success' => true,
                'data' => [
                    'total_no_leidos' => $totalNoLeidos
                ]
            ]);
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
                    'tipo_contenido' => $m['tipo_contenido'] ?? 'texto',
                    'mensaje' => $texto,
                    'nombre_archivo' => $m['nombre_archivo'] ?? null,
                    'tamano_bytes' => isset($m['tamano_bytes']) ? (int)$m['tamano_bytes'] : null,
                    'archivo_url' => !empty($m['ruta_archivo'])
                        ? ('/backend/src/controllers/ChatMedicosController.php?accion=descargar_archivo&id_mensaje=' . (int)$m['id_mensaje'])
                        : null,
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

        case 'enviar_archivo':
            if ($metodo !== 'POST') {
                throw new Exception('Metodo no permitido');
            }

            aplicarRateLimitChat();

            if (!$dao->soportaAdjuntos()) {
                throw new Exception('Tu base de datos aun no tiene aplicada la migracion de adjuntos del chat. Ejecuta database/chat_medicos_seguro.sql antes de enviar archivos.');
            }

            $idReceptor = (int) ($_POST['id_receptor'] ?? 0);
            $mensaje = trim((string) ($_POST['mensaje'] ?? ''));

            if ($idReceptor <= 0 || $idReceptor === $idMedico) {
                throw new Exception('Receptor no valido');
            }
            if (!$dao->medicoActivoPorId($idReceptor)) {
                throw new Exception('El medico receptor no esta disponible');
            }
            if (!isset($_FILES['archivo']) || !is_array($_FILES['archivo'])) {
                throw new Exception('Debes adjuntar un archivo valido');
            }

            $archivo = $_FILES['archivo'];
            if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                throw new Exception('No se pudo cargar el archivo');
            }

            $maxBytes = 10 * 1024 * 1024;
            $size = (int) ($archivo['size'] ?? 0);
            if ($size <= 0 || $size > $maxBytes) {
                throw new Exception('El archivo excede el limite de 10 MB');
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $archivo['tmp_name']) ?: '';
            finfo_close($finfo);

            $allowed = getAllowedAttachmentMimeMap();
            if (!isset($allowed[$mime])) {
                throw new Exception('Tipo de archivo no permitido. Solo PNG, JPG, PDF, DOC y DOCX');
            }

            $ext = $allowed[$mime];
            $safeOriginal = sanitizeFilename((string) ($archivo['name'] ?? ('adjunto.' . $ext)));
            if ($safeOriginal === '') {
                $safeOriginal = 'adjunto.' . $ext;
            }

            $folder = ensureChatUploadDirectory($idMedico);
            $storedName = date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $absolutePath = $folder . '/' . $storedName;

            if (!move_uploaded_file($archivo['tmp_name'], $absolutePath)) {
                throw new Exception('No se pudo guardar el archivo adjunto');
            }

            $relativePath = 'medico_' . (int)$idMedico . '/' . $storedName;
            $payloadText = $mensaje !== '' ? $mensaje : ('[Adjunto] ' . $safeOriginal);
            if (mb_strlen($payloadText) > 2000) {
                $payloadText = mb_substr($payloadText, 0, 2000);
            }

            $cifrado = encryptChatMessage($payloadText);
            $nuevo = $dao->crearMensaje(
                $idMedico,
                $idReceptor,
                $cifrado['mensaje_cifrado'],
                $cifrado['nonce'],
                $cifrado['tag'],
                $cifrado['algoritmo'],
                'archivo',
                $safeOriginal,
                $relativePath,
                $size
            );

            sendJson([
                'success' => true,
                'mensaje' => 'Archivo enviado',
                'data' => [
                    'id_mensaje' => (int)$nuevo['id_mensaje'],
                    'id_emisor' => $idMedico,
                    'id_receptor' => $idReceptor,
                    'tipo_contenido' => 'archivo',
                    'mensaje' => $payloadText,
                    'nombre_archivo' => $safeOriginal,
                    'tamano_bytes' => $size,
                    'archivo_url' => '/backend/src/controllers/ChatMedicosController.php?accion=descargar_archivo&id_mensaje=' . (int)$nuevo['id_mensaje'],
                    'enviado_en' => $nuevo['enviado_en'],
                    'leido_en' => null
                ]
            ]);
            break;

        case 'descargar_archivo':
            $idMensaje = (int) ($_GET['id_mensaje'] ?? 0);
            if ($idMensaje <= 0) {
                throw new Exception('Mensaje no valido');
            }

            $mensaje = $dao->obtenerMensajePorIdParaMedico($idMensaje, $idMedico);
            if (!$mensaje || empty($mensaje['ruta_archivo'])) {
                throw new Exception('Archivo no disponible');
            }

            $fullPath = getChatUploadsBasePath() . '/' . ltrim((string)$mensaje['ruta_archivo'], '/\\');
            streamAttachmentAndExit($fullPath, (string)($mensaje['nombre_archivo'] ?? 'adjunto'));
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
