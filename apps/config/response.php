<?php

function emr_json_success(array $data = [], string $message = 'OK'): void
{
    echo json_encode([
        'success' => true,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

function emr_json_error(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    exit;
}
