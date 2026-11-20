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
require_once(__DIR__ . '/../../includes/validar-data.php');

$erro = "";
$sucesso = false;
$id = $_POST['id'] ?? $_GET['id'] ?? '';

// Não deixa mudar a saída para o passado (manter a data que já estava salva é permitido)
if (isset($_POST['salvar'])) {
    $dadosAtuais = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT dataHoraSaida, midia FROM tbpacote WHERE idPacote = " . (int) $id));
    $saidaSalva = $dadosAtuais['dataHoraSaida'] ?? '';
    $novaSaida  = $_POST['dataHoraSaida'] ?? '';

    if (strtotime($novaSaida) !== strtotime($saidaSalva) && dataNoPassado($novaSaida)) {
        $erro = "A data de saída não pode estar no passado.";
    }
}

// Salvar: recebe os dados do formulário e atualiza no banco
if (isset($_POST['salvar']) && $erro === "") {

    $id               = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $nomePacote       = mysqli_real_escape_string($conexao, $_POST['nomePacote'] ?? '');
    $destino          = mysqli_real_escape_string($conexao, $_POST['destino'] ?? '');
    $locaisEmbarque   = mysqli_real_escape_string($conexao, $_POST['locaisEmbarque'] ?? '');
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

    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $diretorio = __DIR__ . '/../../assets/uploads/';

    // 1. Mídias antigas que o usuário optou por manter
    $midiasMantidas = isset($_POST['midias_existentes']) && is_array($_POST['midias_existentes'])
        ? array_filter($_POST['midias_existentes'])
        : [];

    // Apaga fisicamente do servidor as mídias antigas que foram removidas na tela
    $midiasAnteriores = !empty($dadosAtuais['midia']) ? explode(',', $dadosAtuais['midia']) : [];
    foreach ($midiasAnteriores as $arquivoAntigo) {
        if (!in_array($arquivoAntigo, $midiasMantidas, true)) {
            $caminhoCompleto = $diretorio . basename($arquivoAntigo);
            if (is_file($caminhoCompleto)) {
                unlink($caminhoCompleto);
            }
        }
    }

    $nomesMidia = $midiasMantidas;

    // 2. Faz o upload dos novos arquivos enviados
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

    $midiaFinal = mysqli_real_escape_string($conexao, implode(',', $nomesMidia));

    // Comando SQL
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
        `midia` = '$midiaFinal',
        `status` = '$status'
        WHERE `idPacote` = '$id'";

    try {
        mysqli_query($conexao, $sql);
        $sucesso = true;
    } catch (mysqli_sql_exception $e) {
        $erro = "Erro ao alterar: " . $e->getMessage();
    }
}

$idBusca = mysqli_real_escape_string($conexao, $id);
// Busca o registro atual para preencher o formulário
$sql = "SELECT * FROM tbpacote WHERE idPacote = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$pacote = mysqli_fetch_array($resultado);

// Em caso de erro, mostra o que foi digitado em vez dos dados do banco
if ($erro !== "") {
    $pacote = array_merge($pacote, $_POST);
    $pacote['duracaoViagem'] = $_POST['duracao_viagem'] ?? '';
    $pacote['qtdParcelas']   = $_POST['parcelas'] ?? '';
}

$dataSaidaInput   = $pacote['dataHoraSaida'] ? date('Y-m-d\TH:i', strtotime($pacote['dataHoraSaida'])) : '';
$dataRetornoInput = $pacote['dataHoraRetorno'] ? date('Y-m-d\TH:i', strtotime($pacote['dataHoraRetorno'])) : '';

$duracaoTexto = '';
if ($pacote['duracaoViagem']) {
    [$horasTotal, $minutos] = array_map('intval', explode(':', $pacote['duracaoViagem']));
    $dias = intdiv($horasTotal, 24);
    $horasRestantes = $horasTotal % 24;
    $duracaoTexto = ($dias > 0 ? "$dias dia(s) " : '') . ($horasRestantes > 0 || $dias === 0 ? "$horasRestantes hora(s)" : '');
}

// Prepara lista de mídias já salvas para o JavaScript
$midiasIniciais = !empty($pacote['midia']) ? array_values(array_filter(explode(',', $pacote['midia']))) : [];
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

                <!-- ID do registro (campo oculto) -->
                <input type="hidden" name="id" value="<?= $pacote['idPacote'] ?>">

                <div class="row">
                    <!-- Coluna da esquerda -->
                    <div class="col-lg-8 col-md-12">
                        <!-- Card: Dados do pacote -->
                        <div class="card-principal">
                            <div class="card-principal-conteudo">
                                <label class="py-2">Nome do Pacote</label>
                                <input class="form-control" type="text" name="nomePacote"
                                    value="<?= htmlspecialchars($pacote['nomePacote']) ?>" required>

                                <label class="py-2">Descrição Curta</label>
                                <input class="form-control" type="text" name="descricaoCurta" id="descricaoCurta" maxlength="100"
                                    value="<?= htmlspecialchars($pacote['descricaoCurta']) ?>" >

                                <label class="py-2">Descrição Longa</label>
                                <textarea class="form-control" name="descricaoLonga" id="descricaoLonga" rows="5" ><?= htmlspecialchars($pacote['descricaoLonga']) ?></textarea>

                                <div class="mb-3">
                                    <label for="fileUpload" class="py-2">Conteúdo (Imagens ou Vídeos)</label>
                                    <input class="form-control" type="file" name="midia[]" id="fileUpload" accept="image/*,video/*" multiple>
                                    <div class="form-text">Adicione novos arquivos em etapas ou remova os existentes abaixo.</div>

                                    <!-- Inputs ocultos das mídias já salvas (gerenciados via JS) -->
                                    <div id="inputsMidiasExistentes"></div>

                                    <!-- Grade unificada de visualização (salvos + novos) -->
                                    <div id="listaArquivos" class="d-flex flex-wrap gap-2 mt-3"></div>
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

                    <!-- Coluna da direita -->
                    <div class="col-lg-4 col-md-12">
                        <!-- Card: Status e parceria -->
                        <div class="card-principal sticky-top">
                            <div class="card-principal-conteudo">
                                <strong><label>Status</label></strong>
                                <select class="form-select" name="status">
                                    <option value="Ativo" <?= $pacote['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                    <option value="Inativo" <?= $pacote['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                                    <option value="Finalizado" <?= $pacote['status'] === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                                </select>

                                <strong><label class="d-block pt-3">Pacote de Parceria</label></strong>
                                <?php
                                    $parceiroAtual = $pacote['pacoteParceiro'] ?? 'Não';
                                    $temParceria   = ($parceiroAtual !== 'Não' && $parceiroAtual !== '');
                                    $nomeParceiro  = ($parceiroAtual === 'Sim' || $parceiroAtual === 'Não') ? '' : $parceiroAtual;
                                ?>
                                <select class="form-select" id="selectParceria">
                                    <option value="Não" <?= !$temParceria ? 'selected' : '' ?>>Não</option>
                                    <option value="Sim" <?= $temParceria ? 'selected' : '' ?>>Sim</option>
                                </select>

                                <div id="qualParceria" class="pt-2 <?= $temParceria ? '' : 'd-none' ?>">
                                    <label class="py-2">Nome do Parceiro(a)</label>
                                    <input class="form-control" type="text" id="qualParceriaInput" placeholder="Nome do parceiro(a)" value="<?= htmlspecialchars($nomeParceiro) ?>">
                                </div>

                                <input type="hidden" id="pacoteParceiroHidden" name="pacoteParceiro" value="<?= htmlspecialchars($parceiroAtual) ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button name="salvar" style="background-color: #0b5ed7; color: white;" class="btn me-md-2" type="submit">Salvar</button>
                </div>
            </form>
        </section>

        <!-- GERENCIAMENTO DE MÍDIAS (EXISTENTES + NOVAS) -->
        <script>
            const fileUpload = document.getElementById('fileUpload');
            const listaArquivos = document.getElementById('listaArquivos');
            const inputsMidiasExistentes = document.getElementById('inputsMidiasExistentes');
            const caminhoUploads = '/ValdirTur/assets/uploads/';
            const extensoesVideo = ['mp4', 'mov', 'avi', 'webm'];

            // Lista de arquivos já salvos no banco + novos arquivos selecionados
            let midiasSalvas = <?= json_encode($midiasIniciais) ?>;
            let arquivosNovos = [];

            fileUpload.addEventListener('change', function() {
                for (const novoArquivo of this.files) {
                    const jaExiste = arquivosNovos.some(
                        arq => arq.name === novoArquivo.name && arq.size === novoArquivo.size
                    );
                    if (!jaExiste) {
                        arquivosNovos.push(novoArquivo);
                    }
                }
                renderizarTudo();
            });

            function removerMidiaSalva(index) {
                midiasSalvas.splice(index, 1);
                renderizarTudo();
            }

            function removerArquivoNovo(index) {
                arquivosNovos.splice(index, 1);
                renderizarTudo();
            }

            function criarCardMidia({ src, isVideo, nomeTexto, onRemove }) {
                const card = document.createElement('div');
                card.className = 'border rounded p-2 bg-white position-relative d-flex flex-column align-items-center';
                card.style.width = '120px';

                const btnRemover = document.createElement('button');
                btnRemover.type = 'button';
                btnRemover.className = 'btn btn-danger btn-sm position-absolute top-0 end-0 m-1 py-0 px-1';
                btnRemover.innerHTML = '&times;';
                btnRemover.onclick = onRemove;
                card.appendChild(btnRemover);

                if (isVideo) {
                    const icone = document.createElement('div');
                    icone.className = 'd-flex align-items-center justify-content-center bg-light rounded mb-1';
                    icone.style.width = '100px';
                    icone.style.height = '70px';
                    icone.innerHTML = '<i class="bi bi-film fs-2 text-secondary"></i>';
                    card.appendChild(icone);
                } else {
                    const img = document.createElement('img');
                    img.src = src;
                    img.style.width = '100px';
                    img.style.height = '70px';
                    img.style.objectFit = 'cover';
                    img.className = 'rounded mb-1';
                    card.appendChild(img);
                }

                const nome = document.createElement('small');
                nome.className = 'text-truncate w-100 text-center';
                nome.title = nomeTexto;
                nome.textContent = nomeTexto;
                card.appendChild(nome);

                return card;
            }

            function renderizarTudo() {
                // 1. Atualiza o <input type="file"> com os novos arquivos acumulados
                const dataTransfer = new DataTransfer();
                arquivosNovos.forEach(arquivo => dataTransfer.items.add(arquivo));
                fileUpload.files = dataTransfer.files;

                // 2. Atualiza os inputs ocultos das mídias já existentes mantidas
                inputsMidiasExistentes.innerHTML = '';
                midiasSalvas.forEach(nomeArquivo => {
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'midias_existentes[]';
                    hidden.value = nomeArquivo;
                    inputsMidiasExistentes.appendChild(hidden);
                });

                // 3. Renderiza todos os cards na mesma grade
                listaArquivos.innerHTML = '';

                // Renderiza mídias já salvas
                midiasSalvas.forEach((nomeArquivo, index) => {
                    const ext = nomeArquivo.split('.').pop().toLowerCase();
                    const isVideo = extensoesVideo.includes(ext);
                    const card = criarCardMidia({
                        src: caminhoUploads + nomeArquivo,
                        isVideo: isVideo,
                        nomeTexto: nomeArquivo,
                        onRemove: () => removerMidiaSalva(index)
                    });
                    listaArquivos.appendChild(card);
                });

                // Renderiza novos arquivos selecionados
                arquivosNovos.forEach((arquivo, index) => {
                    const isVideo = arquivo.type.startsWith('video/');
                    const card = criarCardMidia({
                        src: isVideo ? '' : URL.createObjectURL(arquivo),
                        isVideo: isVideo,
                        nomeTexto: arquivo.name,
                        onRemove: () => removerArquivoNovo(index)
                    });
                    listaArquivos.appendChild(card);
                });
            }

            // Carrega as imagens/vídeos existentes assim que a página abre
            renderizarTudo();
        </script>

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

                        let partes = [];

                        if (dias > 0) {
                            partes.push(`${dias} ${dias === 1 ? 'dia' : 'dias'}`);
                        }

                        if (horasRestantes > 0) {
                            partes.push(`${horasRestantes} ${horasRestantes === 1 ? 'hora' : 'horas'}`);
                        }

                        if (dias === 0 && horasRestantes === 0 && minutos > 0) {
                            partes.push(`${minutos} ${minutos === 1 ? 'minuto' : 'minutos'}`);
                        }

                        displayDuracao.value = partes.join(' e ');

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
                    valorParcelaDisplay.value = valorParcela.toLocaleString('pt-BR', {
                        style: 'currency',
                        currency: 'BRL'
                    });
                } else {
                    valorParcelaDisplay.value = '';
                }
            }
            inputPreco.addEventListener('input', calcularParcela);
            inputJuros.addEventListener('input', calcularParcela);
            inputParcelas.addEventListener('input', calcularParcela);
            calcularParcela();

            // Pacote de parceria
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