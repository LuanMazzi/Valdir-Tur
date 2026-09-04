<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

$sql = "SELECT * FROM tbpacote WHERE status = 'Ativo' ORDER BY dataHoraSaida ASC";
$resultado = mysqli_query($conexao, $sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Pacotes - Valdir Tur</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container">
        <div class="card-hero mt-3 mb-3">
            <div class="card-hero-content">
                Nossos Pacotes
            </div>
        </div>

        <?php if (mysqli_num_rows($resultado) === 0): ?>
            <p class="text-center text-muted">Nenhum pacote disponível no momento.</p>
        <?php else: ?>
            <?php while ($pacote = mysqli_fetch_array($resultado)): ?>
                <div class="container card overflow-hidden p-0 shadow-sm mt-3" style="border-radius: 26px;">
                    <div class="row align-items-center g-0">
                        <div class="col-md-5 p-4 p-lg-5">
                            <h1 class="display-5 fw-bold"><?= htmlspecialchars($pacote['nomePacote']) ?></h1>
                            <p class="lead"><?= htmlspecialchars($pacote['descricaoCurta']) ?></p>

                            <span class="badge rounded-pill bg-primary"><?= htmlspecialchars($pacote['destino']) ?></span>
                            <?php if ($pacote['dataHoraSaida']): ?>
                                <span class="badge rounded-pill bg-light text-dark border">
                                    <?= date('d/m/Y', strtotime($pacote['dataHoraSaida'])) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($pacote['pacoteParceiro'] !== 'Não'): ?>
                                <span class="badge rounded-pill bg-success">Parceria: <?= htmlspecialchars($pacote['pacoteParceiro']) ?></span>
                            <?php endif; ?>

                            <p class="fw-bold mt-3 mb-0">a partir de R$ <?= number_format((float) $pacote['preco'], 2, ',', '.') ?> por pessoa</p>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4 botao-onibus">
                                <a href="/ValdirTur/pacotes/pacote?id=<?= $pacote['idPacote'] ?>" class="btn btn-primary btn-lg px-4 me-md-2">Conhecer</a>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <?php $imgCapa = midiaPrimeiraImagem($pacote['midia']); ?>
                            <img src="<?= $imgCapa ? '/ValdirTur/assets/uploads/' . htmlspecialchars($imgCapa) : '/ValdirTur/assets/img/pacote-padrao.jpg' ?>"
                                class="img-fluid w-100 h-100" alt="<?= htmlspecialchars($pacote['nomePacote']) ?>"
                                style="height: 100%; min-height: 300px; object-fit: cover; display: block;">
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <br>

    <?php include(ROOT . '/includes/footer.php'); ?>
</body>

</html>
