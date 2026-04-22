<?php
/**
 * Archivo: chat_crypto.php
 * Descripcion: Utilidades de cifrado para chat medico-medico (AES-256-GCM)
 * Nota: Configurar CHAT_ENCRYPTION_KEY en el entorno del servidor.
 */

function runningInLocalEnvironment() {
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
    return strpos($host, 'localhost') !== false
        || strpos($host, '127.0.0.1') !== false
        || strpos($host, '.local') !== false;
}

function getLocalChatKeyPath() {
    return __DIR__ . '/chat_key.local.txt';
}

function loadOrCreateLocalChatKey() {
    $path = getLocalChatKeyPath();
    if (is_file($path)) {
        return trim((string) file_get_contents($path));
    }

    $newKey = base64_encode(random_bytes(32));
    @file_put_contents($path, $newKey, LOCK_EX);
    return $newKey;
}

function getChatEncryptionKey() {
    $rawKey = trim((string) getenv('CHAT_ENCRYPTION_KEY'));
    if ($rawKey === '') {
        if (runningInLocalEnvironment()) {
            $rawKey = loadOrCreateLocalChatKey();
        } else {
            throw new Exception('No se puede iniciar chat seguro: falta CHAT_ENCRYPTION_KEY');
        }
    }

    $decoded = base64_decode($rawKey, true);
    if ($decoded !== false && strlen($decoded) === 32) {
        return $decoded;
    }

    if (preg_match('/^[a-f0-9]{64}$/i', $rawKey)) {
        $binary = hex2bin($rawKey);
        if ($binary !== false && strlen($binary) === 32) {
            return $binary;
        }
    }

    throw new Exception('CHAT_ENCRYPTION_KEY invalida. Usa una clave de 32 bytes en base64 o 64 caracteres hex.');
}

function encryptChatMessage($plainText) {
    if (!extension_loaded('openssl')) {
        throw new Exception('OpenSSL no esta disponible en el servidor');
    }

    if (!is_string($plainText) || !mb_check_encoding($plainText, 'UTF-8')) {
        throw new Exception('El contenido del mensaje no tiene codificacion valida');
    }

    $key = getChatEncryptionKey();
    $iv = random_bytes(12);
    $tag = '';

    $cipherRaw = openssl_encrypt(
        $plainText,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        'medhistory-chat-v1',
        16
    );

    if ($cipherRaw === false) {
        throw new Exception('No se pudo cifrar el mensaje');
    }

    return [
        'mensaje_cifrado' => base64_encode($cipherRaw),
        'nonce' => base64_encode($iv),
        'tag' => base64_encode($tag),
        'algoritmo' => 'aes-256-gcm'
    ];
}

function decryptChatMessage($cipherTextB64, $nonceB64, $tagB64) {
    if (!extension_loaded('openssl')) {
        throw new Exception('OpenSSL no esta disponible en el servidor');
    }

    $key = getChatEncryptionKey();
    $cipherRaw = base64_decode($cipherTextB64, true);
    $iv = base64_decode($nonceB64, true);
    $tag = base64_decode($tagB64, true);

    if ($cipherRaw === false || $iv === false || $tag === false) {
        throw new Exception('Datos cifrados corruptos');
    }
    if (strlen($iv) !== 12 || strlen($tag) !== 16) {
        throw new Exception('Parametros criptograficos invalidos');
    }

    $plain = openssl_decrypt(
        $cipherRaw,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag,
        'medhistory-chat-v1'
    );

    if ($plain === false) {
        throw new Exception('No se pudo descifrar el mensaje');
    }

    return $plain;
}
