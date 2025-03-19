<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Carrega configurações
$config_file = 'data/config_emprestimos.json';
$config = [];

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
} else {
    $config = [
        'juros_diario_padrao' => 1.0,
        'juros_mensal_padrao' => 5.0,
        'multa_atraso_percentual' => 2.0,
        'multa_atraso_valor_fixo' => 0.0
    ];
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'config' => $config]);
?> 