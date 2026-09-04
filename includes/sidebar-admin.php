<?php
    // Nome do arquivo que está sendo executado agora, ex: "veiculos.php"
    $paginaAtual = basename($_SERVER['PHP_SELF']);

    // Devolve "active" quando o arquivo do link bate com a página atual
    function ativo($arquivo, $paginaAtual)
    {
        return $arquivo === $paginaAtual ? 'active' : '';
    }
?>
<div class="d-flex flex-column flex-shrink-0 p-3 bg-body-tertiary border-end" style="width: 280px; height: 100vh;">
        <a href="/ValdirTur/admin/adminvt.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
            <span class="fs-4 fw-bold">Painel de Controle</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="/ValdirTur/admin/adminvt.php" class="nav-link <?= ativo('adminvt.php', $paginaAtual) ?>" aria-current="page">Início</a>
            </li>
            <li class="mb-1">
                <!-- Botão Principal -->
                <a href="/ValdirTur/admin/listas/veiculos.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('veiculos.php', $paginaAtual) ?>"> Veículos </a>

                <a href="/ValdirTur/admin/listas/funcionarios.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('funcionarios.php', $paginaAtual) ?>"> Funcionários </a>

                <a href="/ValdirTur/admin/listas/clientes.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('clientes.php', $paginaAtual) ?>"> Clientes </a>

                <a href="/ValdirTur/admin/listas/pacotes.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('pacotes.php', $paginaAtual) ?>"> Pacotes </a>

            </li>

            <li class="mb-1">
                <a href="/ValdirTur/admin/relatorios.php" class="btn d-inline-flex align-items-center rounded nav-link link-body-emphasis w-100 <?= ativo('relatorios.php', $paginaAtual) ?>"> Relatórios </a>
            </li>

            <li>
                <a href="/ValdirTur/admin/listas/fretamentos.php" class="nav-link link-body-emphasis <?= ativo('fretamentos.php', $paginaAtual) ?>">Fretamento</a>
            </li>
            <li>
                <a href="/ValdirTur/admin/listas/vendas.php" class="nav-link link-body-emphasis <?= ativo('vendas.php', $paginaAtual) ?>">Vendas</a>
            </li>

        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center link-body-emphasis text-decoration-none dropdown-toggle"
                data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/ValdirTur/assets/icons/logo-menor.png" alt="Logo da Valdir Tur (menor)" width="32" height="32"
                    class="rounded-circle me-2">
                <strong>Admin</strong>
            </a>
            <ul class="dropdown-menu text-small shadow">
                
                <li><a class="dropdown-item" href="/ValdirTur/logout.php">Sair</a></li>
            </ul>
        </div>
    </div>