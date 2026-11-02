<?php
session_start();

// Acesso apenas para admin logado
if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

// Configurações e conexão com o banco
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');
require_once(__DIR__ . '/../includes/agenda.php');

// Devolve o valor digitado (mantém os dados no formulário após um erro)
function valor($campo)
{
    return htmlspecialchars($_POST[$campo] ?? '');
}

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Fretamento cadastrado com sucesso!</div>";
}

// Salvar: recebe os dados do formulário e grava no banco
if (isset($_POST['salvar'])) {

    $idVeiculo              = mysqli_real_escape_string($conexao, $_POST['idVeiculo'] ?? '');
    $idCliente              = mysqli_real_escape_string($conexao, $_POST['idCliente'] ?? '');
    $idFuncionario          = mysqli_real_escape_string($conexao, $_POST['idFuncionario'] ?? '');
    $cidadeOrigem           = mysqli_real_escape_string($conexao, $_POST['cidadeOrigem'] ?? '');
    $locaisEmbarque         = mysqli_real_escape_string($conexao, $_POST['locaisEmbarque'] ?? '');
    $dataHoraSaida          = mysqli_real_escape_string($conexao, $_POST['dataHoraSaida'] ?? '');
    $destino                = mysqli_real_escape_string($conexao, $_POST['destino'] ?? '');
    $dataHoraRetorno        = mysqli_real_escape_string($conexao, $_POST['dataHoraRetorno'] ?? '');
    $valorCombustivel       = mysqli_real_escape_string($conexao, $_POST['valorCombustivel'] ?? '0');
    $consumoCombustivel     = mysqli_real_escape_string($conexao, $_POST['consumoCombustivel'] ?? '');
    $qtdKm                  = mysqli_real_escape_string($conexao, $_POST['qtdKm'] ?? '0');
    $preco                  = mysqli_real_escape_string($conexao, $_POST['preco'] ?? '0');
    $valorSinal             = mysqli_real_escape_string($conexao, ($_POST['valorSinal'] ?? '') !== '' ? $_POST['valorSinal'] : '0');
    $qtdPassageiros         = mysqli_real_escape_string($conexao, $_POST['qtdPassageiros'] ?? '0');
    $status                 = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // Comando SQL
    $sql = "INSERT INTO `tbFretamento` (
        `idVeiculo`, `idCliente`, `idFuncionario`, `cidadeOrigem`, `locaisEmbarque`, `dataHoraSaida`,
        `destino`, `dataHoraRetorno`, `valorCombustivel`, `qtdKm`, `consumoCombustivel`, `preco`, `valorSinal`, `qtdPassageiros`, `status`
    ) VALUES (
        '$idVeiculo', '$idCliente', '$idFuncionario', '$cidadeOrigem', '$locaisEmbarque', '$dataHoraSaida',
        '$destino', '$dataHoraRetorno', '$valorCombustivel', '$qtdKm', '$consumoCombustivel', '$preco', '$valorSinal', '$qtdPassageiros', '$status'
    )";

    // Validações: datas, agenda do veículo e sinal
    $erro = validarAgendaFretamento($conexao, $_POST['idVeiculo'] ?? 0, $_POST['dataHoraSaida'] ?? '', $_POST['dataHoraRetorno'] ?? '', $_POST['status'] ?? '');

    if ($erro === '' && (float) $valorSinal > (float) $preco) {
        $erro = 'O sinal não pode ser maior que o valor do orçamento.';
    }

    if ($erro !== '') {
        $mensagem = "<div class='alert alert-danger'>" . htmlspecialchars($erro) . "</div>";
    } else {
        try {
            mysqli_query($conexao, $sql);

            header('Location: fretamento.php?sucesso=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

$resultadoVeiculos = mysqli_query($conexao, "SELECT * FROM tbVeiculo WHERE status = 'Ativo' ORDER BY nomeIdentificacao");
$resultadoClientes = mysqli_query($conexao, "SELECT * FROM tbCliente WHERE status = 'Ativo' ORDER BY nome, razaoSocial");
$resultadoFuncionarios = mysqli_query($conexao, "SELECT * FROM tbFuncionario WHERE status = 'Ativo' ORDER BY nome");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Cadastro de fretamento</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST">
            <button class="botao-voltar" type="button" onclick="window.location.href='listas/fretamentos.php'">
                <i class="bi bi-signpost-split-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Adicionar fretamento (orçamento)</strong></label>

            <?= $mensagem ?>

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do fretamento -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Cliente</label></strong>
                            <select class="form-select" name="idCliente" required>
                                <option value="" <?= empty($_POST['idCliente']) ? 'selected' : '' ?> disabled>Selecione um cliente ativo</option>
                                <?php while ($cliente = mysqli_fetch_assoc($resultadoClientes)): ?>
                                    <?php $nomeExibido = $cliente['tipoCliente'] === 'PJ' ? $cliente['razaoSocial'] : trim($cliente['nome'] . ' ' . $cliente['sobrenome']); ?>
                                    <option value="<?= $cliente['idCliente'] ?>" <?= ($_POST['idCliente'] ?? '') == $cliente['idCliente'] ? 'selected' : '' ?>><?= htmlspecialchars($nomeExibido) ?></option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Veículo</label></strong>
                            <select class="form-select" name="idVeiculo" required>
                                <option value="" <?= empty($_POST['idVeiculo']) ? 'selected' : '' ?> disabled>Selecione um veículo ativo</option>
                                <?php while ($veiculo = mysqli_fetch_assoc($resultadoVeiculos)): ?>
                                    <option value="<?= $veiculo['idVeiculo'] ?>" <?= ($_POST['idVeiculo'] ?? '') == $veiculo['idVeiculo'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($veiculo['nomeIdentificacao']) ?> (<?= htmlspecialchars($veiculo['numeracao']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <strong><label class="d-block pt-3">Funcionário responsável</label></strong>
                            <select class="form-select" name="idFuncionario" required>
                                <option value="" <?= empty($_POST['idFuncionario']) ? 'selected' : '' ?> disabled>Selecione um funcionário ativo</option>
                                <?php while ($funcionario = mysqli_fetch_assoc($resultadoFuncionarios)): ?>
                                    <option value="<?= $funcionario['idFuncionario'] ?>" <?= ($_POST['idFuncionario'] ?? '') == $funcionario['idFuncionario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($funcionario['nome'] . ' ' . $funcionario['sobrenome']) ?> - <?= htmlspecialchars($funcionario['funcao']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>

                            <label class="form-label pt-3">Origem</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="cidadeOrigem" id="cidadeOrigem" placeholder="Cascavel" autocomplete="off" value="<?= valor('cidadeOrigem') ?>" required>
                            </div>
                            <input type="hidden" id="cidadeOrigemLat">
                            <input type="hidden" id="cidadeOrigemLon">

                            <label class="form-label pt-3">Destino</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="destino" id="destino" placeholder="Foz do Iguaçu" autocomplete="off" value="<?= valor('destino') ?>" required>
                            </div>
                            <input type="hidden" id="destinoLat">
                            <input type="hidden" id="destinoLon">

                            <label class="form-label pt-2">Locais de Embarque</label>
                            <input class="form-control" type="text" name="locaisEmbarque" placeholder="Rodoviária, Praça Central..." value="<?= valor('locaisEmbarque') ?>" required>

                            <label class="form-label pt-2">Quantidade de Passageiros</label>
                            <input class="form-control" type="number" name="qtdPassageiros" min="0" placeholder="40" value="<?= valor('qtdPassageiros') ?>" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Saída</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraSaida" value="<?= valor('dataHoraSaida') ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Retorno</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraRetorno" value="<?= valor('dataHoraRetorno') ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Quantidade de Km</label>
                                    <input type="number" class="form-control" name="qtdKm" id="qtdKm" step="0.01" min="0" placeholder="0" oninput="calcularGasto()" value="<?= valor('qtdKm') ?>" required>
                                    <small class="text-muted">Calculado automaticamente (ida e volta) ao selecionar origem e destino — pode ajustar</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Valor do Combustível</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco" name="valorCombustivel" step="0.01" min="0" placeholder="0,00" oninput="calcularGasto()" value="<?= valor('valorCombustivel') ?>" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pt-2">Kilometros por Litro</label>
                                    <div class="input-group">
                                        <input type="number" id="consumo" class="form-control" name="consumoCombustivel" step="0.01" min="0" placeholder="Km/L" oninput="calcularGasto()" value="<?= valor('consumoCombustivel') ?>" required>
                                    </div>
                                </div>
                            </div>

                            <script>
                                // Orçamento = (km ÷ consumo) × preço do combustível
                                function calcularGasto() {
                                    const distancia = document.getElementById('qtdKm').value;
                                    const consumo = document.getElementById('consumo').value;
                                    const preco = document.getElementById('preco').value;
                                    const campoGasto = document.getElementById('gasto');

                                    if (distancia !== '' && consumo !== '' && preco !== '' && parseFloat(consumo) > 0) {
                                        const litros = parseFloat(distancia) / parseFloat(consumo);
                                        const gasto = litros * parseFloat(preco);
                                        campoGasto.value = gasto.toFixed(2);
                                    } else {
                                        campoGasto.value = '';
                                    }

                                    atualizarSinal();
                                }
                            </script>


                            <label class="form-label pt-2">Valor do Orçamento</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="gasto" class="form-control" name="preco" step="0.01" min="0" placeholder="0,00" value="<?= valor('preco') ?>" required>
                            </div>

                            <label class="form-label pt-2">Sinal (entrada para reservar a data)</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="valorSinal" class="form-control" name="valorSinal" step="0.01" min="0" placeholder="0,00" value="<?= valor('valorSinal') ?>">
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
                                <?php foreach (['Orçamento', 'Aprovado', 'Cancelado', 'Rascunho'] as $opcao): ?>
                                    <option value="<?= $opcao ?>" <?= ($_POST['status'] ?? 'Orçamento') === $opcao ? 'selected' : '' ?>><?= $opcao ?></option>
                                <?php endforeach; ?>
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
