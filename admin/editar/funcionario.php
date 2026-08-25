<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

$erro = "";
$sucesso = false;
$id = $_POST['id'] ?? $_GET['id'] ?? '';

// Se o formulário foi enviado, faz o UPDATE
if (isset($_POST['salvar'])) {

    $id             = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $nome           = mysqli_real_escape_string($conexao, $_POST['nome'] ?? '');
    $sobrenome      = mysqli_real_escape_string($conexao, $_POST['sobrenome'] ?? '');
    $CPF            = mysqli_real_escape_string($conexao, $_POST['CPF'] ?? '');
    $RG             = mysqli_real_escape_string($conexao, $_POST['RG'] ?? '');
    $dataNascimento = mysqli_real_escape_string($conexao, $_POST['dataNascimento'] ?? '');
    $telefone       = mysqli_real_escape_string($conexao, $_POST['telefone'] ?? '');
    $email          = mysqli_real_escape_string($conexao, $_POST['email'] ?? '');
    $funcao         = mysqli_real_escape_string($conexao, $_POST['funcao'] ?? '');
    $cidade         = mysqli_real_escape_string($conexao, $_POST['cidade'] ?? '');
    $senhaPost      = $_POST['senha'] ?? '';
    $UF             = mysqli_real_escape_string($conexao, $_POST['UF'] ?? '');
    $CEP            = mysqli_real_escape_string($conexao, $_POST['CEP'] ?? '');
    $logradouro     = mysqli_real_escape_string($conexao, $_POST['logradouro'] ?? '');
    $bairro         = mysqli_real_escape_string($conexao, $_POST['bairro'] ?? '');
    $numero         = mysqli_real_escape_string($conexao, $_POST['numero'] ?? '');
    $status         = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // Senha é opcional aqui: só grava uma nova coluna `senha` no SQL se o campo vier preenchido
    $senhaSql = "";
    if ($senhaPost !== '') {
        $senhaHash = mysqli_real_escape_string($conexao, password_hash($senhaPost, PASSWORD_DEFAULT));
        $senhaSql = "`senha` = '$senhaHash',";
    }

    $sql = "UPDATE `tbfuncionario` SET
        `nome` = '$nome',
        `sobrenome` = '$sobrenome',
        `CPF` = '$CPF',
        `RG` = '$RG',
        `dataNascimento` = '$dataNascimento',
        `telefone` = '$telefone',
        `email` = '$email',
        `funcao` = '$funcao',
        $senhaSql
        `cidade` = '$cidade',
        `UF` = '$UF',
        `CEP` = '$CEP',
        `logradouro` = '$logradouro',
        `bairro` = '$bairro',
        `numero` = '$numero',
        `status` = '$status'
        WHERE `idFuncionario` = '$id'";

    try {
        mysqli_query($conexao, $sql);
        $sucesso = true; // sucesso, segue e recarrega os dados atualizados abaixo
    } catch (mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $erro = "Já existe outro funcionário cadastrado com esse CPF.";
        } else {
            $erro = "Erro ao alterar: " . $e->getMessage();
        }
    }
}

// Busca os dados atuais do funcionário, pra preencher o formulário
$idBusca = mysqli_real_escape_string($conexao, $id);
$sql = "SELECT * FROM tbfuncionario WHERE idFuncionario = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$funcionario = mysqli_fetch_array($resultado);

$ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
$funcoes = ['Motorista', 'Agente de Viagens', 'Mecânico', 'Vendedor'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar funcionário</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <form method="POST">
            <button type="button" class="botao-voltar" onclick="window.location.href='../listas/funcionarios.php'">
                <i class="bi bi-person-badge fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Alterar funcionário</strong></label>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">Edição feita com sucesso!</div>
            <?php endif; ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Campo escondido: carrega o ID do funcionário junto no POST -->
            <input type="hidden" name="id" value="<?= $funcionario['idFuncionario'] ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12"> <!-- Coluna da esquerda -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome"
                                value="<?= htmlspecialchars($funcionario['nome']) ?>" required>

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome"
                                value="<?= htmlspecialchars($funcionario['sobrenome']) ?>" required>

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento"
                                value="<?= htmlspecialchars($funcionario['dataNascimento']) ?>">

                            <div class="col-auto">
                                <label class="d-block py-2">Função</label>
                                <select class="form-select" name="funcao" id="selectFuncao">
                                    <option disabled>Selecione</option>
                                    <?php foreach ($funcoes as $funcao): ?>
                                        <option value="<?= $funcao ?>" <?= $funcionario['funcao'] === $funcao ? 'selected' : '' ?>><?= $funcao ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF"
                                value="<?= htmlspecialchars($funcionario['CPF']) ?>" required>

                            <label class="py-2">RG</label>
                            <input class="form-control" type="text" name="RG"
                                value="<?= htmlspecialchars($funcionario['RG']) ?>" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone"
                                        value="<?= htmlspecialchars($funcionario['telefone']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email"
                                        value="<?= htmlspecialchars($funcionario['email']) ?>" required>
                                </div>
                            </div>

                            <label class="py-2">Senha de acesso</label>
                            <input class="form-control" type="password" name="senha" placeholder="Deixe em branco para manter a senha atual" minlength="6">

                        </div>
                    </div>

                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">

                                <div class="col-auto">
                                    <label class="d-block py-2">Cidade</label>
                                    <input class="form-control" type="text" name="cidade"
                                        value="<?= htmlspecialchars($funcionario['cidade']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">UF</label>
                                    <select class="form-select" name="UF" id="selectUF" style="width: 100px;" required>
                                        <option disabled value="">UF</option>
                                        <?php foreach ($ufs as $uf): ?>
                                            <option value="<?= $uf ?>" <?= $funcionario['UF'] === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP"
                                        value="<?= htmlspecialchars($funcionario['CEP']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro"
                                        value="<?= htmlspecialchars($funcionario['logradouro']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro"
                                        value="<?= htmlspecialchars($funcionario['bairro']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero"
                                        value="<?= htmlspecialchars($funcionario['numero']) ?>" required>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12"> <!-- Coluna da direita -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo" <?= $funcionario['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= $funcionario['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button style="background-color: #0b5ed7; color: white;" class="btn me-md-2" name="salvar"
                    type="submit">Salvar</button>
            </div>
        </form>
    </section>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>