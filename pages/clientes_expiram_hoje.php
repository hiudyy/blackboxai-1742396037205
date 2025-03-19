<?php
session_start();
require_once 'config.php';

// Define o fuso horário para Brasília
date_default_timezone_set('America/Sao_Paulo');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Carrega os empréstimos
$emprestimos_file = 'data/emprestimos.json';
$emprestimos = [];

if (file_exists($emprestimos_file)) {
    $emprestimos = json_decode(file_get_contents($emprestimos_file), true) ?? [];
    
    // Filtra apenas os empréstimos do usuário atual
    $emprestimos = array_filter($emprestimos, function($emprestimo) {
        return $emprestimo['created_by'] == $_SESSION['user_id'];
    });
}

// Carrega clientes para adicionar informações aos empréstimos
$clientes_file = 'data/clientes.json';
$clientes = [];
$clientes_map = [];

if (file_exists($clientes_file)) {
    $clientes = json_decode(file_get_contents($clientes_file), true) ?? [];
    
    // Filtra apenas os clientes do usuário atual
    $clientes = array_filter($clientes, function($cliente) {
        return $cliente['created_by'] == $_SESSION['user_id'];
    });
    
    $clientes_map = array_column($clientes, null, 'id');
}

// Data atual no fuso horário de Brasília
$hoje = date('Y-m-d');
$data_hoje = new DateTime($hoje);
$data_hoje->setTimezone(new DateTimeZone('America/Sao_Paulo'));

// Log para depuração do fuso horário
error_log("Fuso horário do servidor: " . date_default_timezone_get());
error_log("Data atual (Brasília): " . $hoje);
error_log("Hora atual (Brasília): " . date('H:i:s'));

// Lista para armazenar clientes com pagamentos para hoje
$clientes_hoje = [];

// Processa os empréstimos para encontrar pagamentos de hoje
foreach ($emprestimos as $emprestimo) {
    // Pula empréstimos finalizados
    if ($emprestimo['status'] === 'finalizado') {
        continue;
    }
    
    $cliente_id = $emprestimo['cliente_id'];
    $cliente = $clientes_map[$cliente_id] ?? null;
    
    // Pula se o cliente não for encontrado
    if (!$cliente) {
        continue;
    }
    
    // Verifica se é empréstimo diário
    if ($emprestimo['tipo_juros'] === 'diario') {
        // Verifica diárias para hoje e acumuladas
        $valor_total = 0;
        $diarias_pendentes = [];
        $tem_diaria_hoje = false;
        
        // Adiciona logs para depuração
        error_log("Processando empréstimo ID: " . $emprestimo['id'] . ", Cliente: " . $cliente['nome']);
        error_log("Data do empréstimo: " . $emprestimo['data_emprestimo'] . ", Hoje: " . $hoje);
        
        // Converte a data do empréstimo para o fuso horário de Brasília
        $data_emprestimo = new DateTime($emprestimo['data_emprestimo']);
        $data_emprestimo->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $data_emprestimo_str = $data_emprestimo->format('Y-m-d');
        error_log("Data do empréstimo (Brasília): " . $data_emprestimo_str);
        
        // Primeiro, vamos contar quantas diárias pendentes ou atrasadas existem
        $total_pendentes_ou_atrasadas = 0;
        foreach ($emprestimo['diarias'] as $diaria) {
            if ($diaria['status'] === 'pendente' || $diaria['status'] === 'atrasado') {
                $total_pendentes_ou_atrasadas++;
                error_log("Diária pendente/atrasada: " . $diaria['numero'] . ", Data: " . $diaria['data'] . ", Status: " . $diaria['status']);
            }
        }
        error_log("Total de diárias pendentes ou atrasadas: " . $total_pendentes_ou_atrasadas);
        
        // Agora vamos processar apenas as diárias que devem ser incluídas
        foreach ($emprestimo['diarias'] as $diaria) {
            // Só processa diárias pendentes ou atrasadas
            if ($diaria['status'] === 'pendente' || $diaria['status'] === 'atrasado') {
                // Converte a data da diária para o fuso horário de Brasília
                $data_diaria = new DateTime($diaria['data']);
                $data_diaria->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                $data_diaria_str = $data_diaria->format('Y-m-d');
                
                // Ignora diárias do dia do empréstimo
                if ($data_diaria_str === $data_emprestimo_str) {
                    error_log("Ignorando diária do dia do empréstimo: " . $diaria['numero'] . ", Data: " . $data_diaria_str);
                    continue;
                }
                
                // Se a diária é de hoje ou anterior
                if ($data_diaria_str <= $hoje) {
                    $tem_diaria_hoje = true;
                    $valor_diaria = $diaria['valor'];
                    
                    error_log("Incluindo diária: " . $diaria['numero'] . ", Data: " . $data_diaria_str . ", Valor: " . $valor_diaria);
                    
                    // Adiciona à lista de diárias pendentes
                    $diarias_pendentes[] = [
                        'numero' => $diaria['numero'],
                        'data' => $data_diaria_str,
                        'valor' => $valor_diaria
                    ];
                    
                    $valor_total += $valor_diaria;
                } else {
                    error_log("Ignorando diária futura: " . $diaria['numero'] . ", Data: " . $data_diaria_str);
                }
            }
        }
        
        error_log("Total de diárias incluídas: " . count($diarias_pendentes) . ", Valor total: " . $valor_total);
        
        // Adiciona se tiver diária pendente ou atrasada
        if ($valor_total > 0) {
            // Calcula dias de atraso
            $dias_atraso = 0;
            if (!empty($diarias_pendentes)) {
                $primeira_diaria = min(array_column($diarias_pendentes, 'data'));
                $data_primeira_diaria = new DateTime($primeira_diaria);
                $dias_atraso = $data_hoje->diff($data_primeira_diaria)->days;
            }
            
            $clientes_hoje[] = [
                'nome' => $cliente['nome'],
                'telefone' => $cliente['telefone'] ?? '',
                'valor' => $valor_total,
                'emprestimo_id' => $emprestimo['id'],
                'diarias_pendentes' => $diarias_pendentes,
                'tipo' => 'diaria',
                'status' => $dias_atraso > 0 ? 'atrasado' : 'hoje',
                'dias_atraso' => $dias_atraso,
                'data_vencimento' => $hoje
            ];
            error_log("Cliente adicionado à lista de expiração hoje");
        }
    } else {
        // Empréstimo mensal - verifica se o primeiro pagamento é hoje
        // Converte a data do primeiro pagamento para o fuso horário de Brasília
        $data_primeiro_pagamento = new DateTime($emprestimo['data_primeiro_pagamento']);
        $data_primeiro_pagamento->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        $data_primeiro_pagamento_str = $data_primeiro_pagamento->format('Y-m-d');
        
        error_log("Empréstimo mensal ID: " . $emprestimo['id'] . ", Cliente: " . $cliente['nome']);
        error_log("Data primeiro pagamento: " . $data_primeiro_pagamento_str . ", Hoje: " . $hoje);
        
        // Verifica se o pagamento é hoje OU se já está vencido (data anterior a hoje)
        if ($data_primeiro_pagamento_str === $hoje || $data_primeiro_pagamento_str < $hoje) {
            // Calcula o valor a ser pago (principal + juros)
            $valor_principal = $emprestimo['valor'];
            $valor_juros = $valor_principal * ($emprestimo['taxa_juros'] / 100);
            $valor_total = $valor_principal + $valor_juros;
            
            // Verifica se há atrasos acumulados
            if (isset($emprestimo['atrasos']) && is_array($emprestimo['atrasos'])) {
                foreach ($emprestimo['atrasos'] as $atraso) {
                    $valor_total += $atraso['valor'];
                }
            }
            
            // Verifica se está vencido para adicionar essa informação
            $status = ($data_primeiro_pagamento_str < $hoje) ? 'vencido' : 'hoje';
            $dias_atraso = 0;
            $dias_atraso_data_final = 0;
            $data_final_ultrapassada = false;
            
            if ($status === 'vencido') {
                $data_hoje_obj = new DateTime($hoje);
                $dias_atraso = $data_hoje_obj->diff($data_primeiro_pagamento)->days;
                error_log("Empréstimo vencido há " . $dias_atraso . " dias");
                
                // Verifica se existe data final e se ela foi ultrapassada
                if (isset($emprestimo['data_final']) && !empty($emprestimo['data_final'])) {
                    $data_final = new DateTime($emprestimo['data_final']);
                    $data_final->setTimezone(new DateTimeZone('America/Sao_Paulo'));
                    $data_final_str = $data_final->format('Y-m-d');
                    
                    if ($hoje > $data_final_str) {
                        $data_final_ultrapassada = true;
                        $dias_atraso_data_final = $data_hoje_obj->diff($data_final)->days;
                        error_log("Data final ultrapassada há " . $dias_atraso_data_final . " dias");
                    }
                }
            }
            
            error_log("Empréstimo mensal incluído: Valor total: " . $valor_total . ", Status: " . $status);
            
            $clientes_hoje[] = [
                'nome' => $cliente['nome'],
                'telefone' => $cliente['telefone'] ?? '',
                'valor' => $valor_total,
                'emprestimo_id' => $emprestimo['id'],
                'tipo' => 'mensal',
                'status' => $status,
                'dias_atraso' => $dias_atraso,
                'data_vencimento' => $data_primeiro_pagamento_str,
                'data_final_ultrapassada' => $data_final_ultrapassada,
                'dias_atraso_data_final' => $dias_atraso_data_final
            ];
        }
    }
}

// Retorna a resposta
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'clientes' => $clientes_hoje
]);
exit;
?> 