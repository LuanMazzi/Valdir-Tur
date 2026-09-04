<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

$sql = "SELECT * FROM tbVeiculo WHERE status = 'Ativo' ORDER BY idVeiculo ASC";
$resultado = mysqli_query($conexao, $sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
 <title>Frota - Valdir Tur</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container">
        <div class="card-hero mt-3 mb-3">
            <div class="card-hero-content">
                Nossa Frota
            </div>
        </div>

        <?php if (mysqli_num_rows($resultado) === 0): ?>
            <p class="text-center text-muted">Nenhum veículo disponível.</p>
        <?php else: ?>
            <?php while ($veiculo = mysqli_fetch_array($resultado)): ?>
                <div class="container card overflow-hidden p-0 shadow-sm mt-3" style="border-radius: 26px;">
                    <div class="row align-items-center g-0">
                        <div class="col-md-5 p-4 p-lg-5">
                            <h1 class="display-5 fw-bold"><?= htmlspecialchars($veiculo['nomeIdentificacao']) ?></h1>
                            <p class="lead"><?= htmlspecialchars($veiculo['descricao']) ?></p>

                            <?php if (!empty($veiculo['tags'])): ?>
                                <?php foreach (explode(',', $veiculo['tags']) as $tag): ?>
                                    <span class="badge rounded-pill bg-primary"><?= htmlspecialchars(trim($tag)) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4 botao-onibus">
                                <a href="/ValdirTur/frota/veiculo?id=<?= $veiculo['idVeiculo'] ?>" class="btn btn-primary btn-lg px-4 me-md-2">Conhecer</a>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <?php $imgCapa = midiaPrimeiraImagem($veiculo['midia']); ?>
                            <img src="<?= $imgCapa ? '/ValdirTur/assets/uploads/' . htmlspecialchars($imgCapa) : '/ValdirTur/assets/img/veiculo-padrao.jpg' ?>"
                                class="img-fluid w-100 h-100" alt="<?= htmlspecialchars($veiculo['nomeIdentificacao']) ?>"
                                style="height: 100%; min-height: 300px; object-fit: cover; display: block;">
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php include(ROOT . '/includes/footer.php'); ?>
</body>

</html>