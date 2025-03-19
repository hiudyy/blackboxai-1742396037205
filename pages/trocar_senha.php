<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $erro = null;

    if (strlen($nova_senha) < 6) {
        $erro = "A nova senha deve ter pelo menos 6 caracteres.";
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem.";
    } else {
        $usuarios = lerUsuarios();
        $usuarios_atualizados = [];
        $senha_atualizada = false;

        foreach ($usuarios as $usuario) {
            if ($usuario['id'] == $_SESSION['user_id']) {
                $usuario['senha'] = password_hash($nova_senha, PASSWORD_DEFAULT);
                $usuario['trocar_senha'] = 0; // Marca que não precisa mais trocar a senha
                $senha_atualizada = true;
            }
            $usuarios_atualizados[] = $usuario;
        }

        if ($senha_atualizada) {
            salvarUsuarios($usuarios_atualizados);
            header("Location: index.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include_once 'meta.php'; ?>
    <title>Trocar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 col-sm-12 px-3">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Trocar Senha</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger">
                                <?php echo $erro; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="nova_senha" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-system w-100">Alterar Senha</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="mobile-fixes.js"></script>
</body>
</html> 