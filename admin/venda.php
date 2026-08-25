<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Venda cadastrada com sucesso!</div>";
}

if (isset($_POST['salvar'])) {

    $dataVenda        = mysqli_real_escape_string($conexao, $_POST['dataVenda'] ?? '');
    $idFuncionario     = mysqli_real_escape_string($conexao, $_POST['idFuncionario'] ?? '');
    $idCliente         = mysqli_real_escape_string($conexao, $_POST['idCliente'] ?? '');
    $idPacote          = mysqli_real_escape_string($conexao, $_POST['idPacote'] ?? '');
    $idFretamento      = mysqli_real_escape_string($conexao, $_POST['idFretamento'] ?? '');
    $formaRecebimento  = mysqli_real_escape_string($conexao, $_POST['formaRecebimento'] ?? '');
    $dataRecebimento   = mysqli_real_escape_string($conexao, $_POST['dataRecebimento'] ?? '');
    $valorRecebido     = mysqli_real_escape_string($conexao, $_POST['valorRecebido'] ?? '0');
    $qtdParcelas       = mysqli_real_escape_string($conexao, ($_POST['qtdParcelas'] ?? '') !== '' ? $_POST['qtdParcelas'] : '1');
    $juros             = mysqli_real_escape_string($conexao, ($_POST['juros'] ?? '') !== '' ? $_POST['juros'] : '0');
    $status            = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // idPacote e idFretamento são opcionais (e mutuamente exclusivos), então manda NULL quando não vier valor
    $idPacoteSql = $idPacote === '' ? 'NULL' : "'$idPacote'";
    $idFretamentoSql = $idFretamento === '' ? 'NULL' : "'$idFretamento'";

    $sql = "INSERT INTO `tbvenda` (
        `dataVenda`, `idFuncionario`, `idCliente`, `idPacote`, `idFretamento`, `formaRecebimento`,
        `dataRecebimento`, `valorRecebido`, `qtdParcelas`, `juros`, `status`
    ) VALUES (
        '$dataVenda', '$idFuncionario', '$idCliente', $idPacoteSql, $idFretamentoSql, '$formaRecebimento',
        '$dataRecebimento', '$valorRecebido', '$qtdParcelas', '$juros', '$status'
    )";

    if (mysqli_query($conexao, $sql)) {
        // Redireciona pra evitar reenvio do formulário ao atualizar a página (F5)
        header('Location: venda.php?sucesso=1');
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . mysqli_error($conexao) . "</div>";
    }
}

// Só busca cliente/funcionário/pacote com status Ativo, pra alimentar os selects
$resultadoClientes = mysqli_query($conexao, "SELECT * FROM tbCliente WHERE status = 'Ativo' ORDER BY nome, razaoSocial");
$resultadoFuncionarios = mysqli_query($conexao, "SELECT * FROM tbFuncionario WHERE status = 'Ativo' ORDER BY nome");
$resultadoPacotes = mysqli_query($conexao, "SELECT * FROM tbpacote WHERE status = 'Ativo' ORDER BY nomePacote");
// Só fretamentos Aprovados fazem sentido virar venda
$resultadoFretamentos = mysqli_query($conexao, "SELECT * FROM tbFretamento WHERE status = 'Aprovado' ORDER BY dataHoraSaida DESC");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Cadastro de venda</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST">
            <button class="botao-voltar" type="button" onclick="window.location.href='listas/vendas.php'">
                <i class="bi bi-cash-coin fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Adicionar venda</strong></label>

            <?= $mensagem ?>

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Cliente</label></strong>
                            <select class="form-select" name="idCliente" required>
                                <option value="" selected disabled>Selecione um cliente ativo</option>
                                <?php while ($cliente = mysqli_fetch_assoc($resultadoClientes)): ?>
                                    <?php $nomeExibido = $cliente['tipoCliente'] === 'PJ' ? $cliente['razaoSocial'] : trim($cliente['nome'] . ' ' . $cliente['sobrenome']); ?>
                                    <option value="<?= $cliente['idCliente'] ?>"><?= htmlspecialchars($nomeExibido) ?></option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Funcionário responsável</label></strong>
                            <select class="form-select" name="idFuncionario" required>
                                <option value="" selected disabled>Selecione um funcionário ativo</option>
                                <?php while ($funcionario = mysqli_fetch_assoc($resultadoFuncionarios)): ?>
                                    <option value="<?= $funcionario['idFuncionario'] ?>">
                                        <?= htmlspecialchars($funcionario['nome'] . ' ' . $funcionario['sobrenome']) ?> - <?= htmlspecialchars($funcionario['funcao']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Vínculo da venda</label></strong>
                            <select class="form-select" name="tipoVinculo" id="selectTipoVinculo">
                                <option value="nenhum" selected>Nenhum</option>
                                <option value="pacote">Pacote</option>
                                <option value="fretamento">Fretamento</option>
                            </select>

                            <div id="blocoPacote" class="pt-2" style="display: none;">
                                <label class="d-block py-2">Pacote</label>
                                <select class="form-select" name="idPacote" id="selectPacote">
                                    <option value="" selected>Selecione um pacote</option>
                                    <?php while ($pacote = mysqli_fetch_assoc($resultadoPacotes)): ?>
                                        <option value="<?= $pacote['idPacote'] ?>" data-preco="<?= htmlspecialchars($pacote['preco']) ?>"><?= htmlspecialchars($pacote['nomePacote']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="blocoFretamento" class="pt-2" style="display: none;">
                                <label class="d-block py-2">Fretamento</label>
                                <select class="form-select" name="idFretamento" id="selectFretamento">
                                    <option value="" selected>Selecione um fretamento aprovado</option>
                                    <?php while ($fretamento = mysqli_fetch_assoc($resultadoFretamentos)): ?>
                                        <option value="<?= $fretamento['idFretamento'] ?>" data-preco="<?= htmlspecialchars($fretamento['preco']) ?>">
                                            <?= htmlspecialchars($fretamento['destino']) ?> - <?= date('d/m/Y', strtotime($fretamento['dataHoraSaida'])) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <label class="form-label pt-3">Data da Venda</label>
                            <input type="date" class="form-control" name="dataVenda" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Forma de Recebimento</label>
                                    <select class="form-select" name="formaRecebimento" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Dinheiro">Dinheiro</option>
                                        <option value="PIX">PIX</option>
                                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                                        <option value="Cartão de Débito">Cartão de Débito</option>
                                        <option value="Boleto">Boleto</option>
                                        <option value="Transferência">Transferência</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data do Recebimento</label>
                                    <input type="date" class="form-control" name="dataRecebimento" required>
                                </div>
                            </div>

                            <label class="form-label pt-2">Valor Recebido</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="valorRecebido" id="valorRecebido" step="0.01" min="0" placeholder="0,00" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Quantidade de Parcelas</label>
                                    <input type="number" class="form-control" name="qtdParcelas" min="1" placeholder="1">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Juros (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="juros" step="0.01" min="0" placeholder="0">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status" required>
                                <option value="Em andamento" selected>Em andamento</option>
                                <option value="Concluída">Concluída</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <button name="salvar" style="background-color: #0b5ed7; color: white;" class="btn me-md-2" type="submit">Salvar</button>
            </div>
        </form>
    </section>

    <script>
        // Mostra o select de Pacote ou de Fretamento, conforme o vínculo escolhido
        const selectTipoVinculo = document.getElementById('selectTipoVinculo');
        const blocoPacote = document.getElementById('blocoPacote');
        const blocoFretamento = document.getElementById('blocoFretamento');
        const selectPacote = document.getElementById('selectPacote');
        const selectFretamento = document.getElementById('selectFretamento');
        const inputValorRecebido = document.getElementById('valorRecebido');

        selectTipoVinculo.addEventListener('change', function() {
            blocoPacote.style.display = this.value === 'pacote' ? 'block' : 'none';
            blocoFretamento.style.display = this.value === 'fretamento' ? 'block' : 'none';

            // Limpa o select escondido, pra não mandar um valor que não faz mais sentido
            if (this.value !== 'pacote') selectPacote.value = '';
            if (this.value !== 'fretamento') selectFretamento.value = '';
        });

        // Preenche o Valor Recebido com o preço do pacote ou do fretamento selecionado
        function preencherValorRecebido() {
            const preco = this.options[this.selectedIndex].dataset.preco;
            if (preco) {
                inputValorRecebido.value = parseFloat(preco).toFixed(2);
            }
        }
        selectPacote.addEventListener('change', preencherValorRecebido);
        selectFretamento.addEventListener('change', preencherValorRecebido);
    </script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>
