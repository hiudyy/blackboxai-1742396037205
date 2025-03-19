<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Carrega o arquivo de configuração
$configFile = 'config_pix.json';
$configData = [];

if (file_exists($configFile)) {
    $configJson = file_get_contents($configFile);
    $configData = json_decode($configJson, true);
}

// Verifica se existe configuração para o usuário
if (isset($configData[$_SESSION['user_id']])) {
    echo json_encode([
        'success' => true, 
        'data' => $configData[$_SESSION['user_id']]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Nenhuma configuração encontrada'
    ]);
} 