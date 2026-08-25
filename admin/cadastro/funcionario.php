<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Funcionário cadastrado com sucesso!</div>";
}

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

    $sql = "INSERT INTO `tbfuncionario` (
        `nome`, `sobrenome`, `CPF`, `RG`, `dataNascimento`, `telefone`,
        `email`, `funcao`, `senha`, `cidade`, `UF`, `CEP`, `logradouro`,
        `bairro`, `numero`, `status`
    ) VALUES (
        '$nome', '$sobrenome', '$CPF', '$RG', '$dataNascimento', '$telefone',
        '$email', '$funcao', '$senha', '$cidade', '$UF', '$CEP', '$logradouro',
        '$bairro', '$numero', '$status'
    )";

    try {
        mysqli_query($conexao, $sql);
        // Redireciona pra evitar reenvio do formulário ao atualizar a página (F5)
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
                <div class="col-lg-8 col-md-12"> <!-- Coluna da esquerda -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome" placeholder="João" required>

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome" placeholder="Souza" required>

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento">

                            <div class="col-auto">
                                <label class="d-block py-2">Função</label>
                                <select class="form-select" name="funcao" id="selectFuncao">
                                    <option selected disabled>Selecione</option>
                                    <option value="Motorista">Motorista</option>
                                    <option value="Agente de Viagens">Agente de Viagens</option>
                                    <option value="Mecânico">Mecânico</option>
                                    <option value="Vendedor">Vendedor</option>
                                </select>
                            </div>

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF" placeholder="CPF" required>

                            <label class="py-2">RG</label>
                            <input class="form-control" type="text" name="RG" placeholder="RG" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone"
                                        placeholder="Número de Telefone" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email" placeholder="E-mail" required>
                                </div>
                            </div>

                            <label class="py-2">Senha de acesso</label>
                            <input class="form-control" type="password" name="senha" placeholder="Senha para login" minlength="6">

                        </div>
                    </div>

                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">

                                <div class="col-auto">
                                    <label class="d-block py-2">Cidade</label>
                                    <input class="form-control" type="text" name="cidade" placeholder="Cidade" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">UF</label>
                                    <select class="form-select" name="UF" id="selectUF" style="width: 100px;" required>
                                        <option selected disabled value="">UF</option>
                                        <option value="AC">AC</option>
                                        <option value="AL">AL</option>
                                        <option value="AM">AM</option>
                                        <option value="AP">AP</option>
                                        <option value="BA">BA</option>
                                        <option value="CE">CE</option>
                                        <option value="DF">DF</option>
                                        <option value="ES">ES</option>
                                        <option value="GO">GO</option>
                                        <option value="MA">MA</option>
                                        <option value="MG">MG</option>
                                        <option value="MS">MS</option>
                                        <option value="MT">MT</option>
                                        <option value="PA">PA</option>
                                        <option value="PB">PB</option>
                                        <option value="PE">PE</option>
                                        <option value="PI">PI</option>
                                        <option value="PR">PR</option>
                                        <option value="RJ">RJ</option>
                                        <option value="RN">RN</option>
                                        <option value="RO">RO</option>
                                        <option value="RR">RR</option>
                                        <option value="RS">RS</option>
                                        <option value="SC">SC</option>
                                        <option value="SE">SE</option>
                                        <option value="SP">SP</option>
                                        <option value="TO">TO</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP" placeholder="CEP" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro"
                                        placeholder="Avenida Manoel" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro" placeholder="Centro" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero" placeholder="979" required>
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
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
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