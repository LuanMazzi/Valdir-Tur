<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');
require_once(__DIR__ . '/../includes/galeria.php');

$id = mysqli_real_escape_string($conexao, $_GET['id'] ?? '');
$sql = "SELECT * FROM tbpacote WHERE idPacote = '$id' AND status = 'Ativo'";
$resultado = mysqli_query($conexao, $sql);
$pacote = mysqli_fetch_array($resultado);

// Converte a duração (HH:MM:SS) em texto
$duracaoTexto = '';
if ($pacote && $pacote['duracaoViagem']) {
    [$horasTotal, $minutos] = array_map('intval', explode(':', $pacote['duracaoViagem']));
    $dias = intdiv($horasTotal, 24);
    $horasRestantes = $horasTotal % 24;
    $duracaoTexto = ($dias > 0 ? "$dias dia(s) " : '') . ($horasRestantes > 0 || $dias === 0 ? "$horasRestantes hora(s)" : '');
}

// Vagas, valor da parcela e se a viagem já passou
if ($pacote) {
    $vagas = (int) $pacote['vagasDisponiveis'];
    $preco = (float) $pacote['preco'];
    $parcelas = (int) $pacote['qtdParcelas'];
    $juros = (float) $pacote['juros'];
    $valorParcela = $parcelas > 1 ? $preco * (1 + $juros / 100) / $parcelas : 0;
    $jaPassou = $pacote['dataHoraSaida'] && strtotime($pacote['dataHoraSaida']) < time();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title><?= $pacote ? htmlspecialchars($pacote['nomePacote']) . ' - Valdir Tur' : 'Pacote não encontrado - Valdir Tur' ?></title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container my-4 my-lg-5">

        <?php if (!$pacote): ?>

            <div class="text-center py-5">
                <h2>Pacote não encontrado</h2>
                <p class="text-muted">Esse pacote pode não existir mais ou não estar disponível no momento.</p>
                <a href="/ValdirTur/pacotes" class="btn btn-primary mt-3">Voltar pra Pacotes</a>
            </div>

        <?php else: ?>

            <!-- Caminho da página -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/ValdirTur/index.php">Início</a></li>
                    <li class="breadcrumb-item"><a href="/ValdirTur/pacotes">Pacotes</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($pacote['nomePacote']) ?></li>
                </ol>
            </nav>

            <div class="row g-4 g-lg-5">

                <!-- Coluna da esquerda: galeria e descrição -->
                <div class="col-lg-7">
                    <?php galeriaMidia(midiaLista($pacote['midia']), $pacote['nomePacote'], 'galeriaPacote'); ?>

                    <?php if (trim((string) $pacote['descricaoLonga']) !== ''): ?>
                        <h2 class="h4 mt-4">Sobre a viagem</h2>
                        <p class="text-secondary"><?= nl2br(htmlspecialchars($pacote['descricaoLonga'])) ?></p>
                    <?php endif; ?>

                    <!-- Mapa do destino (só ilustrativo) -->
                    <?php if (trim((string) $pacote['destino']) !== ''): ?>
                        <h2 class="h4 mt-4">Destino no mapa</h2>
                        <div class="ratio ratio-16x9 rounded-3 overflow-hidden border" style="max-height: 450px;">
                            <iframe src="https://www.google.com/maps?q=<?= urlencode($pacote['destino']) ?>&output=embed"
                                loading="lazy" title="Mapa de <?= htmlspecialchars($pacote['destino']) ?>"
                                referrerpolicy="no-referrer-when-downgrade" style="border: 0;"></iframe>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Coluna da direita: resumo fixo -->
                <div class="col-lg-5">
                    <div class="sticky-lg-top" style="top: 1.5rem;">
                        <div class="bg-white border rounded-3 shadow-sm p-4">

                            <!-- Mesmas etiquetas dos cartões da lista de pacotes -->
                            <div class="mb-2">
                                <span class="badge rounded-pill bg-primary"><?= htmlspecialchars($pacote['destino']) ?></span>
                                <?php if ($pacote['dataHoraSaida']): ?>
                                    <span class="badge rounded-pill bg-light text-dark border">
                                        <?= date('d/m/Y', strtotime($pacote['dataHoraSaida'])) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($pacote['pacoteParceiro'] !== 'Não'): ?>
                                    <span class="badge rounded-pill bg-success">Parceria: <?= htmlspecialchars($pacote['pacoteParceiro']) ?></span>
                                <?php endif; ?>
                            </div>

                            <h1 class="h3 fw-bold"><?= htmlspecialchars($pacote['nomePacote']) ?></h1>

                            <!-- Preço -->
                            <div class="mt-3">
                                <span class="text-secondary small">A partir de</span>
                                <div class="fs-2 fw-bold lh-1">R$ <?= number_format($preco, 2, ',', '.') ?></div>
                                <div class="text-secondary small mt-1">
                                    por pessoa<?php if ($valorParcela > 0): ?>
                                    · ou <?= $parcelas ?>x de R$ <?= number_format($valorParcela, 2, ',', '.') ?><?= $juros > 0 ? ' com juros' : ' sem juros' ?>
                                <?php endif; ?>
                                </div>
                            </div>

                            <hr>

                            <!-- Dados rápidos da viagem -->
                            <ul class="list-unstyled mb-0 d-grid gap-3">
                                <li class="d-flex gap-3">
                                    <i class="bi bi-calendar-event text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Saída</div>
                                        <?= $pacote['dataHoraSaida'] ? date('d/m/Y \à\s H:i', strtotime($pacote['dataHoraSaida'])) : 'A definir' ?>
                                    </div>
                                </li>
                                <li class="d-flex gap-3">
                                    <i class="bi bi-calendar-check text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Retorno</div>
                                        <?= $pacote['dataHoraRetorno'] ? date('d/m/Y \à\s H:i', strtotime($pacote['dataHoraRetorno'])) : 'A definir' ?>
                                    </div>



                                </li>

                                <li class="d-flex gap-3">
                                    <i class="bi bi-clock text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Duração da Viagem</div>
                                        <?php if ($duracaoTexto): ?><?= htmlspecialchars($duracaoTexto) ?><?php endif; ?>
                                    </div>

                                </li>

                                <li class="d-flex gap-3">
                                    <i class="bi bi-signpost-split text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Local(is) de saída</div>
                                        <?= htmlspecialchars($pacote['locaisEmbarque']) ?>
                                    </div>
                                </li>
                                <li class="d-flex gap-3">
                                    <i class="bi bi-people text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Vagas</div>
                                        <?php if ($vagas <= 0): ?>
                                            <span class="text-danger fw-semibold">Esgotadas</span>
                                        <?php elseif ($vagas <= 10): ?>
                                            <span class="text-warning-emphasis fw-semibold">Restam só <?= $vagas ?></span>
                                        <?php else: ?>
                                            <?= $vagas ?> disponíveis
                                        <?php endif; ?>
                                    </div>
                                </li>
                            </ul>

                            <!-- Ação -->
                            <div class="d-grid mt-4">
                                <?php if ($jaPassou): ?>
                                    <button class="btn btn-secondary btn-lg" type="button" disabled>Viagem já realizada</button>
                                <?php elseif ($vagas <= 0): ?>
                                    <button class="btn btn-secondary btn-lg" type="button" disabled>Vagas esgotadas</button>
                                <?php else: ?>
                                    <a href="/ValdirTur/contato.php" class="btn btn-primary btn-lg">Reservar minha vaga</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <?php include(ROOT . '/includes/footer.php'); ?>
</body>

</html>