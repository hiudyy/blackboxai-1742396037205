<?php
session_start();
require_once 'config.php';

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Verifica se recebeu os dados necessários
if (!isset($_POST['juros_diario_padrao']) || 
    !isset($_POST['juros_mensal_padrao']) || 
    !isset($_POST['multa_atraso_percentual']) || 
    !isset($_POST['multa_atraso_valor_fixo'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit();
}

// Valida os valores
$juros_diario = floatval($_POST['juros_diario_padrao']);
$juros_mensal = floatval($_POST['juros_mensal_padrao']);
$multa_percentual = floatval($_POST['multa_atraso_percentual']);
$multa_fixo = floatval($_POST['multa_atraso_valor_fixo']);

if ($juros_diario < 0 || $juros_mensal < 0 || $multa_percentual < 0 || $multa_fixo < 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Os valores não podem ser negativos']);
    exit();
}

// Prepara as configurações
$config = [
    'juros_diario_padrao' => $juros_diario,
    'juros_mensal_padrao' => $juros_mensal,
    'multa_atraso_percentual' => $multa_percentual,
    'multa_atraso_valor_fixo' => $multa_fixo
];

// Salva as configurações
$config_file = 'config_emprestimos.json';
if (file_put_contents($config_file, json_encode($config))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo de configurações']);
}
?> 