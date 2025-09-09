<?php
// Servidor PHP simple para debugging
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log todas las requests
error_log("REQUEST: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);
error_log("DATA: " . file_get_contents('php://input'));

// Respuesta simple para test
if ($_SERVER['REQUEST_URI'] === '/api/postulant' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        'message' => 'Servidor funcionando',
        'received_data' => $_POST,
        'received_files' => array_keys($_FILES),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'message' => 'Endpoint no encontrado',
        'method' => $_SERVER['REQUEST_METHOD'],
        'uri' => $_SERVER['REQUEST_URI']
    ]);
}
?>