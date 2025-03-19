<?php
session_start();
require_once 'config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Log para debug
error_log("Iniciando carregar_emprestimos.php");

// Carrega os empréstimos
$emprestimos_file = 'data/emprestimos.json';
$emprestimos = [];

if (file_exists($emprestimos_file)) {
    $json_data = file_get_contents($emprestimos_file);
    error_log("Conteúdo do arquivo emprestimos.json carregado: " . strlen($json_data) . " bytes");
    
    $emprestimos = json_decode($json_data, true) ?? [];
    error_log("Total de empréstimos carregados: " . count($emprestimos));
    
    // Garantir que os IDs são mantidos exatamente como estão no arquivo
    foreach ($emprestimos as &$emprestimo) {
        $emprestimo['id'] = (string)$emprestimo['id'];
    }
    
    // Filtra apenas os empréstimos do usuário atual
    $emprestimos = array_filter($emprestimos, function($emprestimo) {
        return $emprestimo['created_by'] == $_SESSION['user_id'];
    });
    
    // Log dos IDs disponíveis para debug
    $ids_disponiveis = array_map(function($emp) {
        return $emp['id'] . ' (tipo: ' . gettype($emp['id']) . ')';
    }, $emprestimos);
    error_log("IDs disponíveis após filtragem: " . implode(", ", $ids_disponiveis));
}

// Carrega clientes para adicionar informações aos empréstimos
$clientes_file = 'data/clientes.json';
$clientes = [];

if (file_exists($clientes_file)) {
    $clientes = json_decode(file_get_contents($clientes_file), true) ?? [];
    
    // Filtra apenas os clientes do usuário atual
    $clientes = array_filter($clientes, function($cliente) {
        return $cliente['created_by'] == $_SESSION['user_id'];
    });
    
    $clientes_map = array_column($clientes, null, 'id');
}

// Processa os empréstimos para adicionar informações adicionais
foreach ($emprestimos as &$emprestimo) {
    // Adiciona dados do cliente
    $cliente = $clientes_map[$emprestimo['cliente_id']] ?? null;
    $emprestimo['cliente_nome'] = $cliente ? $cliente['nome'] : 'Cliente não encontrado';
    
    // Calcula status e valores para empréstimos diários
    if ($emprestimo['tipo_juros'] === 'diario') {
        $hoje = new DateTime();
        $diarias_atrasadas = 0;
        
        // Se o status é finalizado ou em_dia e já existe um valor_atual, não recalcular
        if (($emprestimo['status'] === 'finalizado' || $emprestimo['status'] === 'em_dia') && isset($emprestimo['valor_atual'])) {
            // Mantém o valor_atual existente
        } else {
            $valor_atual = $emprestimo['valor'];
            
            foreach ($emprestimo['diarias'] as &$diaria) {
                $data_diaria = new DateTime($diaria['data']);
                
                if ($diaria['status'] === 'pendente' && $hoje > $data_diaria) {
                    $diarias_atrasadas++;
                    // Adiciona multa por atraso
                    $valor_atual += $emprestimo['multa_atraso_valor_fixo'];
                    $valor_atual += ($diaria['valor'] * ($emprestimo['multa_atraso_percentual'] / 100));
                }
            }
            
            $emprestimo['valor_atual'] = $valor_atual;
        }
        
        // Define o status_pagamento apenas se não for 'em_dia' ou 'finalizado'
        if (!in_array($emprestimo['status'], ['em_dia', 'finalizado'])) {
            $emprestimo['dias_atraso'] = $diarias_atrasadas;
            $emprestimo['status_pagamento'] = $diarias_atrasadas > 0 ? 'atrasado' : 'em_dia';
        }
    } else {
        // Lógica para empréstimos mensais
        $data_primeiro_pagamento = new DateTime($emprestimo['data_primeiro_pagamento']);
        $hoje = new DateTime();
        
        // Se o status é finalizado ou em_dia e já existe um valor_atual, não recalcular
        if (($emprestimo['status'] === 'finalizado' || $emprestimo['status'] === 'em_dia') && isset($emprestimo['valor_atual'])) {
            // Mantém o valor_atual existente
        } else {
            $valor_inicial = $emprestimo['valor'];
            $valor_atual = $valor_inicial;
            $dias_corridos = $hoje->diff($data_primeiro_pagamento)->days;
            
            $meses = floor($dias_corridos / 30);
            $valor_atual += ($valor_inicial * ($emprestimo['taxa_juros'] / 100) * $meses);
            
            $emprestimo['valor_atual'] = $valor_atual;
        }
        
        // Define o status_pagamento apenas se não for 'em_dia' ou 'finalizado'
        if (!in_array($emprestimo['status'], ['em_dia', 'finalizado'])) {
            $emprestimo['dias_atraso'] = $hoje > $data_primeiro_pagamento ? $hoje->diff($data_primeiro_pagamento)->days : 0;
            $emprestimo['status_pagamento'] = $hoje > $data_primeiro_pagamento ? 'atrasado' : 'em_dia';
        }
    }
}

// Remove a referência do foreach
unset($emprestimo);

// Estatísticas
$total_clientes = count(array_unique(array_map(function($emp) {
    return $emp['cliente_id'];
}, $emprestimos)));

$total_emprestimos = count($emprestimos);

$valor_a_receber = array_reduce($emprestimos, function($total, $emp) {
    return $total + ($emp['valor_atual'] ?? $emp['valor']);
}, 0);

// Retorna a resposta
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'emprestimos' => array_values($emprestimos),
    'estatisticas' => [
        'total_clientes' => $total_clientes,
        'total_emprestimos' => $total_emprestimos,
        'valor_a_receber' => $valor_a_receber
    ]
]);
exit;
?> 