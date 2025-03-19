<?php
// Cria o diretório data se não existir
if (!file_exists('data')) {
    mkdir('data', 0777, true);
}

// Lista de arquivos para mover
$arquivos = [
    'emprestimos.json',
    'clientes.json',
    'config_emprestimos.json'
];

// Move cada arquivo se existir
foreach ($arquivos as $arquivo) {
    if (file_exists($arquivo)) {
        rename($arquivo, 'data/' . $arquivo);
        echo "Arquivo $arquivo movido com sucesso!\n";
    }
}

echo "Processo concluído!\n";
?> 