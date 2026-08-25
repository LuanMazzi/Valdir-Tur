<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

// Período padrão: do primeiro dia do mês atual até hoje
$dataInicio = trim($_GET['dataInicio'] ?? date('Y-m-01'));
$dataFim    = trim($_GET['dataFim'] ?? date('Y-m-d'));

$dataInicioEscapada = mysqli_real_escape_string($conexao, $dataInicio);
$dataFimEscapada    = mysqli_real_escape_string($conexao, $dataFim);

// Vendas do período, com nome do cliente em vez do ID cru
$sqlVendas = "SELECT
            venda.*,
            cliente.tipoCliente AS clienteTipo, cliente.nome AS clienteNome, cliente.sobrenome AS clienteSobrenome, cliente.razaoSocial AS clienteRazaoSocial
        FROM tbvenda venda
        LEFT JOIN tbCliente cliente ON venda.idCliente = cliente.idCliente
        WHERE venda.dataVenda BETWEEN '$dataInicioEscapada' AND '$dataFimEscapada'
        ORDER BY venda.dataVenda DESC";
$resultadoVendas = mysqli_query($conexao, $sqlVendas);

// Fretamentos do período, com nome do cliente e destino
$sqlFretamentos = "SELECT
            fretamento.*,
            cliente.tipoCliente AS clienteTipo, cliente.nome AS clienteNome, cliente.sobrenome AS clienteSobrenome, cliente.razaoSocial AS clienteRazaoSocial
        FROM tbFretamento fretamento
        LEFT JOIN tbCliente cliente ON fretamento.idCliente = cliente.idCliente
        WHERE DATE(fretamento.dataHoraSaida) BETWEEN '$dataInicioEscapada' AND '$dataFimEscapada'
        ORDER BY fretamento.dataHoraSaida DESC";
$resultadoFretamentos = mysqli_query($conexao, $sqlFretamentos);

// Totais pro resumo. SUM/COUNT direto no banco, sem precisar somar linha por linha em PHP
$resumoVendas = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total, COALESCE(SUM(valorRecebido), 0) AS soma FROM tbvenda WHERE dataVenda BETWEEN '$dataInicioEscapada' AND '$dataFimEscapada'"));
$resumoFretamentos = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total, COALESCE(SUM(preco), 0) AS soma FROM tbFretamento WHERE DATE(dataHoraSaida) BETWEEN '$dataInicioEscapada' AND '$dataFimEscapada'"));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<title>Relatórios</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <div class="d-flex align-items-center">
            <button type="button" class="botao-voltar" onclick="window.location.href='adminvt.php'">
                <i class="bi bi-bar-chart-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label><strong>Relatórios</strong></label>
        </div>

        <form method="GET" class="d-flex align-items-end gap-2 mt-3 flex-wrap">
            <div>
                <label class="form-label mb-1">De</label>
                <input type="date" class="form-control" name="dataInicio" value="<?= htmlspecialchars($dataInicio) ?>">
            </div>
            <div>
                <label class="form-label mb-1">Até</label>
                <input type="date" class="form-control" name="dataFim" value="<?= htmlspecialchars($dataFim) ?>">
            </div>
            <button class="btn btn-dark" type="submit">Filtrar</button>
        </form>

        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="card-desenho h-100">
                    <div class="card-informacoes">
                        <p><strong>Vendas no período</strong></p>
                        <h1 class="numero-total">R$ <?= number_format((float) $resumoVendas['soma'], 2, ',', '.') ?></h1>
                        <small><?= (int) $resumoVendas['total'] ?> venda(s)</small>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-desenho h-100">
                    <div class="card-informacoes">
                        <p><strong>Fretamentos no período</strong></p>
                        <h1 class="numero-total">R$ <?= number_format((float) $resumoFretamentos['soma'], 2, ',', '.') ?></h1>
                        <small><?= (int) $resumoFretamentos['total'] ?> fretamento(s)</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card-principal">
                    <div class="card-principal-conteudo">
                        <strong><label>Vendas</label></strong>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Data</th>
                                    <th scope="col">Valor Recebido</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultadoVendas) === 0) { ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">Nenhuma venda no período.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($venda = mysqli_fetch_assoc($resultadoVendas)) { ?>
                                        <?php
                                        $nomeCliente = $venda['clienteTipo'] === 'PJ'
                                            ? $venda['clienteRazaoSocial']
                                            : trim($venda['clienteNome'] . ' ' . $venda['clienteSobrenome']);
                                        ?>
                                        <tr>
                                            <td><?= $venda['idVenda'] ?></td>
                                            <td><?= htmlspecialchars($nomeCliente) ?></td>
                                            <td><?= $venda['dataVenda'] ? date('d/m/Y', strtotime($venda['dataVenda'])) : '' ?></td>
                                            <td>R$ <?= number_format((float) $venda['valorRecebido'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($venda['status']) ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card-principal">
                    <div class="card-principal-conteudo">
                        <strong><label>Fretamentos</label></strong>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">Cliente</th>
                                    <th scope="col">Destino</th>
                                    <th scope="col">Saída</th>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultadoFretamentos) === 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Nenhum fretamento no período.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($fretamento = mysqli_fetch_assoc($resultadoFretamentos)) { ?>
                                        <?php
                                        $nomeCliente = $fretamento['clienteTipo'] === 'PJ'
                                            ? $fretamento['clienteRazaoSocial']
                                            : trim($fretamento['clienteNome'] . ' ' . $fretamento['clienteSobrenome']);
                                        ?>
                                        <tr>
                                            <td><?= $fretamento['idFretamento'] ?></td>
                                            <td><?= htmlspecialchars($nomeCliente) ?></td>
                                            <td><?= htmlspecialchars($fretamento['destino']) ?></td>
                                            <td><?= $fretamento['dataHoraSaida'] ? date('d/m/Y H:i', strtotime($fretamento['dataHoraSaida'])) : '' ?></td>
                                            <td>R$ <?= number_format((float) $fretamento['preco'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($fretamento['status']) ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </section>
    </main>
</body>

</html>
