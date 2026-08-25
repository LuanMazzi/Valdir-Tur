<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

$mensagem = "";
$id = $_POST['id'] ?? $_GET['id'] ?? '';

if (isset($_POST["salvar"])) {

    $id              = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
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
    $CNPJ            = mysqli_real_escape_string($conexao, $_POST['CNPJ'] ?? '');
    $status          = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // CPF, RG e CNPJ são únicos no banco: grava NULL em vez de '' quando vazio,
    // senão dois clientes sem CNPJ (por exemplo) colidem como se fossem duplicados
    $CPFSql  = $CPF === '' ? 'NULL' : "'$CPF'";
    $RGSql   = $RG === '' ? 'NULL' : "'$RG'";
    $CNPJSql = $CNPJ === '' ? 'NULL' : "'$CNPJ'";

    $sql = "UPDATE `tbcliente` SET
        `tipoCliente` = '$tipoCliente', `nome` = '$nome', `sobrenome` = '$sobrenome',
        `dataNascimento` = '$dataNascimento', `email` = '$email', `telefone` = '$telefone',
        `CPF` = $CPFSql, `RG` = $RGSql, `cidade` = '$cidade', `UF` = '$UF', `CEP` = '$CEP',
        `logradouro` = '$logradouro', `bairro` = '$bairro', `numero` = '$numero',
        `razaoSocial` = '$razaoSocial', `nomeFantasia` = '$nomeFantasia',
        `nomeResponsavel` = '$nomeResponsavel', `CNPJ` = $CNPJSql, `status` = '$status'
        WHERE `idCliente` = '$id'";

    try {
        mysqli_query($conexao, $sql);
        $mensagem = "<div class='alert alert-success'>Cliente alterado com sucesso.</div>";
    } catch (mysqli_sql_exception $e) {
        if (str_contains($e->getMessage(), 'Duplicate entry')) {
            $mensagem = "<div class='alert alert-danger'>Já existe outro cliente cadastrado com esse CPF, RG ou CNPJ.</div>";
        } else {
            $mensagem = "<div class='alert alert-danger'>Erro ao alterar: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Busca os dados atuais do cliente, pra preencher o formulário
// (Se acabou de salvar, isso já traz os dados atualizados de volta)
$idBusca = mysqli_real_escape_string($conexao, $id);
$sql = "SELECT * FROM tbcliente WHERE idCliente = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$cliente = mysqli_fetch_array($resultado);

$ufs = ['AC', 'AL', 'AM', 'AP', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MG', 'MS', 'MT', 'PA', 'PB', 'PE', 'PI', 'PR', 'RJ', 'RN', 'RO', 'RR', 'RS', 'SC', 'SE', 'SP', 'TO'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar cliente</title>
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
            <!-- <i class="bi bi-arrow-right-short"></i> -->
            <label class="pb-2"><strong>Alterar cliente</strong></label>

            <?= $mensagem ?>

            <!-- Campo escondido: carrega o ID do cliente junto no POST, senão o UPDATE não sabe quem alterar -->
            <input type="hidden" name="id" value="<?= $cliente['idCliente'] ?>">

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <div class="col-auto mb-3">
                                <strong> <label class="d-block py-2">Tipo de Cliente</label> </strong>
                                <select class="form-select" name="tipoCliente" id="selectTipo">
                                    <option value="PF" <?= $cliente['tipoCliente'] === 'PF' ? 'selected' : '' ?>>Pessoa Física (CPF)</option>
                                    <option value="PJ" <?= $cliente['tipoCliente'] === 'PJ' ? 'selected' : '' ?>>Pessoa Jurídica (CNPJ)</option>
                                </select>
                            </div>

                            <!-- Form Pessoa Física -->

                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>">

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome" value="<?= htmlspecialchars($cliente['sobrenome']) ?>">

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento" value="<?= htmlspecialchars($cliente['dataNascimento']) ?>">

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF" value="<?= htmlspecialchars($cliente['CPF']) ?>">

                            <label class="py-2">RG</label>
                            <input class="form-control" type="text" name="RG" value="<?= htmlspecialchars($cliente['RG']) ?>">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>">
                                </div>
                            </div>


                            <!-- Form Pessoa Jurídica -->
                            <div id="formPJ" style="display: <?= $cliente['tipoCliente'] === 'PJ' ? 'block' : 'none' ?>;">

                                <label class="py-2">Razão Social</label>
                                <input class="form-control" type="text" name="razaoSocial" value="<?= htmlspecialchars($cliente['razaoSocial']) ?>">

                                <label class="py-2">Nome Fantasia</label>
                                <input class="form-control" type="text" name="nomeFantasia" value="<?= htmlspecialchars($cliente['nomeFantasia']) ?>">

                                <label class="py-2">Nome do Responsável</label>
                                <input class="form-control" type="text" name="nomeResponsavel" value="<?= htmlspecialchars($cliente['nomeResponsavel']) ?>">

                                <label class="py-2">CNPJ</label>
                                <input class="form-control" type="text" name="CNPJ" value="<?= htmlspecialchars($cliente['CNPJ']) ?>">

                            </div>
                        </div>
                    </div>

                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="d-block py-2">Cidade</label>
                                    <input class="form-control" type="text" name="cidade" value="<?= htmlspecialchars($cliente['cidade']) ?>" required>
                                </div>
                                <div class="col-auto">
                                    <label class="d-block py-2">UF</label>
                                    <select class="form-select" name="UF" id="selectUF" style="width: 100px;" required>
                                        <option disabled value="">UF</option>
                                        <?php foreach ($ufs as $uf): ?>
                                            <option value="<?= $uf ?>" <?= $cliente['UF'] === $uf ? 'selected' : '' ?>><?= $uf ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP" value="<?= htmlspecialchars($cliente['CEP']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro" value="<?= htmlspecialchars($cliente['bairro']) ?>" required>
                                </div>
                                <div class="col-md-9">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro" value="<?= htmlspecialchars($cliente['logradouro']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero" value="<?= htmlspecialchars($cliente['numero']) ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Fim Coluna Esquerda -->

                <!-- Coluna da direita -->
                <div class="col-lg-4 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo" <?= $cliente['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= $cliente['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>
                        </div>
                    </div>
                </div> <!-- Fim Coluna Direita -->
            </div> <!-- Fim Row -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button style="color: white;" class="btn btn-primary" name="salvar" type="submit">Salvar</button>
            </div>
        </form>
    </section>

    <script>
        const selectTipo = document.getElementById('selectTipo');
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