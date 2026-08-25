<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

// Se veio ?excluir=X na URL, apaga o pacote antes de montar a lista
$erroExclusao = "";
if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conexao, $_GET['excluir']);
    try {
        mysqli_query($conexao, "DELETE FROM tbpacote WHERE idPacote = '$id'");
        header('Location: pacotes.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $erroExclusao = "Não é possível excluir: este pacote está vinculado a uma ou mais vendas. Altere o status para Inativo em vez de excluir.";
    }
}

// Se veio ?busca=X na URL, filtra por nome do pacote ou destino
$busca = trim($_GET['busca'] ?? '');
$where = "";
if ($busca !== "") {
    $buscaEscapada = mysqli_real_escape_string($conexao, $busca);
    $where = "WHERE nomePacote LIKE '%$buscaEscapada%' OR destino LIKE '%$buscaEscapada%'";
}

$sql = "select * from tbpacote $where";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt_BR">
<title>Lista de pacotes</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <div class="d-flex align-items-center">
            <button type="button" class="botao-voltar" onclick="window.location.href='../adminvt.php'">
                <i class="bi bi-luggage-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label><strong>Lista de pacotes</strong></label>

            <a href="../cadastro/pacote.php" class="ms-auto text-decoration-none">
                <button class="btn btn-dark" type="button">Adicionar pacote</button>
            </a>
        </div>

        <?php if ($erroExclusao !== ""): ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($erroExclusao) ?></div>
        <?php endif; ?>

        <form method="GET" class="d-flex mt-3" style="max-width: 400px;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou destino..." value="<?= htmlspecialchars($busca) ?>">
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
                                    <th scope="col">Nome do Pacote</th>
                                    <th scope="col">Destino</th>
                                    <th scope="col">Saída</th>
                                    <th scope="col">Preço</th>
                                    <th scope="col">Vagas</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Parceria</th>
                                    <th scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado) === 0) { ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">Nenhum pacote cadastrado.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($linha = mysqli_fetch_array($resultado)) { ?>
                                        <tr>
                                            <td><?= $linha['idPacote'] ?></td>
                                            <td><?= htmlspecialchars($linha['nomePacote']) ?></td>
                                            <td><?= htmlspecialchars($linha['destino']) ?></td>
                                            <td><?= $linha['dataHoraSaida'] ? date('d/m/Y H:i', strtotime($linha['dataHoraSaida'])) : '' ?></td>
                                            <td>R$ <?= number_format((float) $linha['preco'], 2, ',', '.') ?></td>
                                            <td><?= $linha['vagasDisponiveis'] ?></td>
                                            <td><?= htmlspecialchars($linha['status']) ?></td>
                                            <td><?= htmlspecialchars($linha['pacoteParceiro']) ?></td>
                                            <td>
                                                <a href="../editar/pacote.php?id=<?= $linha['idPacote'] ?>" class="text-decoration-none">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pencil-square"></i></button>
                                                </a>
                                                <a href="pacotes.php?excluir=<?= $linha['idPacote'] ?>" class="text-decoration-none"
                                                    onclick="return confirm('Tem certeza que deseja excluir este pacote?')">
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