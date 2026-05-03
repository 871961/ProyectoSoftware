<?php
header('Content-Type: application/json; charset=utf-8');

$raw_input = file_get_contents('php://input');
$decoded = json_decode($raw_input, true);

echo json_encode([
    'método' => $_SERVER['REQUEST_METHOD'],
    'content_type_header' => $_SERVER['CONTENT_TYPE'] ?? 'NO DEFINIDO',
    'raw_input_length' => strlen($raw_input),
    'raw_input_primeros_100_chars' => substr($raw_input, 0, 100),
    'raw_input_completo' => $raw_input,
    'decoded_json' => $decoded,
    'json_error' => json_last_error_msg()
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
