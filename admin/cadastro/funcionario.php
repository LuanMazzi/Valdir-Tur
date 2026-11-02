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
require_once(__DIR__ . '/../../includes/validar-cpf.php');

// Devolve o valor digitado (mantém os dados no formulário após um erro)
function valor($campo)
{
    return htmlspecialchars($_POST[$campo] ?? '');
}

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Funcionário cadastrado com sucesso!</div>";
}

// Salvar: recebe os dados do formulário e grava no banco
if (isset($_POST['salvar'])) {

    $nome           = mysqli_real_escape_string($conexao, $_POST['nome'] ?? '');
    $sobrenome      = mysqli_real_escape_string($conexao, $_POST['sobrenome'] ?? '');
    $CPF            = mysqli_real_escape_string($conexao, $_POST['CPF'] ?? '');
    $RG             = mysqli_real_escape_string($conexao, $_POST['RG'] ?? '');
    $dataNascimento = mysqli_real_escape_string($conexao, $_POST['dataNascimento'] ?? '');
    $telefone       = mysqli_real_escape_string($conexao, $_POST['telefone'] ?? '');
    $email          = mysqli_real_escape_string($conexao, $_POST['email'] ?? '');
    $funcao         = mysqli_real_escape_string($conexao, $_POST['funcao'] ?? '');
    $senha          = mysqli_real_escape_string($conexao, password_hash($_POST['senha'] ?? '', PASSWORD_DEFAULT));
    $cidade         = mysqli_real_escape_string($conexao, $_POST['cidade'] ?? '');
    $UF             = mysqli_real_escape_string($conexao, $_POST['UF'] ?? '');
    $CEP            = mysqli_real_escape_string($conexao, $_POST['CEP'] ?? '');
    $logradouro     = mysqli_real_escape_string($conexao, $_POST['logradouro'] ?? '');
    $bairro         = mysqli_real_escape_string($conexao, $_POST['bairro'] ?? '');
    $numero         = mysqli_real_escape_string($conexao, $_POST['numero'] ?? '');
    $status         = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // Comando SQL
    $sql = "INSERT INTO `tbfuncionario` (
        `nome`, `sobrenome`, `CPF`, `RG`, `dataNascimento`, `telefone`,
        `email`, `funcao`, `senha`, `cidade`, `UF`, `CEP`, `logradouro`,
        `bairro`, `numero`, `status`
    ) VALUES (
        '$nome', '$sobrenome', '$CPF', '$RG', '$dataNascimento', '$telefone',
        '$email', '$funcao', '$senha', '$cidade', '$UF', '$CEP', '$logradouro',
        '$bairro', '$numero', '$status'
    )";

    // Validações: CPF e confirmação de senha
    if (!validarCPF($_POST['CPF'] ?? '')) {
        $mensagem = "<div class='alert alert-danger'>CPF inválido.</div>";
    } elseif (($_POST['senha'] ?? '') !== ($_POST['confirmarSenha'] ?? '')) {
        $mensagem = "<div class='alert alert-danger'>As senhas não conferem.</div>";
    } else {
        try {
            mysqli_query($conexao, $sql);

            header('Location: funcionario.php?sucesso=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $mensagem = "<div class='alert alert-danger'>Já existe um funcionário cadastrado com esse CPF.</div>";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar funcionário: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

$ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
$funcoes = ['Motorista', 'Agente de Viagens', 'Mecânico', 'Vendedor'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Cadastro de funcionário</title>
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
            <label class="pb-2"><strong>Adicionar funcionário</strong></label>

            <?= $mensagem ?>

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do funcionário -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome" placeholder="João"
                                value="<?= valor('nome') ?>" required>

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome" placeholder="Souza"
                                value="<?= valor('sobrenome') ?>" required>

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento"
                                value="<?= valor('dataNascimento') ?>">

                            <div class="col-auto">
                                <label class="d-block py-2">Função</label>
                                <select class="form-select" name="funcao" id="selectFuncao">
                                    <option <?= empty($_POST['funcao']) ? 'selected' : '' ?> disabled>Selecione</option>
                                    <?php foreach ($funcoes as $funcao): ?>
                                        <option value="<?= $funcao ?>" <?= ($_POST['funcao'] ?? '') === $funcao ? 'selected' : '' ?>><?= $funcao ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF" placeholder="CPF" maxlength="11"
                                value="<?= valor('CPF') ?>" required>
                            <small class="text-muted">Escrever sem pontos e traços</small>

                            <?php $semRG = isset($_POST['semRG']); ?>
                            <label class="d-block py-2">RG</label>
                            <input class="form-control" type="text" name="RG" id="campoRG" placeholder="RG"
                                value="<?= valor('RG') ?>" <?= $semRG ? 'disabled' : 'required' ?>>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="semRG" id="semRG"
                                    <?= $semRG ? 'checked' : '' ?>
                                    onchange="const rg = document.getElementById('campoRG'); rg.disabled = this.checked; rg.required = !this.checked; if (this.checked) rg.value = '';">
                                <label class="form-check-label" for="semRG">Não usa RG</label>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone"
                                        placeholder="Número de Telefone" value="<?= valor('telefone') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email" placeholder="E-mail"
                                        value="<?= valor('email') ?>" required>
                                </div>
                            </div>

                            <label class="py-2">Senha de acesso</label>
                            <input class="form-control" type="password" name="senha" placeholder="Senha para login" minlength="6">

                            <label class="py-2">Confirmar senha</label>
                            <input class="form-control" type="password" name="confirmarSenha" placeholder="Repita a senha" minlength="6">

                        </div>
                    </div>

                    <!-- Card: Endereço -->
                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">

                                <div class="col-auto">
                                    <label class="d-block py-2">Cidade</label>
                                    <input class="form-control" type="text" name="cidade" placeholder="Cidade"
                                        value="<?= valor('cidade') ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">UF</label>
                                    <select class="form-select" name="UF" id="selectUF" style="width: 100px;" required>
                                        <option <?= empty($_POST['UF']) ? 'selected' : '' ?> disabled value="">UF</option>
                                        <?php foreach ($ufs as $uf): ?>
                                            <option value="<?= $uf ?>" <?= ($_POST['UF'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP" placeholder="CEP"
                                        value="<?= valor('CEP') ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro"
                                        placeholder="Avenida Manoel" value="<?= valor('logradouro') ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro" placeholder="Centro"
                                        value="<?= valor('bairro') ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero" placeholder="979"
                                        value="<?= valor('numero') ?>" required>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Coluna da direita -->
                <div class="col-lg-4 col-md-12">
                    <!-- Card: Status -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo" <?= ($_POST['status'] ?? '') === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= ($_POST['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
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
