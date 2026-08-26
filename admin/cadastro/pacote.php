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
    $mensagem = "<div class='alert alert-success'>Pacote cadastrado com sucesso!</div>";
}

if (isset($_POST["salvar"])) {
    $diretorio = __DIR__ . '/../../assets/uploads/';

    // Processamento de upload (um arquivo só, a coluna `midia` guarda um nome de arquivo)
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $nomeMidia = "";
    if (isset($_FILES['midia']) && $_FILES['midia']['error'] === UPLOAD_ERR_OK) {
        $nomeOriginal = basename($_FILES['midia']['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (in_array($extensao, $extensoesPermitidas, true)) {
            $nomeSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeOriginal);
            $nomeMidia = time() . "_" . $nomeSeguro;
            move_uploaded_file($_FILES['midia']['tmp_name'], $diretorio . $nomeMidia);
        }
    }

    // Captura dos dados
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

    // Inserção no Banco
    $sql = "INSERT INTO `tbpacote` (
        `nomePacote`, `destino`, `locaisEmbarque`, `dataHoraSaida`, `dataHoraRetorno`,
        `preco`, `duracaoViagem`, `descricaoCurta`, `descricaoLonga`, `vagasDisponiveis`, `pacoteParceiro`, `midia`, `status`, `juros`, `qtdParcelas`
    ) VALUES (
        '$nomePacote', '$destino', '$locaisEmbarque', '$dataSaida', '$dataRetorno',
        '$preco', '$duracao', '$descricaoCurta', '$descricaoLonga', '$vagasDisponiveis', '$pacoteParceiro', '$nomeMidia', '$status', '$juros', '$parcelas'
    )";

    if (mysqli_query($conexao, $sql)) {
        // Redireciona pra evitar reenvio do formulário (e reenvio do upload) ao atualizar a página (F5)
        header('Location: pacote.php?sucesso=1');
        exit;
    } else {
        $mensagem = "<div class='alert alert-danger'>Erro ao cadastrar: " . mysqli_error($conexao) . "</div>";
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
                <div class="col-lg-8 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome do Pacote</label>
                            <input class="form-control" type="text" name="nomePacote" placeholder="Excursão Ody Park Aquático" required>

                            <label class="py-2">Descrição Curta</label>
                            <input class="form-control" type="text" name="descricaoCurta" id="descricaoCurta" maxlength="100" placeholder="Aparece no card da viagem" required>

                            <label class="py-2">Descrição Longa</label>
                            <textarea class="form-control" name="descricaoLonga" id="descricaoLonga" rows="5" placeholder="Detalhes completos da viagem" required></textarea>

                            <label class="py-1">Conteúdo (Imagem ou Vídeo)</label>
                            <div class="upload-container">
                                <div class="upload-area d-flex flex-wrap align-items-center justify-content-center p-3">
                                    <input type="file" name="midia" id="fileUpload" class="d-none" accept="image/*,video/*">
                                    <label for="fileUpload" class="text-center w-100 py-4 cursor-pointer border border-secondary border-dashed">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <span class="d-block text-secondary">Clique para selecionar</span>
                                    </label>
                                </div>
                            </div>

                            <label class="py-2">Destino</label>
                            <input class="form-control" type="text" name="destino" placeholder="Ody Park Aquático" required>

                            <label class="form-label pt-2">Data e Hora de Saída</label>
                            <input type="datetime-local" class="form-control" id="dataHoraSaida" name="dataHoraSaida" required>

                            <label class="py-2">Local de Saída</label>
                            <input class="form-control" type="text" name="locaisEmbarque" placeholder="Rodoviária" required>

                            <label class="form-label pt-2">Data e Hora de Retorno</label>
                            <input type="datetime-local" class="form-control" id="dataHoraRetorno" name="dataHoraRetorno" required>

                            <label class="form-label pt-2">Duração Estimada</label>
                            <div class="input-group">
                                <input type="text" class="form-control bg-light" id="duracao_display" placeholder="Calculado automaticamente" readonly>
                                <span class="input-group-text"><i class="bi bi-clock"></i></span>
                            </div>
                            <input type="hidden" name="duracao_viagem" id="duracao_valor">

                            <label class="form-label pt-2">Valor do pacote</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" class="form-control" name="preco" id="preco" step="0.01" min="0" placeholder="0,00" required>
                            </div>

                            <label class="form-label pt-2">Parcelas</label>
                            <input type="number" class="form-control" name="parcelas" id="parcelas" min="1" placeholder="1">

                             <label class="form-label pt-2">Juros (%)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" name="juros" id="juros" step="1" min="0" placeholder="0">
                                <span class="input-group-text">%</span>
                            </div>

                            <div class="input-group mt-2">
                                <span class="input-group-text">Valor da parcela</span>
                                <input type="text" class="form-control bg-light" id="valorParcelaDisplay" placeholder="Calculado automaticamente" readonly>
                            </div>

                            <label class="form-label pt-2">Vagas Disponíveis</label>
                            <input type="number" class="form-control" name="vagasDisponiveis" min="0" placeholder="40" required>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-12">
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status">
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>

                            <strong><label class="d-block pt-3">Pacote de Parceria</label></strong>
                            <select class="form-select" id="selectParceria">
                                <option value="Não" selected>Não</option>
                                <option value="Sim">Sim</option>
                            </select>

                            <div id="qualParceria" class="pt-2 d-none">
                                <label class="py-2">Qual?</label>
                                <input class="form-control" type="text" id="qualParceriaInput" placeholder="Nome do parceiro">
                            </div>

                            <!-- Campo real enviado pro banco: "Não" ou o nome do parceiro -->
                            <input type="hidden" name="pacoteParceiro" id="pacoteParceiroHidden" value="Não">
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
        // Lógica de cálculo de duração
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

                    // Texto amigável pra mostrar (ex: "2 dia(s) 3 hora(s)")
                    const dias = Math.floor(horasTotal / 24);
                    const horasRestantes = horasTotal % 24;
                    let resultado = (dias > 0 ? dias + " dia(s) " : "") + (horasRestantes > 0 || dias === 0 ? horasRestantes + "e hora(s)" : "");
                    displayDuracao.value = resultado;

                    // Valor real enviado pro banco, formato TIME (HH:MM:SS) — aceita mais de 24h
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

        document.getElementById('fileUpload').addEventListener('change', function(e) {
            const uploadArea = document.querySelector('.upload-area');
            const file = e.target.files[0];

            if (file) {
                const existing = uploadArea.querySelector('.preview-container');
                if (existing) existing.remove();

                const reader = new FileReader();
                reader.onload = function(event) {
                    const div = document.createElement('div');
                    div.className = 'preview-container';

                    let media;
                    if (file.type.startsWith('video/')) {
                        media = document.createElement('video');
                        media.controls = true;
                    } else {
                        media = document.createElement('img');
                    }
                    media.src = event.target.result;
                    media.style.width = '120px';
                    media.style.height = '120px';
                    media.style.objectFit = 'cover';

                    const btn = document.createElement('span');
                    btn.innerHTML = 'X';
                    btn.className = 'btn-remove';
                    btn.onclick = function() {
                        div.remove();
                        document.getElementById('fileUpload').value = "";
                    };

                    div.appendChild(media);
                    div.appendChild(btn);
                    uploadArea.appendChild(div);
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>