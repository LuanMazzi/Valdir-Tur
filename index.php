<?php
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/config/conexao.php');

$sql = "SELECT * FROM tbpacote WHERE status = 'Ativo' ORDER BY dataHoraSaida ASC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<title>Valdir Tur</title>
<?php include(__DIR__ . '/includes/head.php'); ?>

<style>
    .card {
        border-radius: 20px;
    }
</style>

<body>
    <?php include 'includes/header.php'; ?>
    <script src="/ValdirTur/bootstrap/js/bootstrap.bundle.min.js"></script>

    <br>

    <?php if (isset($_GET['mensagem'])) { ?>
        <div class=" container alert alert-danger mt-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?= $_GET['mensagem'] ?>
        </div>
    <?php } ?>

    <div class="container card overflow-hidden p-0 shadow-sm">
        <div class="row align-items-center g-0">
            <div class="col-md-9 p-4 p-lg-5">
                <h1 class="display-5 fw-bold">Viaje com Conforto e Segurança, <br> a sua jornada começa aqui.</h1>
                <p class="lead">A Valdir Tur oferece as melhores experiências em turismo rodoviário, com frotas
                    modernas, motoristas experientes e roteiros inesquecíveis por todo o Brasil.</p>
                <div class="d-grid gap-2 d-md-flex justify-content-md-start mt-4">
                    <a href="pacotes/pacotes.php" class="btn btn-primary btn-lg px-4 me-md-2">Ver Pacotes</a>
                    <a href="contato.php" class="btn btn-outline-secondary btn-lg px-4">Falar Conosco</a>
                </div>
            </div>
            <div class="col-md-3">
                <img src="./assets/img/foto-capa-onibus.png" class="img-fluid w-100" alt="Frota Valdir Tur"
                    style="height: auto; object-fit: cover; display: block;">
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h1 class="mb-4 text-center titulo-personalizado">Nossos Pacotes</h1>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php if (mysqli_num_rows($resultado) === 0): ?>
                <p class="text-center text-muted">Nenhum pacote disponível no momento.</p>
            <?php else: ?>
                <?php while ($pacote = mysqli_fetch_array($resultado)): ?>
                    <a href="/ValdirTur/pacotes/pacote?id=<?= $pacote['idPacote'] ?>"
                        class="col text-decoration-none text-reset">
                        <div class="card h-100">
                            <?php $imgCapa = midiaPrimeiraImagem($pacote['midia']); ?>
                            <img src="<?= $imgCapa ? '/ValdirTur/assets/uploads/' . htmlspecialchars($imgCapa) : '/ValdirTur/assets/img/pacote-padrao.jpg' ?>"
                                class="card-img-top" alt="<?= htmlspecialchars($pacote['nomePacote']) ?>"
                                style="height: 200px; object-fit: cover;">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($pacote['nomePacote']) ?></h5>
                                <p class="card-text"><?= htmlspecialchars($pacote['descricaoCurta']) ?></p>
                                <p class="fw-bold mt-auto mb-0">a partir de R$
                                    <?= number_format((float) $pacote['preco'], 2, ',', '.') ?> por pessoa</p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <br>


    <a href="login.php" class="btn btn-primary"> login </a>

    <?php include 'includes/footer.php'; ?>
</body>

</html>