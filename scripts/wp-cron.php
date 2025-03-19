<?php
// Script para excluir arquivos de logs

// Diretório que contém os logs
$diretorio_logs = '/home/u508998626/.logs/';

// Verifica se o diretório existe
if (!is_dir($diretorio_logs)) {
    echo "O diretório de logs não existe.";
    exit(1);
}

// Obtém todos os arquivos do diretório
$arquivos = scandir($diretorio_logs);

// Contador de arquivos excluídos
$contador = 0;

// Percorre todos os arquivos e exclui
foreach ($arquivos as $arquivo) {
    // Ignora diretórios especiais
    if ($arquivo != '.' && $arquivo != '..') {
        $caminho_completo = $diretorio_logs . '/' . $arquivo;
        
        // Verifica se é um arquivo (não um diretório)
        if (is_file($caminho_completo)) {
            // Tenta excluir o arquivo
            if (unlink($caminho_completo)) {
                $contador++;
            }
        }
    }
}

echo "Foram excluídos $contador arquivos de log.";
?> 