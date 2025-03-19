<?php
require_once 'config.php';

// Função para obter total de dias de assinatura do usuário
function obterDiasAssinatura($user_id) {
    // Verifica se o usuário é admin - se for, retorna um valor muito alto (dias "infinitos")
    if (isAdmin($user_id)) {
        return 999999; // Valor muito alto para representar "infinito"
    }
    
    $dias_totais = 0;
    $arquivo_ativacoes = 'ativacoes.txt';
    
    if (file_exists($arquivo_ativacoes)) {
        $ativacoes = file($arquivo_ativacoes, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($ativacoes as $ativacao) {
            $dados = json_decode($ativacao, true);
            if ($dados && $dados['user_id'] == $user_id) {
                $dias_totais += $dados['validade_dias'];
            }
        }
    }
    
    return $dias_totais;
}

// Carrega todos os usuários
$usuarios = lerUsuarios();
$usuarios_atualizados = [];
$usuarios_bloqueados = 0;

// Verifica cada usuário
foreach ($usuarios as $usuario) {
    // Não bloqueia usuários admin
    if (!$usuario['is_admin']) {
        $dias_assinatura = obterDiasAssinatura($usuario['id']);
        
        // Se os dias de assinatura expiraram e o usuário não está bloqueado
        if ($dias_assinatura <= 0 && !$usuario['bloqueado']) {
            $usuario['bloqueado'] = true;
            $usuarios_bloqueados++;
        }
    }
    $usuarios_atualizados[] = $usuario;
}

// Salva as alterações se houver usuários bloqueados
if ($usuarios_bloqueados > 0) {
    salvarUsuarios($usuarios_atualizados);
    echo "Total de usuários bloqueados por expiração: " . $usuarios_bloqueados;
} else {
    echo "Nenhum usuário precisou ser bloqueado.";
} 