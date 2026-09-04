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

    $id               = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $nomePacote       = mysqli_real_escape_string($conexao, $_POST['nomePacote'] ?? '');
    $destino          = mysqli_real_escape_string($conexao, $_POST['destino'] ?? '');
    $locaisEmbarque       = mysqli_real_escape_string($conexao, $_POST['locaisEmbarque'] ?? '');
    $dataSaida        = mysqli_real_escape_string($conexao, $_POST['dataHoraSaida'] ?? '');
    $dataRetorno      = mysqli_real_escape_string($conexao, $_POST['dataHoraRetorno'] ?? '');
    $preco            = mysqli_real_escape_string($conexao, $_POST['preco'] ?? '');
    $duracao          = mysqli_real_escape_string($conexao, ($_POST['duracao_viagem'] ?? '') !== '' ? $_POST['duracao_viagem'] : '00:00:00');
    $descricaoCurta   = mysqli_real_escape_string($conexao, $_POST['descricaoCurta'] ?? '');
    $descricaoLonga   = mysqli_real_escape_string($conexao, $_POST['descricaoLonga'] ?? '');
    $vagasDisponiveis = mysqli_real_escape_string($conexao, $_POST['vagasDisponiveis'] ?? '');
    $pacoteParceiro   = mysqli_real_escape_string($conexao, $_POST['pacoteParceiro'] ?? 'Não');
    $status           = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');
    $juros            = (int) ($_POST['juros'] ?? 0);
    $parcelas         = (int) (($_POST['parcelas'] ?? '') !== '' ? $_POST['parcelas'] : 1);

    // Upload de mídia (opcional, vários arquivos). Só troca se enviarem
    // arquivo(s) novo(s) — nesse caso, substitui a lista inteira anterior.
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $novaMidiaSql = ""; // se continuar vazio, o UPDATE não mexe na coluna `midia`
    $nomesMidia = [];
    $diretorio  = __DIR__ . '/../../assets/uploads/';

    if (isset($_FILES['midia']) && is_array($_FILES['midia']['name'])) {
        foreach ($_FILES['midia']['name'] as $i => $nomeOriginal) {
            if ($_FILES['midia']['error'][$i] !== UPLOAD_ERR_OK || $nomeOriginal === '') {
                continue;
            }

            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                continue;
            }

            $nomeSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($nomeOriginal));
            $nomeSalvo  = time() . "_" . $i . "_" . $nomeSeguro;
            move_uploaded_file($_FILES['midia']['tmp_name'][$i], $diretorio . $nomeSalvo);
            $nomesMidia[] = $nomeSalvo;
        }
    }

    if ($nomesMidia) {
        $novaMidiaSql = "`midia` = '" . implode(',', $nomesMidia) . "',";
    }

    $sql = "UPDATE `tbpacote` SET
        `nomePacote` = '$nomePacote',
        `destino` = '$destino',
        `locaisEmbarque` = '$locaisEmbarque',
        `dataHoraSaida` = '$dataSaida',
        `dataHoraRetorno` = '$dataRetorno',
        `preco` = '$preco',
        `duracaoViagem` = '$duracao',
        `descricaoCurta` = '$descricaoCurta',
        `descricaoLonga` = '$descricaoLonga',
        `vagasDisponiveis` = '$vagasDisponiveis',
        `pacoteParceiro` = '$pacoteParceiro',
        `juros` = '$juros',
        `qtdParcelas` = '$parcelas',
        $novaMidiaSql
        `status` = '$status'
        WHERE `idPacote` = '$id'";

    if (mysqli_query($conexao, $sql)) {
        $sucesso = true; // sucesso, segue e recarrega os dados atualizados abaixo
    } else {
        $erro = "Erro ao alterar: " . mysqli_error($conexao);
    }
}

// Busca os dados atuais do pacote, pra preencher o formulário
$idBusca = mysqli_real_escape_string($conexao, $id);
$sql = "SELECT * FROM tbpacote WHERE idPacote = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$pacote = mysqli_fetch_array($resultado);

// O input datetime-local espera "AAAA-MM-DDTHH:MM"; o banco guarda "AAAA-MM-DD HH:MM:SS"
$dataSaidaInput   = $pacote['dataHoraSaida'] ? date('Y-m-d\TH:i', strtotime($pacote['dataHoraSaida'])) : '';
$dataRetornoInput = $pacote['dataHoraRetorno'] ? date('Y-m-d\TH:i', strtotime($pacote['dataHoraRetorno'])) : '';

// duracaoViagem vem do banco como "HH:MM:SS" (tipo TIME); monta um texto amigável pra mostrar
$duracaoTexto = '';
if ($pacote['duracaoViagem']) {
    [$horasTotal, $minutos] = array_map('intval', explode(':', $pacote['duracaoViagem']));
    $dias = intdiv($horasTotal, 24);
    $horasRestantes = $horasTotal % 24;
    $duracaoTexto = ($dias > 0 ? "$dias dia(s) " : '') . ($horasRestantes > 0 || $dias === 0 ? "$horasRestantes hora(s)" : '');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar pacote</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">
        <form method="POST" enctype="multipart/form-data">
            <button class="botao-voltar" type="button" onclick="window.location.href='../listas/pacotes.php'">
                <i class="bi bi-luggage-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Alterar pacote de viagem</strong></label>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">Edição feita com sucesso!</div>
            <?php endif; ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Campo escondido: carrega o ID do pacote junto no POST -->
            <input type="hidden" name="id" value="<?= $pacote['idPacote'] ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome do Pacote</label>
                            <input class="form-control" type="text" name="nomePacote"
                                value="<?= htmlspecialchars($pacote['nomePacote']) ?>" required>

                            <label class="py-2">Descrição Curta</label>
                            <input class="form-control" type="text" name="descricaoCurta" id="descricaoCurta" maxlength="100"
                                value="<?= htmlspecialchars($pacote['descricaoCurta']) ?>" required>

                            <label class="py-2">Descrição Longa</label>
                            <textarea class="form-control" name="descricaoLonga" id="descricaoLonga" rows="5" required><?= htmlspecialchars($pacote['descricaoLonga']) ?></textarea>

                            <div class="mb-3">
                                <label for="fileUpload" class="form-label py-1">Conteúdo (Imagens ou Vídeos)</label>
                                <input class="form-control" type="file" name="midia[]" id="fileUpload" accept="image/*,video/*" multiple>
                                <div class="form-text">
                                    <?php $qtdMidiaAtual = count(midiaLista($pacote['midia'])); ?>
                                    <?= $qtdMidiaAtual ? "Atualmente: {$qtdMidiaAtual} arquivo(s). " : '' ?>
                                    Selecionar novos arquivos substitui todos os atuais. Deixe em branco pra manter.
                                </div>
                            </div>

                            <label class="py-2">Destino</label>
                            <input class="form-control" type="text" name="destino"
                                value="<?= htmlspecialchars($pacote['destino']) ?>" required>

                            <label class="form-label pt-2">Data e Hora de Saída</label>
                            <input type="datetime-local" class="form-control" id="dataHoraSaida" name="dataHoraSaida"
                                value="<?= $dataSaidaInput ?>" required>

                            <label class="py-2">Local de Saída</label>
                            <input class="form-control" type="text" name="locaisEmbarque"
                                value="<?= htmlspecialchars($pacote['locaisEmbarque']) ?>" required>

                            <label class="form-label pt-2">Data e Hora de Retorno</label>
                            <input type="datetime-local" class="form-control" id="dataHoraRetorno" name="dataHoraRetorno"
                                value="<?= $dataRetornoInput ?>" required>

                            <label class="form-label pt-2">Duração Estimada</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="duracao_display"
                                    value="<?= htmlspecialchars($duracaoTexto) ?>" placeholder="Calculado automaticamente" readonly>
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            </div>
                            <input type="hidden" name="duracao_viagem" id="duracao_valor" value="<?= htmlspecialchars($pacote['duracaoViagem']) ?>">

                            <label class="form-label pt-2">Valor do pacote</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="preco" id="preco" step="0.01" min="0"
                                    value="<?= htmlspecialchars($pacote['preco']) ?>" required>
                            </div>

                            <label class="form-label pt-2">Juros (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="juros" id="juros" step="1" min="0"
                                    value="<?= htmlspecialchars($pacote['juros'] ?? '0') ?>">
                                <span class="input-group-text">%</span>
                            </div>

                            <label class="form-label pt-2">Parcelas</label>
                            <input type="number" class="form-control" name="parcelas" id="parcelas" min="1"
                                value="<?= htmlspecialchars($pacote['qtdParcelas'] ?? '1') ?>">

                            <div class="input-group mt-2">
                                <span class="input-group-text">Valor da parcela</span>
                                <input type="text" class="form-control bg-light" id="valorParcelaDisplay" placeholder="Calculado automaticamente" readonly>
                            </div>

                            <label class="form-label pt-2">Vagas Disponíveis</label>
                            <input type="number" class="form-control" name="vagasDisponiveis" min="0"
                                value="<?= htmlspecialchars($pacote['vagasDisponiveis']) ?>" required>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo" <?= $pacote['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= $pacote['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>

                            <strong><label class="d-block pt-3">Pacote de Parceria</label></strong>
                            <select class="form-select" id="selectParceria">
                                <option value="Não" <?= $pacote['pacoteParceiro'] === 'Não' ? 'selected' : '' ?>>Não</option>
                                <option value="Sim" <?= $pacote['pacoteParceiro'] !== 'Não' ? 'selected' : '' ?>>Sim</option>
                            </select>

                            <div id="qualParceria" class="pt-2 <?= $pacote['pacoteParceiro'] !== 'Não' ? '' : 'd-none' ?>">
                                <label class="py-2">Qual?</label>
                                <input class="form-control" type="text" id="qualParceriaInput" placeholder="Nome do parceiro"
                                    value="<?= $pacote['pacoteParceiro'] !== 'Não' ? htmlspecialchars($pacote['pacoteParceiro']) : '' ?>">
                            </div>

                            <!-- Campo real enviado pro banco: "Não" ou o nome do parceiro -->
                            <input type="hidden" name="pacoteParceiro" id="pacoteParceiroHidden" value="<?= htmlspecialchars($pacote['pacoteParceiro']) ?>">
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
        // Se a pessoa trocar as datas manualmente, recalcula a duração (mesma lógica do cadastro)
        const inputSaida = document.getElementById('dataHoraSaida');
        const inputRetorno = document.getElementById('dataHoraRetorno');
        const displayDuracao = document.getElementById('duracao_display');
        const valorDuracao = document.getElementById('duracao_valor');

        function calcularDuracao() {
            if (inputSaida.value && inputRetorno.value) {
                const dataSaida = new Date(inputSaida.value);
                const dataRetorno = new Date(inputRetorno.value);
                if (dataRetorno > dataSaida) {
                    const diffMilisegundos = dataRetorno - dataSaida;
                    const diffMinutosTotal = Math.round(diffMilisegundos / (1000 * 60));
                    const horasTotal = Math.floor(diffMinutosTotal / 60);
                    const minutos = diffMinutosTotal % 60;

                    const dias = Math.floor(horasTotal / 24);
                    const horasRestantes = horasTotal % 24;
                    let resultado = (dias > 0 ? dias + " dia(s) " : "") + (horasRestantes > 0 || dias === 0 ? horasRestantes + " hora(s)" : "");
                    displayDuracao.value = resultado;

                    const horasStr = String(horasTotal).padStart(2, '0');
                    const minutosStr = String(minutos).padStart(2, '0');
                    valorDuracao.value = `${horasStr}:${minutosStr}:00`;
                }
            }
        }
        inputSaida.addEventListener('change', calcularDuracao);
        inputRetorno.addEventListener('change', calcularDuracao);

        // Valor da parcela: (preço + juros%) / parcelas
        const inputPreco = document.getElementById('preco');
        const inputJuros = document.getElementById('juros');
        const inputParcelas = document.getElementById('parcelas');
        const valorParcelaDisplay = document.getElementById('valorParcelaDisplay');

        function calcularParcela() {
            const preco = parseFloat(inputPreco.value) || 0;
            const juros = parseFloat(inputJuros.value) || 0;
            const parcelas = parseInt(inputParcelas.value) || 0;

            if (preco > 0 && parcelas > 0) {
                const totalComJuros = preco * (1 + juros / 100);
                const valorParcela = totalComJuros / parcelas;
                valorParcelaDisplay.value = valorParcela.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            } else {
                valorParcelaDisplay.value = '';
            }
        }
        inputPreco.addEventListener('input', calcularParcela);
        inputJuros.addEventListener('input', calcularParcela);
        inputParcelas.addEventListener('input', calcularParcela);
        calcularParcela(); // já mostra o valor calculado assim que a página carrega

        // Pacote de Parceria: por trás, tudo vira um valor só ("Não" ou o nome do parceiro)
        const selectParceria = document.getElementById('selectParceria');
        const qualParceria = document.getElementById('qualParceria');
        const qualParceriaInput = document.getElementById('qualParceriaInput');
        const pacoteParceiroHidden = document.getElementById('pacoteParceiroHidden');

        function atualizarParceiro() {
            pacoteParceiroHidden.value = selectParceria.value === 'Sim'
                ? (qualParceriaInput.value.trim() || 'Sim')
                : 'Não';
        }

        selectParceria.addEventListener('change', function() {
            qualParceria.classList.toggle('d-none', this.value !== 'Sim');
            atualizarParceiro();
        });
        qualParceriaInput.addEventListener('input', atualizarParceiro);
    </script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>