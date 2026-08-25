<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

$id = mysqli_real_escape_string($conexao, $_GET['id'] ?? '');
$sql = "SELECT * FROM tbpacote WHERE idPacote = '$id' AND status = 'Ativo'";
$resultado = mysqli_query($conexao, $sql);
$pacote = mysqli_fetch_array($resultado);

// duracaoViagem vem do banco como "HH:MM:SS" (tipo TIME); monta um texto amigável
$duracaoTexto = '';
if ($pacote && $pacote['duracaoViagem']) {
    [$horasTotal, $minutos] = array_map('intval', explode(':', $pacote['duracaoViagem']));
    $dias = intdiv($horasTotal, 24);
    $horasRestantes = $horasTotal % 24;
    $duracaoTexto = ($dias > 0 ? "$dias dia(s) " : '') . ($horasRestantes > 0 || $dias === 0 ? "$horasRestantes hora(s)" : '');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title><?= $pacote ? htmlspecialchars($pacote['nomePacote']) . ' - Valdir Tur' : 'Pacote não encontrado - Valdir Tur' ?></title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container my-5">

        <?php if (!$pacote): ?>

            <div class="text-center py-5">
                <h2>Pacote não encontrado</h2>
                <p class="text-muted">Esse pacote pode não existir mais ou não estar disponível no momento.</p>
                <a href="/ValdirTur/pacotes" class="btn btn-primary mt-3">Voltar pra Pacotes</a>
            </div>

        <?php else: ?>

            <div class="card overflow-hidden shadow-sm" style="border-radius: 26px;">

                <?php if ($pacote['midia']): ?>
                    <img src="/ValdirTur/assets/uploads/<?= htmlspecialchars($pacote['midia']) ?>"
                        class="w-100" alt="<?= htmlspecialchars($pacote['nomePacote']) ?>"
                        style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <div class="w-100 d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 400px;">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4 p-lg-5">
                    <h1 class="fw-bold"><?= htmlspecialchars($pacote['nomePacote']) ?></h1>

                    <div class="mb-3">
                        <span class="badge rounded-pill bg-primary"><?= htmlspecialchars($pacote['destino']) ?></span>
                        <?php if ($pacote['pacoteParceiro'] !== 'Não'): ?>
                            <span class="badge rounded-pill bg-success">Parceria: <?= htmlspecialchars($pacote['pacoteParceiro']) ?></span>
                        <?php endif; ?>
                    </div>

                    <p class="lead"><?= nl2br(htmlspecialchars($pacote['descricaoLonga'])) ?></p>

                    <hr class="my-4">

                    <h4 class="mb-3">Detalhes da viagem</h4>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Local de Saída</strong><br><?= htmlspecialchars($pacote['localSaida']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Duração</strong><br><?= htmlspecialchars($duracaoTexto) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Saída</strong><br>
                            <?= $pacote['dataHoraSaida'] ? date('d/m/Y H:i', strtotime($pacote['dataHoraSaida'])) : '' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Retorno</strong><br>
                            <?= $pacote['dataHoraRetorno'] ? date('d/m/Y H:i', strtotime($pacote['dataHoraRetorno'])) : '' ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Vagas Disponíveis</strong><br><?= htmlspecialchars($pacote['vagasDisponiveis']) ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <p class="fs-4 fw-bold mb-4">A partir de R$ <?= number_format((float) $pacote['preco'], 2, ',', '.') ?> por pessoa</p>
                    <!-- <small class="fs-4 fw-bold mb-4"> Ou <?= $pacote['qtdParcelas']?> parcelas</small> -->

                    <div class="d-grid gap-2 d-md-flex">
                        <a href="/ValdirTur/contato.php" class="btn btn-primary btn-lg px-4">Reservar</a>
                        <a href="/ValdirTur/pacotes" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Voltar pra Pacotes
                        </a>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php include(ROOT . '/includes/footer.php'); ?>
</body>

</html>
