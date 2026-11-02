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
require_once(__DIR__ . '/../../includes/agenda.php');

$erro = "";
$sucesso = false;
$id = $_POST['id'] ?? $_GET['id'] ?? '';


// Salvar: recebe os dados do formulário e atualiza no banco
if (isset($_POST['salvar'])) {

    $id               = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $idVeiculo        = mysqli_real_escape_string($conexao, $_POST['idVeiculo'] ?? '');
    $idCliente        = mysqli_real_escape_string($conexao, $_POST['idCliente'] ?? '');
    $idFuncionario    = mysqli_real_escape_string($conexao, $_POST['idFuncionario'] ?? '');
    $cidadeOrigem     = mysqli_real_escape_string($conexao, $_POST['cidadeOrigem'] ?? '');
    $locaisEmbarque   = mysqli_real_escape_string($conexao, $_POST['locaisEmbarque'] ?? '');
    $dataHoraSaida    = mysqli_real_escape_string($conexao, $_POST['dataHoraSaida'] ?? '');
    $destino          = mysqli_real_escape_string($conexao, $_POST['destino'] ?? '');
    $dataHoraRetorno  = mysqli_real_escape_string($conexao, $_POST['dataHoraRetorno'] ?? '');
    $valorCombustivel = mysqli_real_escape_string($conexao, $_POST['valorCombustivel'] ?? '0');
    $qtdKm            = mysqli_real_escape_string($conexao, $_POST['qtdKm'] ?? '0');
    $preco            = mysqli_real_escape_string($conexao, $_POST['preco'] ?? '0');
    $valorSinal       = mysqli_real_escape_string($conexao, ($_POST['valorSinal'] ?? '') !== '' ? $_POST['valorSinal'] : '0');
    $qtdPassageiros  = mysqli_real_escape_string($conexao, $_POST['qtdPassageiros'] ?? '0');
    $status           = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // Comando SQL
    $sql = "UPDATE `tbFretamento` SET
        `idVeiculo` = '$idVeiculo',
        `idCliente` = '$idCliente',
        `idFuncionario` = '$idFuncionario',
        `cidadeOrigem` = '$cidadeOrigem',
        `locaisEmbarque` = '$locaisEmbarque',
        `dataHoraSaida` = '$dataHoraSaida',
        `destino` = '$destino',
        `dataHoraRetorno` = '$dataHoraRetorno',
        `valorCombustivel` = '$valorCombustivel',
        `qtdKm` = '$qtdKm',
        `preco` = '$preco',
        `valorSinal` = '$valorSinal',
        `qtdPassageiros` = '$qtdPassageiros',
        `status` = '$status'
        WHERE `idFretamento` = '$id'";

    // Validações: datas, agenda do veículo (ignorando este próprio fretamento) e sinal
    $erro = validarAgendaFretamento($conexao, $_POST['idVeiculo'] ?? 0, $_POST['dataHoraSaida'] ?? '', $_POST['dataHoraRetorno'] ?? '', $_POST['status'] ?? '', $id);

    if ($erro === '' && (float) $valorSinal > (float) $preco) {
        $erro = 'O sinal não pode ser maior que o valor do orçamento.';
    }

    if ($erro === '') {
        try {
            mysqli_query($conexao, $sql);
            $sucesso = true;
        } catch (mysqli_sql_exception $e) {
            $erro = "Erro ao alterar: " . $e->getMessage();
        }
    }
}


$idBusca = mysqli_real_escape_string($conexao, $id);
// Busca o registro atual para preencher o formulário
$sql = "SELECT * FROM tbFretamento WHERE idFretamento = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$fretamento = mysqli_fetch_assoc($resultado);

// Em caso de erro, mostra o que foi digitado em vez dos dados do banco
if ($erro !== "") {
    $fretamento = array_merge($fretamento, $_POST);
}

$dataSaidaInput   = $fretamento['dataHoraSaida'] ? date('Y-m-d\TH:i', strtotime($fretamento['dataHoraSaida'])) : '';
$dataRetornoInput = $fretamento['dataHoraRetorno'] ? date('Y-m-d\TH:i', strtotime($fretamento['dataHoraRetorno'])) : '';

$resultadoVeiculos = mysqli_query($conexao, "SELECT * FROM tbVeiculo WHERE status = 'Ativo' ORDER BY nomeIdentificacao");
$resultadoClientes = mysqli_query($conexao, "SELECT * FROM tbCliente WHERE status = 'Ativo' ORDER BY nome, razaoSocial");
$resultadoFuncionarios = mysqli_query($conexao, "SELECT * FROM tbFuncionario WHERE status = 'Ativo' ORDER BY nome");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar fretamento</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST">
            <button class="botao-voltar" type="button" onclick="window.location.href='../listas/fretamentos.php'">
                <i class="bi bi-signpost-split-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Alterar fretamento (orçamento)</strong></label>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">Edição feita com sucesso!</div>
            <?php endif; ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- ID do registro (campo oculto) -->
            <input type="hidden" name="id" value="<?= $fretamento['idFretamento'] ?>">

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do fretamento -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Cliente</label></strong>
                            <select class="form-select" name="idCliente" required>
                                <option value="" disabled>Selecione um cliente ativo</option>
                                <?php while ($cliente = mysqli_fetch_assoc($resultadoClientes)): ?>
                                    <?php $nomeExibido = $cliente['tipoCliente'] === 'PJ' ? $cliente['razaoSocial'] : trim($cliente['nome'] . ' ' . $cliente['sobrenome']); ?>
                                    <option value="<?= $cliente['idCliente'] ?>" <?= $cliente['idCliente'] == $fretamento['idCliente'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($nomeExibido) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Veículo</label></strong>
                            <select class="form-select" name="idVeiculo" required>
                                <option value="" disabled>Selecione um veículo ativo</option>
                                <?php while ($veiculo = mysqli_fetch_assoc($resultadoVeiculos)): ?>
                                    <option value="<?= $veiculo['idVeiculo'] ?>" <?= $veiculo['idVeiculo'] == $fretamento['idVeiculo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($veiculo['nomeIdentificacao']) ?> (<?= htmlspecialchars($veiculo['numeracao']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Funcionário responsável</label></strong>
                            <select class="form-select" name="idFuncionario" required>
                                <option value="" disabled>Selecione um funcionário ativo</option>
                                <?php while ($funcionario = mysqli_fetch_assoc($resultadoFuncionarios)): ?>
                                    <option value="<?= $funcionario['idFuncionario'] ?>" <?= $funcionario['idFuncionario'] == $fretamento['idFuncionario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($funcionario['nome'] . ' ' . $funcionario['sobrenome']) ?> - <?= htmlspecialchars($funcionario['funcao']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <label class="form-label pt-3">Destino</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="destino" id="destino"
                                    value="<?= htmlspecialchars($fretamento['destino']) ?>" autocomplete="off" required>
                            </div>
                            <input type="hidden" id="destinoLat">
                            <input type="hidden" id="destinoLon">

                            <label class="form-label pt-2">Cidade de Origem</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="cidadeOrigem" id="cidadeOrigem"
                                    value="<?= htmlspecialchars($fretamento['cidadeOrigem']) ?>" autocomplete="off" required>
                            </div>
                            <input type="hidden" id="cidadeOrigemLat">
                            <input type="hidden" id="cidadeOrigemLon">
                            <!-- <small class="text-muted">Se trocar origem ou destino, selecione um item da lista de sugestões pra recalcular o Km automaticamente</small> -->

                            <label class="form-label pt-2">Locais de Embarque</label>
                            <input class="form-control" type="text" name="locaisEmbarque"
                                value="<?= htmlspecialchars($fretamento['locaisEmbarque']) ?>" required>

                            <label class="form-label pt-2">Quantidade de Passageiros</label>
                            <input class="form-control" type="number" name="qtdPassageiros" min="0"
                                value="<?= htmlspecialchars($fretamento['qtdPassageiros']) ?>" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Saída</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraSaida"
                                        value="<?= $dataSaidaInput ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Retorno</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraRetorno"
                                        value="<?= $dataRetornoInput ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Quantidade de Km</label>
                                    <input type="number" class="form-control" name="qtdKm" id="qtdKm" step="0.01" min="0"
                                        value="<?= htmlspecialchars($fretamento['qtdKm']) ?>" required>
                                    <small class="text-muted">O valor automático é aproximado</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Valor do Combustível</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" name="valorCombustivel" step="0.01" min="0"
                                            value="<?= htmlspecialchars($fretamento['valorCombustivel']) ?>" required>
                                    </div>
                                </div>
                            </div>

                            <label class="form-label pt-2">Valor do Orçamento</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="gasto" class="form-control" name="preco" step="0.01" min="0"
                                    value="<?= htmlspecialchars($fretamento['preco']) ?>" required>
                            </div>

                            <label class="form-label pt-2">Sinal (entrada para reservar a data)</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="valorSinal" class="form-control" name="valorSinal" step="0.01" min="0" placeholder="0,00"
                                    value="<?= htmlspecialchars($fretamento['valorSinal'] ?? '0') ?>">
                            </div>
                            <small class="text-muted d-block" id="infoSinal"></small>
                            <small class="text-warning-emphasis d-none" id="avisoSinal">Atenção: o sinal está abaixo de 10% do valor do orçamento.</small>
                        </div>
                    </div>
                </div>

                <!-- Coluna da direita -->
                <div class="col-lg-4 col-md-12">
                    <!-- Card: Status -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status" id="status" required>
                                <option value="Orçamento" <?= $fretamento['status'] === 'Orçamento' ? 'selected' : '' ?>>Orçamento</option>
                                <option value="Aprovado" <?= $fretamento['status'] === 'Aprovado' ? 'selected' : '' ?>>Aprovado</option>
                                <option value="Cancelado" <?= $fretamento['status'] === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                <option value="Rascunho" <?= $fretamento['status'] === 'Rascunho' ? 'selected' : '' ?>>Rascunho</option>
                            </select>
                            <small class="text-muted d-block mt-2">Só fretamentos <strong>Aprovados</strong> reservam o ônibus na agenda.</small>
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
        // Sinal: mostra os 10% sugeridos, o saldo a pagar e o prazo (véspera da viagem)
        const campoOrcamento = document.getElementById('gasto');
        const campoSinal = document.getElementById('valorSinal');
        const campoSaida = document.querySelector('[name="dataHoraSaida"]');
        const campoStatus = document.getElementById('status');
        const infoSinal = document.getElementById('infoSinal');
        const avisoSinal = document.getElementById('avisoSinal');

        function reais(valor) {
            return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }

        function atualizarSinal() {
            const total = parseFloat(campoOrcamento.value) || 0;
            const sinal = parseFloat(campoSinal.value) || 0;
            let texto = '';

            if (total > 0) {
                texto = 'Sinal mínimo sugerido (10%): ' + reais(total * 0.10) + ' | Saldo a pagar: ' + reais(total - sinal);

                if (campoSaida.value) {
                    const vespera = new Date(campoSaida.value);
                    vespera.setDate(vespera.getDate() - 1);
                    texto += ' até ' + vespera.toLocaleDateString('pt-BR');
                }
            }

            infoSinal.textContent = texto;
            avisoSinal.classList.toggle('d-none', !(campoStatus.value === 'Aprovado' && total > 0 && sinal < total * 0.10));
        }

        [campoOrcamento, campoSinal, campoSaida, campoStatus].forEach(c => {
            c.addEventListener('input', atualizarSinal);
            c.addEventListener('change', atualizarSinal);
        });
        atualizarSinal();
    </script>
    <script src="/ValdirTur/assets/js/mapa-livre.js"></script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>
