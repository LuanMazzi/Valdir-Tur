<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');


if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conexao, $_GET['excluir']);
    mysqli_query($conexao, "DELETE FROM tbFretamento WHERE idFretamento = '$id'");
    header('Location: fretamentos.php');
    exit;
}

// Se veio ?busca=X na URL, filtra por destino, cliente ou veículo
$busca = trim($_GET['busca'] ?? '');
$where = "";
if ($busca !== "") {
    $buscaEscapada = mysqli_real_escape_string($conexao, $busca);
    $where = "WHERE fretamento.destino LIKE '%$buscaEscapada%'
        OR cliente.nome LIKE '%$buscaEscapada%' OR cliente.sobrenome LIKE '%$buscaEscapada%' OR cliente.razaoSocial LIKE '%$buscaEscapada%'
        OR veiculo.nomeIdentificacao LIKE '%$buscaEscapada%'";
}

// JOIN com veículo, cliente e funcionário só pra mostrar nome em vez do ID cru
$sql = "SELECT
            fretamento.*,
            veiculo.nomeIdentificacao AS veiculoNome,
            cliente.tipoCliente AS clienteTipo, cliente.nome AS clienteNome, cliente.sobrenome AS clienteSobrenome, cliente.razaoSocial AS clienteRazaoSocial,
            funcionario.nome AS funcionarioNome, funcionario.sobrenome AS funcionarioSobrenome
        FROM tbFretamento fretamento
        LEFT JOIN tbVeiculo veiculo ON fretamento.idVeiculo = veiculo.idVeiculo
        LEFT JOIN tbCliente cliente ON fretamento.idCliente = cliente.idCliente
        LEFT JOIN tbFuncionario funcionario ON fretamento.idFuncionario = funcionario.idFuncionario
        $where
        ORDER BY fretamento.idFretamento DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt_BR">
<title>Lista de fretamentos</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
        <section class="container py-5">

            <div class="d-flex align-items-center">
                <button type="button" class="botao-voltar" onclick="window.location.href='../adminvt.php'">
                    <i class="bi bi-signpost-split-fill fs-4"></i>
                </button>
                <i class="bi bi-arrow-right-short"></i>
                <label><strong>Lista de fretamentos</strong></label>

                <a href="../fretamento.php" class="ms-auto text-decoration-none">
                    <button class="btn btn-dark" type="button">Adicionar fretamento</button>
                </a>
            </div>

            <form method="GET" class="d-flex mt-3" style="max-width: 400px;">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por destino, cliente ou veículo..." value="<?= htmlspecialchars($busca) ?>">
                <button class="btn btn-outline-secondary ms-2" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <div class="row mt-3">
                <!-- Coluna da esquerda -->
                <div class="col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Cliente</th>
                                        <th scope="col">Veículo</th>
                                        <th scope="col">Destino</th>
                                        <th scope="col">Saída</th>
                                        <th scope="col">Valor</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($resultado) === 0) { ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">Nenhum fretamento cadastrado.</td>
                                        </tr>
                                    <?php } else { ?>
                                        <?php while ($fretamento = mysqli_fetch_assoc($resultado)) { ?>
                                            <?php
                                            $nomeCliente = $fretamento['clienteTipo'] === 'PJ'
                                                ? $fretamento['clienteRazaoSocial']
                                                : trim($fretamento['clienteNome'] . ' ' . $fretamento['clienteSobrenome']);
                                            ?>
                                            <tr>
                                                <td><?= $fretamento['idFretamento'] ?></td>
                                                <td><?= htmlspecialchars($nomeCliente) ?></td>
                                                <td><?= htmlspecialchars($fretamento['veiculoNome']) ?></td>
                                                <td><?= htmlspecialchars($fretamento['destino']) ?></td>
                                                <td><?= $fretamento['dataHoraSaida'] ? date('d/m/Y H:i', strtotime($fretamento['dataHoraSaida'])) : '' ?></td>
                                                <td>R$ <?= number_format((float) $fretamento['preco'], 2, ',', '.') ?></td>
                                                <td><?= htmlspecialchars($fretamento['status']) ?></td>
                                                <td>
                                                    <a href="../editar/fretamento.php?id=<?= $fretamento['idFretamento'] ?>" class="text-decoration-none">
                                                        <button type="button" class="btn btn-warning"><i class="bi bi-pencil-square"></i></button>
                                                    </a>
                                                    <a href="fretamentos.php?excluir=<?= $fretamento['idFretamento'] ?>" class="text-decoration-none"
                                                        onclick="return confirm('Tem certeza que deseja excluir este fretamento?')">
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