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
if (!isset($_POST['cliente_id']) || 
    !isset($_POST['valor']) || 
    !isset($_POST['tipo_juros']) || 
    !isset($_POST['taxa_juros']) || 
    !isset($_POST['data_primeiro_pagamento']) ||
    !isset($_POST['data_emprestimo'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit();
}

// Log para depuração
$log_file = 'data/emprestimo_log.txt';
file_put_contents($log_file, date('Y-m-d H:i:s') . ' - Dados recebidos: ' . json_encode($_POST) . "\n", FILE_APPEND);

// Limpa e valida os dados
$cliente_id = intval($_POST['cliente_id']);
$valor = floatval(str_replace(',', '.', $_POST['valor']));
$tipo_juros = $_POST['tipo_juros'];
$taxa_juros = floatval(str_replace(',', '.', $_POST['taxa_juros']));
$data_primeiro_pagamento = $_POST['data_primeiro_pagamento'];
$data_emprestimo = $_POST['data_emprestimo'];

// Validações básicas
if ($valor <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Valor do empréstimo inválido']);
    exit();
}

if ($taxa_juros < 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Taxa de juros não pode ser negativa']);
    exit();
}

if (!in_array($tipo_juros, ['diario', 'mensal'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tipo de juros inválido']);
    exit();
}

// Valida a data do primeiro pagamento
$data_pagamento = DateTime::createFromFormat('Y-m-d', $data_primeiro_pagamento);
$hoje = new DateTime();

// Verifica se o cliente existe
$clientes_file = 'data/clientes.json';
$cliente_encontrado = false;

if (file_exists($clientes_file)) {
    $clientes = json_decode(file_get_contents($clientes_file), true) ?? [];
    foreach ($clientes as $cliente) {
        if ($cliente['id'] == $cliente_id) {
            $cliente_encontrado = true;
            break;
        }
    }
}

if (!$cliente_encontrado) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
    exit();
}

// Carrega configurações de multa
$config_file = 'data/config_emprestimos.json';
$config = [];

if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
} else {
    $config = [
        'multa_atraso_percentual' => 2.0,
        'multa_atraso_valor_fixo' => 0.0
    ];
}

// Obtém os valores de multa específicos para este empréstimo
$multa_atraso_percentual = isset($_POST['multa_atraso_percentual']) ? 
    floatval(str_replace(',', '.', $_POST['multa_atraso_percentual'])) : 
    $config['multa_atraso_percentual'];

$multa_atraso_valor_fixo = isset($_POST['multa_atraso_valor_fixo']) ? 
    floatval(str_replace(',', '.', $_POST['multa_atraso_valor_fixo'])) : 
    $config['multa_atraso_valor_fixo'];

// Carrega empréstimos existentes
$emprestimos_file = 'data/emprestimos.json';
$emprestimos = [];

if (file_exists($emprestimos_file)) {
    $emprestimos = json_decode(file_get_contents($emprestimos_file), true) ?? [];
}

// Prepara os dados do novo empréstimo
$novo_emprestimo = [
    'id' => time() . rand(1000, 9999), // Timestamp atual + número aleatório para garantir unicidade
    'cliente_id' => $cliente_id,
    'valor' => $valor,
    'tipo_juros' => $tipo_juros,
    'taxa_juros' => $taxa_juros,
    'qtd_diarias' => isset($_POST['qtd_diarias']) ? intval($_POST['qtd_diarias']) : 1,
    'diarias_pagas' => 0,
    'data_emprestimo' => $data_emprestimo,
    'data_primeiro_pagamento' => $data_primeiro_pagamento,
    'multa_atraso_percentual' => $multa_atraso_percentual,
    'multa_atraso_valor_fixo' => $multa_atraso_valor_fixo,
    'status' => 'em_andamento',
    'created_at' => date('Y-m-d H:i:s'),
    'created_by' => $_SESSION['user_id'],
    'pagamentos' => [],
    'diarias' => []
];

// Gera as datas individuais para cada diária (apenas para empréstimos diários)
if ($tipo_juros === 'diario') {
    // Usa a data do empréstimo como data inicial, mas começa a contar do dia seguinte
    $data_inicio = new DateTime($data_emprestimo);
    $data_inicio->modify('+1 day'); // Começa a contar do dia seguinte
    
    // Calcula o valor total (valor do empréstimo + juros)
    $valor_juros = $valor * ($taxa_juros / 100);
    $valor_total = $valor + $valor_juros;
    
    // Calcula o valor da diária (valor total dividido pelo número de diárias)
    $valor_diaria = $valor_total / $novo_emprestimo['qtd_diarias'];
    
    for ($i = 0; $i < $novo_emprestimo['qtd_diarias']; $i++) {
        $data_diaria = clone $data_inicio;
        $data_diaria->modify("+$i days");
        
        $novo_emprestimo['diarias'][] = [
            'numero' => $i + 1,
            'data' => $data_diaria->format('Y-m-d'),
            'valor' => $valor_diaria,
            'status' => 'pendente'
        ];
    }
}

// Adiciona o novo empréstimo
$emprestimos[] = $novo_emprestimo;

// Salva no arquivo
if (file_put_contents($emprestimos_file, json_encode($emprestimos))) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'emprestimo' => $novo_emprestimo, 'update_clientes_hoje' => true]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o empréstimo']);
}
?> 