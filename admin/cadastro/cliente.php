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
require_once(__DIR__ . '/../../includes/validar-cnpj.php');

// Devolve o valor digitado (mantém os dados no formulário após um erro)
function valor($campo)
{
    return htmlspecialchars($_POST[$campo] ?? '');
}

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Cliente cadastrado com sucesso!</div>";
}

// Salvar: recebe os dados do formulário e grava no banco
if (isset($_POST["salvar"])) {

    $tipoCliente     = mysqli_real_escape_string($conexao, $_POST['tipoCliente'] ?? '');
    $nome            = mysqli_real_escape_string($conexao, $_POST['nome'] ?? '');
    $sobrenome       = mysqli_real_escape_string($conexao, $_POST['sobrenome'] ?? '');
    $dataNascimento  = mysqli_real_escape_string($conexao, $_POST['dataNascimento'] ?? '');
    $email           = mysqli_real_escape_string($conexao, $_POST['email'] ?? '');
    $telefone        = mysqli_real_escape_string($conexao, $_POST['telefone'] ?? '');
    $CPF             = mysqli_real_escape_string($conexao, $_POST['CPF'] ?? '');
    $RG              = mysqli_real_escape_string($conexao, $_POST['RG'] ?? '');
    $cidade          = mysqli_real_escape_string($conexao, $_POST['cidade'] ?? '');
    $UF              = mysqli_real_escape_string($conexao, $_POST['UF'] ?? '');
    $CEP             = mysqli_real_escape_string($conexao, $_POST['CEP'] ?? '');
    $logradouro      = mysqli_real_escape_string($conexao, $_POST['logradouro'] ?? '');
    $bairro          = mysqli_real_escape_string($conexao, $_POST['bairro'] ?? '');
    $numero          = mysqli_real_escape_string($conexao, $_POST['numero'] ?? '');
    $razaoSocial     = mysqli_real_escape_string($conexao, $_POST['razaoSocial'] ?? '');
    $nomeFantasia    = mysqli_real_escape_string($conexao, $_POST['nomeFantasia'] ?? '');
    $nomeResponsavel = mysqli_real_escape_string($conexao, $_POST['nomeResponsavel'] ?? '');
    $CNPJ            = mysqli_real_escape_string($conexao, strtoupper($_POST['CNPJ'] ?? ''));
    $status          = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    $CPFSql  = $CPF === '' ? 'NULL' : "'$CPF'";
    $RGSql   = $RG === '' ? 'NULL' : "'$RG'";
    $CNPJSql = $CNPJ === '' ? 'NULL' : "'$CNPJ'";

    // Comando SQL
    $sql = "INSERT INTO `tbcliente` (
        `tipoCliente`, `nome`, `sobrenome`, `dataNascimento`, `email`, `telefone`,
        `CPF`, `RG`, `cidade`, `UF`, `CEP`, `logradouro`, `bairro`, `numero`,
        `razaoSocial`, `nomeFantasia`, `nomeResponsavel`, `CNPJ`, `status`
    ) VALUES (
        '$tipoCliente', '$nome', '$sobrenome', '$dataNascimento', '$email', '$telefone',
        $CPFSql, $RGSql, '$cidade', '$UF', '$CEP', '$logradouro', '$bairro', '$numero',
        '$razaoSocial', '$nomeFantasia', '$nomeResponsavel', $CNPJSql, '$status'
    )";

    // CPF e CNPJ são opcionais: só valida se preenchidos
    if ($CPF !== '' && !validarCPF($_POST['CPF'])) {
        $mensagem = "<div class='alert alert-danger'>CPF inválido.</div>";
    } elseif ($CNPJ !== '' && !validarCNPJ($CNPJ)) {
        $mensagem = "<div class='alert alert-danger'>CNPJ inválido.</div>";
    } else {
        try {
            mysqli_query($conexao, $sql);

            header('Location: cliente.php?sucesso=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $mensagem = "<div class='alert alert-danger'>Já existe um cliente cadastrado com esse CPF, RG ou CNPJ.</div>";
            } else {
                $mensagem = "<div class='alert alert-danger'>Erro ao inserir: " . htmlspecialchars($e->getMessage()) . "</div>";
            }
        }
    }
}

$ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
$tipoSelecionado = $_POST['tipoCliente'] ?? 'PF';
?>

<!DOCTYPE html>
<html lang="pt-BR">
 <title>Cadastro de cliente</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST">
            <button type="button" class="botao-voltar" onclick="window.location.href='../listas/clientes.php'">
                <i class="bi bi-person-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Adicionar cliente</strong></label>

            <?= $mensagem ?>

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do cliente -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <div class="col-auto mb-3">
                                <strong> <label class="d-block py-2">Tipo de Cliente</label> </strong>
                                <select class="form-select" name="tipoCliente" id="selectTipo">
                                    <option value="PF" <?= $tipoSelecionado === 'PF' ? 'selected' : '' ?>>Pessoa Física (CPF)</option>
                                    <option value="PJ" <?= $tipoSelecionado === 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica (CNPJ)</option>
                                </select>
                            </div>

                            <!-- Form Pessoa Física -->

                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome" placeholder="João" value="<?= valor('nome') ?>">

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome" placeholder="Souza" value="<?= valor('sobrenome') ?>">

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento" value="<?= valor('dataNascimento') ?>">

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF" placeholder="CPF" maxlength="11" value="<?= valor('CPF') ?>">
                            <small class="text-muted">Escrever sem pontos e traços</small>

                            <?php $semRG = isset($_POST['semRG']); ?>
                            <label class="d-block py-2">RG</label>
                            <input class="form-control" type="text" name="RG" id="campoRG" placeholder="RG"
                                value="<?= valor('RG') ?>" <?= $semRG ? 'disabled' : '' ?>>
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" name="semRG" id="semRG"
                                    <?= $semRG ? 'checked' : '' ?>
                                    onchange="const rg = document.getElementById('campoRG'); rg.disabled = this.checked; if (this.checked) rg.value = '';">
                                <label class="form-check-label" for="semRG">Não usa RG</label>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone"
                                        placeholder="Número de Telefone" value="<?= valor('telefone') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email" placeholder="E-mail" value="<?= valor('email') ?>">
                                </div>
                            </div>


                            <!-- Form Pessoa Jurídica -->
                            <div id="formPJ" style="display: <?= $tipoSelecionado === 'PJ' ? 'block' : 'none' ?>;">

                                <label class="py-2">Razão Social</label>
                                <input class="form-control" type="text" name="razaoSocial" placeholder="Empresa LTDA" value="<?= valor('razaoSocial') ?>">

                                <label class="py-2">Nome Fantasia</label>
                                <input class="form-control" type="text" name="nomeFantasia" placeholder="Nome Fantasia" value="<?= valor('nomeFantasia') ?>">

                                <label class="py-2">Nome do Responsável</label>
                                <input class="form-control" type="text" name="nomeResponsavel"
                                    placeholder="Nome do Responsável" value="<?= valor('nomeResponsavel') ?>">

                                <label class="py-2">CNPJ</label>
                                <input class="form-control" type="text" name="CNPJ" placeholder="CNPJ" maxlength="14" value="<?= valor('CNPJ') ?>">
                                <small class="text-muted">Escrever sem pontos, barras e traços</small>

                            </div>
                        </div>
                    </div>

                    <!-- Card: Endereço -->
                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="d-block py-2">Cidade</label>
                                    <input class="form-control" type="text" name="cidade" placeholder="Cidade" value="<?= valor('cidade') ?>" required>
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
                                <div class="col-md-3">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP" placeholder="CEP" value="<?= valor('CEP') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro" placeholder="Centro" value="<?= valor('bairro') ?>" required>
                                </div>
                                <div class="col-md-9">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro"
                                        placeholder="Avenida Manoel" value="<?= valor('logradouro') ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero" placeholder="979" value="<?= valor('numero') ?>" required>
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
                <button style="color: white;" class="btn btn-primary" name="salvar" type="submit">Salvar</button>
            </div>
        </form>
    </section>

    <script>
        // Mostra os campos de Pessoa Jurídica quando o tipo é PJ
        const selectTipo = document.getElementById('selectTipo');
        const formPF = document.getElementById('formPF');
        const formPJ = document.getElementById('formPJ');

        selectTipo.addEventListener('change', function() {
            if (this.value === 'PJ') {
                formPJ.style.display = 'block';

            } else {
                formPJ.style.display = 'none';
            }
        });
    </script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>
