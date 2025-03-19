<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?php include_once 'meta.php'; ?>
    <title>Ativar Chave</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, height=device-height">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
   <style>
        body {
            background-color: #121212;
            color: #fff;
        }
        .card {
            background-color: #1e1e1e;
            border-color: #1e1e1e;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(33, 33, 33, 0.15);
        }
        .card-header {
            background-color: #252525;
            border-color: #198754;
        }
        .modal-content {
            background-color: #1e1e1e;
            color: #fff;
            border-color: #1e1e1e;
        }
        .modal-header, .modal-footer {
            border-color: #1e1e1e;
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .nav-tabs .nav-link {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s, border-color 0.3s;
            padding: 0.5rem 1rem;
            margin-right: 0.5rem;
            border: 1px solid transparent;
        }
        .nav-tabs .nav-link:hover {
            color: #198754;
            border-color: transparent transparent #198754 transparent;
            border-bottom-width: 2px;
        }
        .nav-tabs .nav-link.active {
            background-color: #1e1e1e;
            color: #198754;
            border-color: #198754 #198754 #1e1e1e;
            border-bottom-width: 2px;
            font-weight: 500;
        }
        .table-striped>tbody>tr:nth-of-type(odd) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        .border-bottom {
            border-color: #1e1e1e !important;
        }
        .btn-secondary {
            background-color: #333;
            border-color: #1e1e1e;
        }
        .btn-info {
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
        .btn-info:hover {
            color: #fff;
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        .table-dark {
            --bs-table-bg: #1e1e1e;
            --bs-table-striped-bg: #252525;
            --bs-table-striped-color: #fff;
            --bs-table-active-bg: #2c2c2c;
            --bs-table-active-color: #fff;
            --bs-table-hover-bg: #282828;
            --bs-table-hover-color: #fff;
            color: #fff;
            border-color: #1e1e1e;
        }
        .table {
            border-color: #1e1e1e;
            --bs-table-border-color: #1e1e1e;
        }
        .table>:not(caption)>*>* {
            border-bottom-width: 0;
        }
        .table>:not(:first-child) {
            border-top: none;
        }
        .nav-tabs {
            border-bottom: 1px solid #198754;
        }
        .text-primary {
            color: #0d6efd !important;
        }
        .text-success {
            color: #198754 !important;
        }
        .text-warning {
            color: #ffc107 !important;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .text-info {
            color: #0dcaf0 !important;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            border-radius: 0.2rem;
        }
        /* Remover sublinhados */
        a, a:hover, a:focus, a:active {
            text-decoration: none;
        }
        .nav-link, .nav-link:hover, .nav-link:focus, .nav-link:active {
            text-decoration: none;
        }
        .btn, .btn:hover, .btn:focus, .btn:active {
            text-decoration: none;
        }
        .navbar-brand, .navbar-brand:hover, .navbar-brand:focus, .navbar-brand:active {
            text-decoration: none;
        }
        .table td, .table th {
            border-color: #1e1e1e !important;
            border: none !important;
        }
        
        /* Estilos para a navbar igual à página index */
        .navbar {
            background-color: #212529 !important;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
            min-height: 56px;
        }
        
        .navbar-dark .navbar-brand {
            color: #fff;
            font-size: 1.25rem;
            padding-top: 0.3125rem;
            padding-bottom: 0.3125rem;
        }
        
        .navbar-dark .navbar-nav .nav-link {
            color: #fff;
            transition: color 0.3s ease;
        }
        
        .navbar-dark .navbar-nav .nav-link.active {
            color: #198754;
        }
        
        .navbar-dark .navbar-nav .nav-link:hover {
            color: #198754;
        }
        
        .navbar .nav-link {
            padding: 0.5rem 1rem;
        }
    </style>
</head>
<body style="height: 100%; display: block; padding: 0; margin: 0; overflow: hidden;">
    <div class="container-fluid" style="max-width: 450px; padding: 15px; margin: 0; position: absolute; top: 0; left: 0;">
        <div class="row mb-3 mx-0">
            <div class="col-12 px-0">
                <div class="card" style="border: 1px solid #2d2d2d; width: 100%; margin: 0; max-width: 450px;">
                    <div class="card-body d-flex justify-content-between align-items-center" style="padding: 12px 15px;">
                        <div>
                            <h2 class="text-white mb-0" style="font-size: 1.2rem;">Ativar Chave</h2>
                        </div>
                        <div class="d-flex">
                            <a href="index.php" class="btn btn-primary me-2" style="padding: 6px 12px; font-size: 14px; height: 38px; line-height: 1.5;">
                                <i class="bi bi-arrow-left"></i> Voltar
                            </a>
                            <a href="logout.php" class="btn btn-danger" style="padding: 6px 12px; font-size: 14px; height: 38px; line-height: 1.5;">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isBloqueado($_SESSION['user_id'])): ?>
        <div class="row mb-3 mx-0">
            <div class="col-12 px-0">
                <div class="alert alert-warning" style="padding: 12px 15px; margin-bottom: 15px; font-size: 14px; max-width: 450px; margin: 0 auto 15px auto;">
                    <strong>Atenção!</strong> Assinatura expirada.
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="row justify-content-center mx-0">
            <div class="col-12 px-0">
                <div class="card" style="border: 1px solid #2d2d2d; width: 100%; max-width: 450px; margin: 0 auto;">
                    <div class="card-body" style="padding: 20px;">
                        <h4 class="text-white mb-3" style="font-size: 1.1rem;">Ativar Nova Chave</h4>
                        <div id="mensagem"></div>
                        <form id="formAtivar" method="post" class="mt-3">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="chave_acesso" name="chave_acesso" required style="font-size: 16px; padding: 10px 15px; height: 45px;" placeholder="Chave de Acesso">
                            </div>
                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 10px 15px; height: 45px;">
                                    <i class="bi bi-key"></i> Ativar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Garantir que a viewport tenha o tamanho correto
    function fixViewport() {
        const viewport = document.querySelector('meta[name="viewport"]');
        if (viewport) {
            viewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, height=device-height');
        }
        
        // Forçar scroll para o topo
        window.scrollTo(0, 0);
    }
    
    // Ajustar tamanhos para caber em uma tela
    function adjustSizes() {
        // Ajustar tamanho do container
        $('body').css({
            'width': '100%',
            'height': '100%',
            'overflow-y': 'hidden'
        });
        
        // Detectar se está em modo desktop ou mobile
        const isDesktop = window.innerWidth > 768;
        
        $('.container-fluid').css({
            'width': '100%',
            'max-width': isDesktop ? '450px' : '300px',
            'margin': '0',
            'position': 'absolute',
            'top': '0',
            'left': '0',
            'padding': '15px'
        });
        
        // Centralizar cards
        $('.card').css({
            'margin': '0',
            'max-width': isDesktop ? '450px' : '300px'
        });
        
        // Ajustar tamanho do input e botão
        $('.form-control').css({
            'height': isDesktop ? '45px' : '36px',
            'padding': isDesktop ? '10px 15px' : '6px 10px',
            'font-size': isDesktop ? '16px' : '14px'
        });
        
        $('.btn-primary[type="submit"]').css({
            'padding': isDesktop ? '10px 15px' : '6px 10px',
            'font-size': isDesktop ? '16px' : '14px',
            'height': isDesktop ? '45px' : '36px'
        });
        
        // Ajustar tamanho dos botões de navegação
        $('.btn-primary:not([type="submit"]), .btn-danger').css({
            'padding': isDesktop ? '6px 12px' : '4px 8px',
            'font-size': isDesktop ? '14px' : '12px',
            'height': isDesktop ? '38px' : '30px'
        });
        
        // Ajustar tamanho dos títulos
        $('h2.text-white').css({
            'font-size': isDesktop ? '1.2rem' : '0.9rem'
        });
        
        $('h4.text-white').css({
            'font-size': isDesktop ? '1.1rem' : '0.9rem'
        });
        
        // Ajustar espaçamentos
        $('.card-body').css({
            'padding': isDesktop ? '20px' : '8px'
        });
        
        // Forçar scroll para o topo
        setTimeout(function() {
            window.scrollTo(0, 0);
        }, 100);
    }
    
    $(document).ready(function() {
        // Corrigir viewport
        fixViewport();
        
        // Ajustar tamanhos
        adjustSizes();
        
        // Reajustar ao redimensionar
        $(window).on('resize', adjustSizes);
        
        // Forçar scroll para o topo quando a página carrega
        $(window).on('load', function() {
            window.scrollTo(0, 0);
        });
        
        $('#formAtivar').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: 'ativar_chave.php',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    var alertClass = response.sucesso ? 'alert-success' : 'alert-danger';
                    $('#mensagem').html('<div class="alert ' + alertClass + '" style="padding: 10px 15px; font-size: 14px; margin-bottom: 15px;">' + response.mensagem + '</div>');
                    
                    if (response.sucesso) {
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 2000);
                    }
                },
                error: function() {
                    $('#mensagem').html('<div class="alert alert-danger" style="padding: 10px 15px; font-size: 14px; margin-bottom: 15px;">Erro ao processar requisição.</div>');
                }
            });
        });
    });
    </script>
</body>
</html> 