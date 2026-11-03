<?php
    // Página atual
    $paginaAtual = basename($_SERVER['PHP_SELF']);

    // Marca o link do menu que corresponde à página atual
    function ativo($arquivo, $paginaAtual)
    {
        return $arquivo === $paginaAtual ? 'active' : '';
    }

    $nome = $_SESSION['admin']['nome'] ?? 'admin';
?>
<div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary border-end" style="width: 280px; height: 100vh;">
        <a href="/ValdirTur/admin/adminvt.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4 fw-bold">Painel de Controle</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <h5><a href="/ValdirTur/admin/adminvt.php" class="nav-link <?= ativo('adminvt.php', $paginaAtual) ?>" aria-current="page">Início</a></h5>
            </li>
            <li class="mb-1">
                <!-- Links do menu -->
                <h5><a href="/ValdirTur/admin/listas/veiculos.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('veiculos.php', $paginaAtual) ?>"> Veículos </a></h5>

                <h5><a href="/ValdirTur/admin/listas/funcionarios.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('funcionarios.php', $paginaAtual) ?>"> Funcionários </a></h5>

                <h5><a href="/ValdirTur/admin/listas/clientes.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('clientes.php', $paginaAtual) ?>"> Clientes </a></h5>

                <h5><a href="/ValdirTur/admin/listas/pacotes.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('pacotes.php', $paginaAtual) ?>"> Pacotes </a></h5>
            
                <h5><a href="/ValdirTur/admin/listas/fretamentos.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('fretamentos.php', $paginaAtual) ?>"> Fretamento </a></h5>
    
                <h5><a href="/ValdirTur/admin/listas/vendas.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('vendas.php', $paginaAtual) ?>"> Vendas </a></h5>
            
                <h5><a href="/ValdirTur/admin/relatorios.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('relatorios.php', $paginaAtual) ?>"> Relatórios </a></h5>
            </li>

        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/ValdirTur/assets/logos/logo-vt.svg" alt="Logo da Valdir Tur" width="auto" height="32"
                    class="me-2">
                <strong><?= htmlspecialchars($nome) ?></strong>
            </a>
            <ul class="dropdown-menu text-small shadow">
                
                <li><a class="dropdown-item" href="/ValdirTur/logout.php">Sair</a></li>
            </ul>
        </div>
    </div>