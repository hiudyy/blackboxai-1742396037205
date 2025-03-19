<?php
include '../includes/routes.php';

// Get the current URI
$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
route($request_uri);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Bem-vindo ao Sistema de Gerenciamento</h1>
        <nav>
            <ul>
                <li><a href="pages/buscar_cliente.php">Gerenciar Clientes</a></li>
                <li><a href="pages/buscar_emprestimo.php">Gerenciar Empréstimos</a></li>
                <li><a href="admin/admin_painel.php">Painel Administrativo</a></li>
                <li><a href="pages/login.php">Login</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Sobre o Sistema</h2>
        <p>Este sistema permite gerenciar clientes e empréstimos de forma eficiente.</p>
    </main>
    <footer>
        <p>&copy; 2023 Sistema de Gerenciamento</p>
    </footer>
</body>
</html>
