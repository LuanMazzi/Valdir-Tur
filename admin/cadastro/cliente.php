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
    $mensagem = "<div class='alert alert-success'>Cliente cadastrado com sucesso!</div>";
}

                            if (isset($_POST["salvar"])) {

                                // 1. Capturar e escapar os dados vindos do formulário
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

                                // 2. CPF, RG e CNPJ são únicos no banco: grava NULL em vez de '' quando vazio,
                                // senão dois clientes sem CNPJ (por exemplo) colidem como se fossem duplicados
                                $CPFSql  = $CPF === '' ? 'NULL' : "'$CPF'";
                                $RGSql   = $RG === '' ? 'NULL' : "'$RG'";
                                $CNPJSql = $CNPJ === '' ? 'NULL' : "'$CNPJ'";

                                // 3. Preparar a SQL
                                $sql = "INSERT INTO `tbcliente` (
                                    `tipoCliente`, `nome`, `sobrenome`, `dataNascimento`, `email`, `telefone`,
                                    `CPF`, `RG`, `cidade`, `UF`, `CEP`, `logradouro`, `bairro`, `numero`,
                                    `razaoSocial`, `nomeFantasia`, `nomeResponsavel`, `CNPJ`, `status`
                                ) VALUES (
                                    '$tipoCliente', '$nome', '$sobrenome', '$dataNascimento', '$email', '$telefone',
                                    $CPFSql, $RGSql, '$cidade', '$UF', '$CEP', '$logradouro', '$bairro', '$numero',
                                    '$razaoSocial', '$nomeFantasia', '$nomeResponsavel', $CNPJSql, '$status'
                                )";

                                // 4. Executar e verificar erro
                                try {
                                    mysqli_query($conexao, $sql);
                                    // Redireciona pra evitar reenvio do formulário ao atualizar a página (F5)
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
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <div class="col-auto mb-3">
                                <strong> <label class="d-block py-2">Tipo de Cliente</label> </strong>
                                <select class="form-select" name="tipoCliente" id="selectTipo">
                                    <option value="PF" selected>Pessoa Física (CPF)</option>
                                    <option value="PJ">Pessoa Jurídica (CNPJ)</option>
                                </select>
                            </div>

                            <!-- Form Pessoa Física -->

                            <label class="py-2">Nome</label>
                            <input class="form-control" type="text" name="nome" placeholder="João">

                            <label class="py-2">Sobrenome</label>
                            <input class="form-control" type="text" name="sobrenome" placeholder="Souza">

                            <label class="py-2">Data de nascimento</label>
                            <input type="date" class="form-control" id="dataNascimento" name="dataNascimento">

                            <label class="py-2">CPF</label>
                            <input class="form-control" type="text" name="CPF" placeholder="CPF">

                            <label class="py-2">RG</label>
                            <input class="form-control" type="text" name="RG" placeholder="RG">

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Telefone</label>
                                    <input class="form-control" type="text" name="telefone"
                                        placeholder="Número de Telefone">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">E-Mail</label>
                                    <input class="form-control" type="email" name="email" placeholder="E-mail">
                                </div>
                            </div>


                            <!-- Form Pessoa Jurídica -->
                            <div id="formPJ" style="display: none;">

                                <label class="py-2">Razão Social</label>
                                <input class="form-control" type="text" name="razaoSocial" placeholder="Empresa LTDA">

                                <label class="py-2">Nome Fantasia</label>
                                <input class="form-control" type="text" name="nomeFantasia" placeholder="Nome Fantasia">

                                <label class="py-2">Nome do Responsável</label>
                                <input class="form-control" type="text" name="nomeResponsavel"
                                    placeholder="Nome do Responsável">

                                <label class="py-2">CNPJ</label>
                                <input class="form-control" type="text" name="CNPJ" placeholder="00.000.000/0000-00">

                            </div>
                        </div>
                    </div>

                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Endereço</label></strong> <br>
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
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
                                <div class="col-md-3">
                                    <label class="d-block py-2">CEP</label>
                                    <input class="form-control" type="text" name="CEP" placeholder="CEP" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Bairro</label>
                                    <input class="form-control" type="text" name="bairro" placeholder="Centro" required>
                                </div>
                                <div class="col-md-9">
                                    <label class="d-block py-2">Logradouro</label>
                                    <input class="form-control" type="text" name="logradouro"
                                        placeholder="Avenida Manoel" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="d-block py-2">Número</label>
                                    <input class="form-control" type="text" name="numero" placeholder="979" required>
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
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
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