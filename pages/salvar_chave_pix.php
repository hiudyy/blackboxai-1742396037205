<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

// Verifica se os dados foram enviados
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit();
}

// Obtém os dados do formulário
$dados = json_decode(file_get_contents('php://input'), true);

if (!isset($dados['chavePix']) || !isset($dados['tipoPix']) || !isset($dados['nomePix'])) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit();
}

$chavePix = $dados['chavePix'];
$tipoPix = $dados['tipoPix'];
$nomePix = $dados['nomePix'];

// Cria o arquivo config_pix.json se não existir
$configFile = 'config_pix.json';
$configData = [];

if (file_exists($configFile)) {
    $configJson = file_get_contents($configFile);
    $configData = json_decode($configJson, true);
}

// Atualiza ou adiciona a configuração do usuário
$configData[$_SESSION['user_id']] = [
    'chavePix' => $chavePix,
    'tipoPix' => $tipoPix,
    'nomePix' => $nomePix,
    'atualizado_em' => date('Y-m-d H:i:s')
];

// Salva o arquivo
if (file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT))) {
    // Retorna os dados atualizados junto com a mensagem de sucesso
    // Importante: Certifique-se de que os dados da chave PIX estejam corretos
    echo json_encode([
        'success' => true, 
        'message' => 'Chave PIX configurada com sucesso',
        'data' => [
            'chavePix' => $chavePix,
            'tipoPix' => $tipoPix,
            'nomePix' => $nomePix
        ],
        'reload' => true,
        'redirect_url' => 'index.php'
    ]);
    
    // Limpa o buffer de saída para garantir que a resposta seja enviada imediatamente
    ob_flush();
    flush();
    
    // Aguarda um momento para permitir que o JavaScript processe a resposta
    // antes que o redirecionamento ocorra (se o JavaScript não o fizer)
  
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar a configuração']);
} 