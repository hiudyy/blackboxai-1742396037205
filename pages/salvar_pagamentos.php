<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

if (!isset($data['emprestimo_id']) || !isset($data['pagamentos'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit();
}

// Carrega os empréstimos para verificar se o empréstimo pertence ao usuário
$emprestimos = lerEmprestimos();
$emprestimo_encontrado = false;

foreach ($emprestimos as &$emprestimo) {
    if ($emprestimo['id'] == $data['emprestimo_id'] && $emprestimo['user_id'] == $_SESSION['user_id']) {
        $emprestimo_encontrado = true;
        
        // Atualiza o status do empréstimo
        $total_pagamentos = count($data['pagamentos']);
        $total_esperado = $emprestimo['tipo_juros'] === 'diario' ? $emprestimo['qtd_diarias'] : 1;
        
        $emprestimo['status_pagamento'] = $total_pagamentos >= $total_esperado ? 'pago' : 'em_andamento';
        break;
    }
}

if (!$emprestimo_encontrado) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Empréstimo não encontrado']);
    exit();
}

// Salva os pagamentos em um arquivo separado
$arquivo_pagamentos = 'data/pagamentos_' . $data['emprestimo_id'] . '.json';

// Garante que o diretório existe
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Salva os pagamentos
file_put_contents($arquivo_pagamentos, json_encode($data['pagamentos']));

// Atualiza o arquivo de empréstimos
salvarEmprestimos($emprestimos);

header('Content-Type: application/json');
echo json_encode(['success' => true]); 