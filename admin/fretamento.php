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
    $mensagem = "<div class='alert alert-success'>Fretamento cadastrado com sucesso!</div>";
}

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
    $qntdPassageiros        = mysqli_real_escape_string($conexao, $_POST['qntdPassageiros'] ?? '0');
    $status                 = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');


    $sql = "INSERT INTO `tbFretamento` (
        `idVeiculo`, `idCliente`, `idFuncionario`, `cidadeOrigem`, `locaisEmbarque`, `dataHoraSaida`,
        `destino`, `dataHoraRetorno`, `valorCombustivel`, `qtdKm`, `consumoCombustivel`, `preco`, `qntdPassageiros`, `status`
    ) VALUES (
        '$idVeiculo', '$idCliente', '$idFuncionario', '$cidadeOrigem', '$locaisEmbarque', '$dataHoraSaida',
        '$destino', '$dataHoraRetorno', '$valorCombustivel', '$qtdKm', '$consumoCombustivel','$preco', '$qntdPassageiros', '$status'
    )";

    if (mysqli_query($conexao, $sql)) {
        // Redireciona pra evitar reenvio do formulário ao atualizar a página (F5)
        header('Location: fretamento.php?sucesso=1');
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . mysqli_error($conexao) . "</div>";
    }
}

// Só busca veículo/cliente/funcionário com status Ativo, pra alimentar os selects
// SELECT * traz todas as colunas, assim dá pra mostrar qualquer campo no option sem precisar mudar a query
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

                            <strong><label class="d-block pt-3">Veículo</label></strong>
                            <select class="form-select" name="idVeiculo" required>
                                <option value="" selected disabled>Selecione um veículo ativo</option>
                                <?php while ($veiculo = mysqli_fetch_assoc($resultadoVeiculos)): ?>
                                    <option value="<?= $veiculo['idVeiculo'] ?>">
                                        <?= htmlspecialchars($veiculo['nomeIdentificacao']) ?> (<?= htmlspecialchars($veiculo['numeracao']) ?>)
                                    </option>
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

                            <label class="form-label pt-3">Origem</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="cidadeOrigem" id="cidadeOrigem" placeholder="Cascavel" autocomplete="off" required>
                            </div>
                            <input type="hidden" id="cidadeOrigemLat">
                            <input type="hidden" id="cidadeOrigemLon">

                            <label class="form-label pt-3">Destino</label>
                            <div class="position-relative">
                                <input class="form-control" type="text" name="destino" id="destino" placeholder="Foz do Iguaçu" autocomplete="off" required>
                            </div>
                            <input type="hidden" id="destinoLat">
                            <input type="hidden" id="destinoLon">

                            <label class="form-label pt-2">Locais de Embarque</label>
                            <input class="form-control" type="text" name="locaisEmbarque" placeholder="Rodoviária, Praça Central..." required>

                            <label class="form-label pt-2">Quantidade de Passageiros</label>
                            <input class="form-control" type="number" name="qntdPassageiros" min="0" placeholder="40" required>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Saída</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraSaida" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Data e Hora de Retorno</label>
                                    <input type="datetime-local" class="form-control" name="dataHoraRetorno" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Quantidade de Km</label>
                                    <input type="number" class="form-control" name="qtdKm" id="qtdKm" step="0.01" min="0" placeholder="0" oninput="calcularGasto()" required>
                                    <small class="text-muted">Calculado automaticamente (ida e volta) ao selecionar origem e destino — pode ajustar</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label pt-2">Valor do Combustível</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="preco" name="valorCombustivel" step="0.01" min="0" placeholder="0,00" oninput="calcularGasto()" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label pt-2">Kilometros por Litro</label>
                                    <div class="input-group">
                                        <input type="number" id="consumo" class="form-control" name="consumoCombustivel" step="0.01" min="0" placeholder="Km/L" oninput="calcularGasto()" required>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function calcularGasto() {
                                    const distancia = document.getElementById('qtdKm').value;
                                    const consumo = document.getElementById('consumo').value;
                                    const preco = document.getElementById('preco').value;
                                    const campoGasto = document.getElementById('gasto');

                                    if (distancia !== '' && consumo !== '' && preco !== '' && parseFloat(consumo) > 0) {
                                        const litros = parseFloat(distancia) / parseFloat(consumo);
                                        const gasto = litros * parseFloat(preco);
                                        campoGasto.value = gasto.toFixed(2); // só o número, sem "R$" e sem vírgula
                                    } else {
                                        campoGasto.value = '';
                                    }
                                }
                            </script>


                            <label class="form-label pt-2">Valor do Orçamento</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="gasto" class="form-control" name="preco" step="0.01" min="0" placeholder="0,00" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status" required>
                                <option value="Orçamento" selected>Orçamento</option>
                                <option value="Aprovado">Aprovado</option>
                                <option value="Em andamento">Em andamento</option>
                                <option value="Concluído">Concluído</option>
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

    <script src="/ValdirTur/assets/js/mapa-livre.js"></script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>