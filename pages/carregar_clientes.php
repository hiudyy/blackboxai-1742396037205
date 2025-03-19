<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Carrega clientes
$clientes_file = 'data/clientes.json';
$clientes = [];

if (file_exists($clientes_file)) {
    $clientes = json_decode(file_get_contents($clientes_file), true) ?? [];
    
    // Filtra apenas os clientes do usuário atual
    $clientes = array_filter($clientes, function($cliente) {
        return $cliente['created_by'] == $_SESSION['user_id'];
    });
}

// Ordena os clientes por nome
usort($clientes, function($a, $b) {
    return strcmp($a['nome'], $b['nome']);
});

header('Content-Type: application/json');
echo json_encode(['success' => true, 'clientes' => array_values($clientes)]);
?> 