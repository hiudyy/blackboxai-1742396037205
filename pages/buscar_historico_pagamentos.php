<?php
session_start();
require_once 'config.php';

// Ativa exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do empréstimo não fornecido']);
    exit();
}

$emprestimo_id = intval($_GET['id']);

// Debug: Mostra o ID do empréstimo e do usuário
error_log("Buscando empréstimo ID: " . $emprestimo_id . " para usuário ID: " . $_SESSION['user_id']);

// Garante que o diretório data existe
if (!file_exists('data')) {
    if (!mkdir('data', 0777, true)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao criar diretório de dados']);
        exit();
    }
}

// Carrega os empréstimos
$emprestimos_file = 'emprestimos.json';
$emprestimos = [];

if (file_exists($emprestimos_file)) {
    $conteudo = file_get_contents($emprestimos_file);
    $emprestimos = json_decode($conteudo, true) ?? [];
    
    // Debug: Mostra o conteúdo do arquivo
    error_log("Conteúdo do arquivo emprestimos.json: " . $conteudo);
    error_log("Empréstimos carregados: " . print_r($emprestimos, true));
} else {
    error_log("Arquivo emprestimos.json não encontrado");
}

$emprestimo_encontrado = false;

foreach ($emprestimos as $emprestimo) {
    // Verifica se o empréstimo tem todas as chaves necessárias
    if (!isset($emprestimo['id']) || !isset($emprestimo['created_by'])) {
        error_log("Empréstimo inválido encontrado: " . print_r($emprestimo, true));
        continue;
    }

    // Debug: Mostra cada empréstimo sendo verificado
    error_log("Verificando empréstimo: ID=" . $emprestimo['id'] . ", created_by=" . $emprestimo['created_by']);

    if ($emprestimo['id'] == $emprestimo_id && $emprestimo['created_by'] == $_SESSION['user_id']) {
        $emprestimo_encontrado = true;
        
        // Carrega os pagamentos do arquivo
        $pagamentos = [];
        $arquivo_pagamentos = 'data/pagamentos_' . $emprestimo_id . '.json';
        
        try {
            // Primeiro verifica se o empréstimo tem pagamentos registrados diretamente
            if (isset($emprestimo['pagamentos']) && is_array($emprestimo['pagamentos'])) {
                $pagamentos = $emprestimo['pagamentos'];
                error_log("Pagamentos carregados do objeto empréstimo: " . count($pagamentos));
            }
            // Depois verifica se existe o arquivo de pagamentos
            elseif (file_exists($arquivo_pagamentos)) {
                $conteudo = file_get_contents($arquivo_pagamentos);
                if ($conteudo === false) {
                    throw new Exception('Erro ao ler arquivo de pagamentos');
                }
                $pagamentos = json_decode($conteudo, true) ?: [];
                
                // Debug: Mostra os pagamentos carregados
                error_log("Pagamentos carregados do arquivo: " . print_r($pagamentos, true));
            } else {
                error_log("Nenhum pagamento encontrado para o empréstimo ID: {$emprestimo_id}");
            }
        } catch (Exception $e) {
            error_log("Erro ao carregar pagamentos: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao carregar pagamentos: ' . $e->getMessage()
            ]);
            exit();
        }
        
        // Formata os pagamentos para exibição
        $historico = [];
        foreach ($pagamentos as $pagamento) {
            if (isset($pagamento['data'])) {
                $historico[] = [
                    'data' => $pagamento['data'],
                    'valor' => isset($pagamento['valor']) ? $pagamento['valor'] : null,
                    'diaria' => isset($pagamento['diaria_numero']) ? $pagamento['diaria_numero'] : null,
                    'tipo' => isset($pagamento['tipo']) ? $pagamento['tipo'] : null
                ];
            }
        }
        
        // Ordena por data (mais recente primeiro)
        usort($historico, function($a, $b) {
            return strtotime($b['data']) - strtotime($a['data']);
        });
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'historico' => $historico,
            'emprestimo' => [
                'valor' => $emprestimo['valor'] ?? 0,
                'tipo_juros' => $emprestimo['tipo_juros'] ?? 'mensal',
                'qtd_diarias' => $emprestimo['qtd_diarias'] ?? 1,
                'status' => $emprestimo['status'] ?? 'pendente',
                'diarias' => $emprestimo['diarias'] ?? []
            ],
            'mensagem' => empty($historico) ? 'Nenhum pagamento registrado para este empréstimo.' : null
        ]);
        exit();
    }
}

error_log("Nenhum empréstimo encontrado com ID: " . $emprestimo_id . " para usuário: " . $_SESSION['user_id']);

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Empréstimo não encontrado']);
?> 