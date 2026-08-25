<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

// Se veio ?excluir=X na URL, apaga o funcionário antes de montar a lista
$erroExclusao = "";
if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conexao, $_GET['excluir']);
    try {
        mysqli_query($conexao, "DELETE FROM tbfuncionario WHERE idFuncionario = '$id'");
        header('Location: funcionarios.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $erroExclusao = "Não é possível excluir: este funcionário está vinculado a um ou mais fretamentos/vendas. Altere o status para Inativo em vez de excluir.";
    }
}

// Se veio ?busca=X na URL, filtra por nome, sobrenome ou função
$busca = trim($_GET['busca'] ?? '');
$where = "";
if ($busca !== "") {
    $buscaEscapada = mysqli_real_escape_string($conexao, $busca);
    $where = "WHERE nome LIKE '%$buscaEscapada%' OR sobrenome LIKE '%$buscaEscapada%' OR funcao LIKE '%$buscaEscapada%'";
}

$sql = "select * from tbfuncionario $where";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt_BR">
<title>Lista de funcionários</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <div class="d-flex align-items-center">
            <button type="button" class="botao-voltar" onclick="window.location.href='../adminvt.php'">
                <i class="bi bi-person-badge fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label><strong>Lista de funcionários</strong></label>

            <a href="../cadastro/funcionario.php" class="ms-auto text-decoration-none">
                <button class="btn btn-dark" type="button">Adicionar funcionário</button>
            </a>
        </div>

        <?php if ($erroExclusao !== ""): ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($erroExclusao) ?></div>
        <?php endif; ?>

        <form method="GET" class="d-flex mt-3" style="max-width: 400px;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou função..." value="<?= htmlspecialchars($busca) ?>">
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
                                    <th scope="col">Nome</th>
                                    <th scope="col">Função</th>
                                    <th scope="col">Telefone</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado) === 0) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">Nenhum funcionário cadastrado.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($linha = mysqli_fetch_array($resultado)) { ?>
                                        <tr>
                                            <td><?= $linha['idFuncionario'] ?></td>
                                            <td><?= htmlspecialchars($linha['nome'] . ' ' . $linha['sobrenome']) ?></td>
                                            <td><?= htmlspecialchars($linha['funcao']) ?></td>
                                            <td><?= htmlspecialchars($linha['telefone']) ?></td>
                                            <td><?= htmlspecialchars($linha['status']) ?></td>
                                            <td>
                                                <a href="../editar/funcionario.php?id=<?= $linha['idFuncionario'] ?>" class="text-decoration-none">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pencil-square"></i></button>
                                                </a>
                                                <a href="funcionarios.php?excluir=<?= $linha['idFuncionario'] ?>" class="text-decoration-none"
                                                    onclick="return confirm('Tem certeza que deseja excluir este funcionário?')">
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