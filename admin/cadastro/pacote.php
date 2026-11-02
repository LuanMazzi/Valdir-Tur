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

// Devolve o valor digitado (mantém os dados no formulário após um erro)
function valor($campo)
{
    return htmlspecialchars($_POST[$campo] ?? '');
}

$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Pacote cadastrado com sucesso!</div>";
}

// Salvar: recebe os dados do formulário e grava no banco
if (isset($_POST["salvar"])) {
    $diretorio = __DIR__ . '/../../assets/uploads/';


    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $nomesMidia = [];
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
    $nomeMidia = implode(',', $nomesMidia);


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


    // Comando SQL
    $sql = "INSERT INTO `tbpacote` (
        `nomePacote`, `destino`, `locaisEmbarque`, `dataHoraSaida`, `dataHoraRetorno`,
        `preco`, `duracaoViagem`, `descricaoCurta`, `descricaoLonga`, `vagasDisponiveis`, `pacoteParceiro`, `midia`, `status`, `juros`, `qtdParcelas`
    ) VALUES (
        '$nomePacote', '$destino', '$locaisEmbarque', '$dataSaida', '$dataRetorno',
        '$preco', '$duracao', '$descricaoCurta', '$descricaoLonga', '$vagasDisponiveis', '$pacoteParceiro', '$nomeMidia', '$status', '$juros', '$parcelas'
    )";

    try {
        mysqli_query($conexao, $sql);

        header('Location: pacote.php?sucesso=1');
        exit;
    } catch (mysqli_sql_exception $e) {
        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Cadastro de pacote</title>
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
            <label class="pb-2"><strong>Adicionar pacote de viagem</strong></label>

            <?= $mensagem ?>

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do pacote -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome do Pacote</label>
                            <input class="form-control" type="text" name="nomePacote" value="<?= valor('nomePacote') ?>" placeholder="Excursão Ody Park Aquático" required>

                            <label class="py-2">Descrição Curta</label>
                            <input class="form-control" type="text" name="descricaoCurta" id="descricaoCurta" value="<?= valor('descricaoCurta') ?>" maxlength="100" placeholder="Aparece no card da viagem" required>

                            <label class="py-2">Descrição Longa</label>
                            <textarea class="form-control" name="descricaoLonga" id="descricaoLonga" rows="5" placeholder="Detalhes completos da viagem" required><?= valor('descricaoLonga') ?></textarea>

                            <div class="mb-3">
                                <label for="fileUpload" class="py-2">Conteúdo (Imagens ou Vídeos)</label>
                                <input class="form-control" type="file" name="midia[]" id="fileUpload" accept="image/*,video/*" multiple>
                                <div class="form-text">Você pode selecionar várias imagens e vídeos de uma vez.</div>
                            </div>

                            <label class="py-2">Destino</label>
                            <input class="form-control" type="text" name="destino" value="<?= valor('destino') ?>" placeholder="Ody Park Aquático" required>

                            <label class="form-label pt-2">Data e Hora de Saída</label>
                            <input type="datetime-local" class="form-control" id="dataHoraSaida" name="dataHoraSaida" value="<?= valor('dataHoraSaida') ?>" required>

                            <label class="py-2">Local de Saída</label>
                            <input class="form-control" type="text" name="locaisEmbarque" value="<?= valor('locaisEmbarque') ?>" placeholder="Rodoviária" required>

                            <label class="form-label pt-2">Data e Hora de Retorno</label>
                            <input type="datetime-local" class="form-control" id="dataHoraRetorno" name="dataHoraRetorno" value="<?= valor('dataHoraRetorno') ?>" required>

                            <label class="form-label pt-2">Duração Estimada</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="duracao_display" placeholder="Calculado automaticamente" readonly>
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            </div>
                            <input type="hidden" name="duracao_viagem" id="duracao_valor">

                            <label class="form-label pt-2">Valor do pacote</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="preco" id="preco" value="<?= valor('preco') ?>" step="0.01" min="0" placeholder="0,00" required>
                            </div>

                            <label class="form-label pt-2">Parcelas</label>
                            <input type="number" class="form-control" name="parcelas" id="parcelas" value="<?= valor('parcelas') ?>" min="1" placeholder="1">

                             <label class="form-label pt-2">Juros (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="juros" id="juros" value="<?= valor('juros') ?>" step="1" min="0" placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>

                            <div class="input-group mt-2">
                                <span class="input-group-text">Valor da parcela</span>
                                <input type="text" class="form-control bg-light" id="valorParcelaDisplay" placeholder="Calculado automaticamente" readonly>
                            </div>

                            <label class="form-label pt-2">Vagas Disponíveis</label>
                            <input type="number" class="form-control" name="vagasDisponiveis" value="<?= valor('vagasDisponiveis') ?>" min="0" placeholder="40" required>
                        </div>
                    </div>
                </div>

                <!-- Coluna da direita -->
                <div class="col-lg-4 col-md-12">
                    <!-- Card: Status e parceria -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo" <?= ($_POST['status'] ?? '') === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>

                            <strong><label class="d-block pt-3">Pacote de Parceria</label></strong>
                            <?php
                            // pacoteParceiro guarda "Não", "Sim" ou o nome do parceiro
                            $parceiro     = $_POST['pacoteParceiro'] ?? 'Não';
                            $temParceria  = $parceiro !== 'Não';
                            $nomeParceiro = $parceiro === 'Sim' || $parceiro === 'Não' ? '' : $parceiro;
                            ?>
                            <select class="form-select" id="selectParceria">
                                <option value="Não" <?= !$temParceria ? 'selected' : '' ?>>Não</option>
                                <option value="Sim" <?= $temParceria ? 'selected' : '' ?>>Sim</option>
                            </select>

                            <div id="qualParceria" class="pt-2 <?= $temParceria ? '' : 'd-none' ?>">
                                <label class="py-2">Qual?</label>
                                <input class="form-control" type="text" id="qualParceriaInput" placeholder="Nome do parceiro"
                                    value="<?= htmlspecialchars($nomeParceiro) ?>">
                            </div>


                            <input type="hidden" name="pacoteParceiro" id="pacoteParceiroHidden" value="<?= htmlspecialchars($parceiro) ?>">
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

        // Duração da viagem (calculada pelas datas de saída e retorno)
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
                    let resultado = (dias > 0 ? dias + " dia(s) " : "") + (horasRestantes > 0 || dias === 0 ? "e " + horasRestantes + " horas" : "");
                    displayDuracao.value = resultado;

                    
                    const horasStr = String(horasTotal).padStart(2, '0');
                    const minutosStr = String(minutos).padStart(2, '0');
                    valorDuracao.value = `${horasStr}:${minutosStr}:00`;
                }
            }
        }
        inputSaida.addEventListener('change', calcularDuracao);
        inputRetorno.addEventListener('change', calcularDuracao);


        // Valor da parcela (preço, juros e quantidade de parcelas)
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

        // Recalcula duração e parcela quando a página volta com erro
        calcularDuracao();
        calcularParcela();


        // Pacote de parceria (mostra o campo "Qual?")
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