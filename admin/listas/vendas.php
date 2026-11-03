<?php
session_start();

// Acesso apenas para admin logado
if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

// Configurações e conexão com o banco
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conexao, $_GET['excluir']);
    mysqli_query($conexao, "DELETE FROM tbvenda WHERE idVenda = '$id'");
    header('Location: vendas.php');
    exit;
}

// Busca
$busca = trim($_GET['busca'] ?? '');
$where = "";
if ($busca !== "") {
    $buscaTabela = mysqli_real_escape_string($conexao, $busca);
    $where = "WHERE cliente.nome LIKE '%$buscaTabela%' OR cliente.sobrenome LIKE '%$buscaTabela%' OR cliente.razaoSocial LIKE '%$buscaTabela%'
        OR funcionario.nome LIKE '%$buscaTabela%' OR funcionario.sobrenome LIKE '%$buscaTabela%'
        OR pacote.nomePacote LIKE '%$buscaTabela%'
        OR fretamento.destino LIKE '%$buscaTabela%'";
}

// Lista com os nomes de cliente, funcionário, pacote e fretamento (JOIN)
$sql = "SELECT
            venda.*,
            cliente.tipoCliente AS clienteTipo, cliente.nome AS clienteNome, cliente.sobrenome AS clienteSobrenome, cliente.razaoSocial AS clienteRazaoSocial,
            funcionario.nome AS funcionarioNome, funcionario.sobrenome AS funcionarioSobrenome,
            pacote.nomePacote AS pacoteNome,
            fretamento.destino AS fretamentoDestino, fretamento.dataHoraSaida AS fretamentoSaida
        FROM tbvenda venda
        LEFT JOIN tbCliente cliente ON venda.idCliente = cliente.idCliente
        LEFT JOIN tbFuncionario funcionario ON venda.idFuncionario = funcionario.idFuncionario
        LEFT JOIN tbpacote pacote ON venda.idPacote = pacote.idPacote
        LEFT JOIN tbFretamento fretamento ON venda.idFretamento = fretamento.idFretamento
        $where
        ORDER BY venda.idVenda DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt_BR">
<title>Vendas</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
        <section class="container py-5">

            <div class="d-flex align-items-center">
                <h3><strong>Vendas</strong></h3>

                <a href="../venda.php" class="ms-auto text-decoration-none">
                    <button class="btn btn-dark" type="button">Adicionar venda</button>
                </a>
            </div>

            <!-- Campo de busca -->
            <form method="GET" class="d-flex mt-3" style="max-width: 400px;">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por cliente, funcionário, pacote ou fretamento..." value="<?= htmlspecialchars($busca) ?>">
                <button class="btn btn-outline-secondary ms-2" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <div class="row mt-3">
                <!-- Tabela -->
                <div class="col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">N°</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Funcionário</th>
                                        <th scope="col">Pacote / Fretamento</th>
                                        <th scope="col">Data da Venda</th>
                                        <th scope="col">Valor Recebido</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($resultado) === 0) { ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Nenhuma venda cadastrada.</td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php while ($venda = mysqli_fetch_assoc($resultado)) { ?>
                                            <?php
                                            $nomeCliente = $venda['clienteTipo'] === 'PJ'
                                                ? $venda['clienteRazaoSocial']
                                                : trim($venda['clienteNome'] . ' ' . $venda['clienteSobrenome']);
                                            ?>
                                            <tr>
                                                <td><?= $venda['idVenda'] ?></td>
                                                <td><?= htmlspecialchars($nomeCliente) ?></td>
                                                <td><?= htmlspecialchars(trim($venda['funcionarioNome'] . ' ' . $venda['funcionarioSobrenome'])) ?></td>
                                                <td>
                                                    <?php if ($venda['idFretamento'] !== null): ?>
                                                        <!-- Venda de fretamento: mostra o número, o destino (só a cidade) e a data de saída -->
                                                        <span class="badge text-bg-secondary">Fretamento nº <?= $venda['idFretamento'] ?></span><br>
                                                        <?= htmlspecialchars(trim(explode(',', (string) $venda['fretamentoDestino'])[0])) ?>
                                                        <?= $venda['fretamentoSaida'] ? ' · ' . date('d/m/Y', strtotime($venda['fretamentoSaida'])) : '' ?>
                                                    <?php elseif ($venda['idPacote'] !== null): ?>
                                                        <!-- Venda de pacote -->
                                                         
                                                        <span class="badge text-bg-primary">Pacote nº <?= $venda['idPacote'] ?></span><br>
                                                        <?= htmlspecialchars((string) $venda['pacoteNome']) ?>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= $venda['dataVenda'] ? date('d/m/Y', strtotime($venda['dataVenda'])) : '' ?></td>
                                                <td>R$ <?= number_format((float) $venda['valorRecebido'], 2, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($venda['status']) ?></td>
                                                <td>
                                                    <a href="../editar/venda.php?id=<?= $venda['idVenda'] ?>" class="text-decoration-none">
                                                        <button type="button" class="btn btn-warning"><i class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <a href="vendas.php?excluir=<?= $venda['idVenda'] ?>" class="text-decoration-none"
                                                        onclick="return confirm('Tem certeza que deseja excluir esta venda?')">
                                                        <button type="button" class="btn btn-danger"><i class="bi bi-trash"></i></button>
                                                    </a>
                                                </td>
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
