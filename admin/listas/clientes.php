<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

// Se veio ?excluir=X na URL, apaga o cliente antes de montar a lista
$erroExclusao = "";
if (isset($_GET['excluir'])) {
    $id = mysqli_real_escape_string($conexao, $_GET['excluir']);
    try {
        mysqli_query($conexao, "DELETE FROM tbCliente WHERE idCliente = '$id'");
        header('Location: clientes.php');
        exit;
    } catch (mysqli_sql_exception $e) {
        $erroExclusao = "Não é possível excluir: este cliente está vinculado a um ou mais fretamentos/vendas. Altere o status para Inativo em vez de excluir.";
    }
}

// Se veio ?busca=X na URL, filtra por nome, sobrenome, razão social ou email
$busca = trim($_GET['busca'] ?? '');
$where = "";
if ($busca !== "") {
    $buscaEscapada = mysqli_real_escape_string($conexao, $busca);
    $where = "WHERE nome LIKE '%$buscaEscapada%' OR sobrenome LIKE '%$buscaEscapada%' OR razaoSocial LIKE '%$buscaEscapada%' OR email LIKE '%$buscaEscapada%'";
}

$sql = "select * from tbCliente $where";
$resultado = mysqli_query($conexao, $sql);
?>

<!DOCTYPE html>
<html lang="pt_BR">
<title>Lista de clientes</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <div class="d-flex align-items-center">
            <button type="button" class="botao-voltar" onclick="window.location.href='../adminvt.php'">
                <i class="bi bi-person-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label><strong>Lista de clientes</strong></label>

            <a href="../cadastro/cliente.php" class="ms-auto text-decoration-none">
                <button class="btn btn-dark" type="button">Adicionar cliente</button>
            </a>
        </div>

        <?php if ($erroExclusao !== ""): ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($erroExclusao) ?></div>
        <?php endif; ?>

        <form method="GET" class="d-flex mt-3" style="max-width: 400px;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome ou email..." value="<?= htmlspecialchars($busca) ?>">
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
                                    <th scope="col">Email</th>
                                    <th scope="col">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($resultado) === 0) { ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td>
                                    </tr>
                                <?php } else { ?>
                                    <?php while ($linha = mysqli_fetch_array($resultado)) { ?>
                                        <tr>
                                            <td><?= $linha['idCliente'] ?></td>
                                            <td><?= htmlspecialchars($linha['nome']) ?></td>
                                            <td><?= htmlspecialchars($linha['email']) ?></td>
                                            <td>
                                                <a href="../editar/cliente.php?id=<?= $linha['idCliente'] ?>" class="text-decoration-none">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pencil-square"></i></button>
                                                </a>
                                                <a href="clientes.php?excluir=<?= $linha['idCliente'] ?>" class="text-decoration-none"
                                                    onclick="return confirm('Tem certeza que deseja excluir este cliente?')">
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