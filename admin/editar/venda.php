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

    $id                = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $dataVenda         = mysqli_real_escape_string($conexao, $_POST['dataVenda'] ?? '');
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

    $sql = "UPDATE `tbvenda` SET
        `dataVenda` = '$dataVenda',
        `idFuncionario` = '$idFuncionario',
        `idCliente` = '$idCliente',
        `idPacote` = $idPacoteSql,
        `idFretamento` = $idFretamentoSql,
        `formaRecebimento` = '$formaRecebimento',
        `dataRecebimento` = '$dataRecebimento',
        `valorRecebido` = '$valorRecebido',
        `qtdParcelas` = '$qtdParcelas',
        `juros` = '$juros',
        `status` = '$status'
        WHERE `idVenda` = '$id'";

    if (mysqli_query($conexao, $sql)) {
        $sucesso = true; // sucesso, segue e recarrega os dados atualizados abaixo
    } else {
        $erro = "Erro ao alterar: " . mysqli_error($conexao);
    }
}

// Busca os dados atuais da venda, pra preencher o formulário
$idBusca = mysqli_real_escape_string($conexao, $id);
$sql = "SELECT * FROM tbvenda WHERE idVenda = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$venda = mysqli_fetch_assoc($resultado);

// Só busca cliente/funcionário/pacote com status Ativo, pra alimentar os selects
$resultadoClientes = mysqli_query($conexao, "SELECT * FROM tbCliente WHERE status = 'Ativo' ORDER BY nome, razaoSocial");
$resultadoFuncionarios = mysqli_query($conexao, "SELECT * FROM tbFuncionario WHERE status = 'Ativo' ORDER BY nome");
$resultadoPacotes = mysqli_query($conexao, "SELECT * FROM tbpacote WHERE status = 'Ativo' ORDER BY nomePacote");
// Só fretamentos Aprovados fazem sentido virar venda
$resultadoFretamentos = mysqli_query($conexao, "SELECT * FROM tbFretamento WHERE status = 'Aprovado' ORDER BY dataHoraSaida DESC");
$tipoVinculoAtual = $venda['idFretamento'] !== null ? 'fretamento' : ($venda['idPacote'] !== null ? 'pacote' : 'nenhum');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar venda</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST">
            <button class="botao-voltar" type="button" onclick="window.location.href='../listas/vendas.php'">
                <i class="bi bi-cash-coin fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Alterar venda</strong></label>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">Edição feita com sucesso!</div>
            <?php endif; ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Campo escondido: carrega o ID da venda junto no POST -->
            <input type="hidden" name="id" value="<?= $venda['idVenda'] ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Cliente</label></strong>
                            <select class="form-select" name="idCliente" required>
                                <option value="" disabled>Selecione um cliente ativo</option>
                                <?php while ($cliente = mysqli_fetch_assoc($resultadoClientes)): ?>
                                    <?php $nomeExibido = $cliente['tipoCliente'] === 'PJ' ? $cliente['razaoSocial'] : trim($cliente['nome'] . ' ' . $cliente['sobrenome']); ?>
                                    <option value="<?= $cliente['idCliente'] ?>" <?= $cliente['idCliente'] == $venda['idCliente'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nomeExibido) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Funcionário responsável</label></strong>
                            <select class="form-select" name="idFuncionario" required>
                                <option value="" disabled>Selecione um funcionário ativo</option>
                                <?php while ($funcionario = mysqli_fetch_assoc($resultadoFuncionarios)): ?>
                                    <option value="<?= $funcionario['idFuncionario'] ?>" <?= $funcionario['idFuncionario'] == $venda['idFuncionario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($funcionario['nome'] . ' ' . $funcionario['sobrenome']) ?> - <?= htmlspecialchars($funcionario['funcao']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Vínculo da venda</label></strong>
                            <select class="form-select" name="tipoVinculo" id="selectTipoVinculo">
                                <option value="nenhum" <?= $tipoVinculoAtual === 'nenhum' ? 'selected' : '' ?>>Nenhum</option>
                                <option value="pacote" <?= $tipoVinculoAtual === 'pacote' ? 'selected' : '' ?>>Pacote</option>
                                <option value="fretamento" <?= $tipoVinculoAtual === 'fretamento' ? 'selected' : '' ?>>Fretamento</option>
                            </select>

                            <div id="blocoPacote" class="pt-2" style="display: <?= $tipoVinculoAtual === 'pacote' ? 'block' : 'none' ?>;">
                                <label class="d-block py-2">Pacote</label>
                                <select class="form-select" name="idPacote" id="selectPacote">
                                    <option value="" <?= $venda['idPacote'] === null ? 'selected' : '' ?>>Selecione um pacote</option>
                                    <?php while ($pacote = mysqli_fetch_assoc($resultadoPacotes)): ?>
                                        <option value="<?= $pacote['idPacote'] ?>" data-preco="<?= htmlspecialchars($pacote['preco']) ?>" <?= $pacote['idPacote'] == $venda['idPacote'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($pacote['nomePacote']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div id="blocoFretamento" class="pt-2" style="display: <?= $tipoVinculoAtual === 'fretamento' ? 'block' : 'none' ?>;">
                                <label class="d-block py-2">Fretamento</label>
                                <select class="form-select" name="idFretamento" id="selectFretamento">
                                    <option value="" <?= $venda['idFretamento'] === null ? 'selected' : '' ?>>Selecione um fretamento aprovado</option>
                                    <?php while ($fretamento = mysqli_fetch_assoc($resultadoFretamentos)): ?>
                                        <option value="<?= $fretamento['idFretamento'] ?>" data-preco="<?= htmlspecialchars($fretamento['preco']) ?>" <?= $fretamento['idFretamento'] == $venda['idFretamento'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($fretamento['destino']) ?> - <?= date('d/m/Y', strtotime($fretamento['dataHoraSaida'])) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <label class="form-label pt-3">Data da Venda</label>
                            <input type="date" class="form-control" name="dataVenda" value="<?= htmlspecialchars($venda['dataVenda']) ?>" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Forma de Recebimento</label>
                                    <select class="form-select" name="formaRecebimento" required>
                                        <option value="" disabled>Selecione</option>
                                        <?php foreach (['Dinheiro', 'PIX', 'Cartão de Crédito', 'Cartão de Débito', 'Boleto', 'Transferência'] as $forma): ?>
                                            <option value="<?= $forma ?>" <?= $venda['formaRecebimento'] === $forma ? 'selected' : '' ?>><?= $forma ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data do Recebimento</label>
                                    <input type="date" class="form-control" name="dataRecebimento" value="<?= htmlspecialchars($venda['dataRecebimento']) ?>" required>
                                </div>
                            </div>

                            <label class="form-label pt-2">Valor Recebido</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="valorRecebido" id="valorRecebido" step="0.01" min="0" value="<?= htmlspecialchars($venda['valorRecebido']) ?>" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Quantidade de Parcelas</label>
                                    <input type="number" class="form-control" name="qtdParcelas" min="1" value="<?= htmlspecialchars($venda['qtdParcelas']) ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Juros (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" name="juros" step="0.01" min="0" value="<?= htmlspecialchars($venda['juros']) ?>">
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
                                <option value="Em andamento" <?= $venda['status'] === 'Em andamento' ? 'selected' : '' ?>>Em andamento</option>
                                <option value="Concluída" <?= $venda['status'] === 'Concluída' ? 'selected' : '' ?>>Concluída</option>
                                <option value="Cancelado" <?= $venda['status'] === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
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
