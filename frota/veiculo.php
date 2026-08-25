<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

$id = mysqli_real_escape_string($conexao, $_GET['id'] ?? '');
$sql = "SELECT * FROM tbVeiculo WHERE idVeiculo = '$id' AND status = 'Ativo'";
$resultado = mysqli_query($conexao, $sql);
$veiculo = mysqli_fetch_array($resultado);
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <title><?= $veiculo ? htmlspecialchars($veiculo['nomeIdentificacao']) . ' - Valdir Tur' : 'Veículo não encontrado - Valdir Tur' ?></title>
<?php include(__DIR__ . '/../includes/head.php'); ?>


<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container my-5">

        <?php if (!$veiculo): ?>

            <div class="text-center py-5">
                <h2>Veículo não encontrado</h2>
                <p class="text-muted">Esse veículo pode não existir mais ou não estar disponível no momento.</p>
                <a href="/ValdirTur/frota" class="btn btn-primary mt-3">Voltar pra Frota</a>
            </div>

        <?php else: ?>

            <div class="card overflow-hidden shadow-sm" style="border-radius: 26px;">

                <?php if ($veiculo['midia']): ?>
                    <img src="/ValdirTur/assets/uploads/<?= htmlspecialchars($veiculo['midia']) ?>"
                        class="w-100" alt="<?= htmlspecialchars($veiculo['nomeIdentificacao']) ?>"
                        style="height: 400px; object-fit: cover;">
                <?php else: ?>
                    <div class="w-100 d-flex align-items-center justify-content-center bg-light text-secondary" style="height: 400px;">
                        <i class="bi bi-image" style="font-size: 4rem;"></i>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4 p-lg-5">
                    <h1 class="fw-bold"><?= htmlspecialchars($veiculo['nomeIdentificacao']) ?></h1>

                    <?php if (!empty($veiculo['tags'])): ?>
                        <div class="mb-3">
                            <?php foreach (explode(',', $veiculo['tags']) as $tag): ?>
                                <span class="badge rounded-pill bg-primary"><?= htmlspecialchars(trim($tag)) ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <p class="lead"><?= nl2br(htmlspecialchars($veiculo['descricao'])) ?></p>

                    <hr class="my-4">

                    <h4 class="mb-3">Especificações</h4>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong>Tipo</strong><br><?= htmlspecialchars($veiculo['tipoVeiculo']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Ano</strong><br><?= htmlspecialchars($veiculo['ano']) ?>
                        </div>
                        <div class="col-md-4">
                            <strong>Capacidade total</strong><br><?= htmlspecialchars($veiculo['capacidadeTotal']) ?> lugares
                        </div>

                        <?php if (!empty($veiculo['tipoLeito'])): ?>
                            <div class="col-md-4">
                                <strong>Tipo de leito</strong><br><?= htmlspecialchars($veiculo['tipoLeito']) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($veiculo['capacidadePrimeiroAndar'])): ?>
                            <div class="col-md-4">
                                <strong>1º andar</strong><br>
                                <?= htmlspecialchars($veiculo['capacidadePrimeiroAndar']) ?> lugares
                                <?= !empty($veiculo['leitoPrimeiroAndar']) ? '(' . htmlspecialchars($veiculo['leitoPrimeiroAndar']) . ')' : '' ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($veiculo['capacidadeSegundoAndar'])): ?>
                            <div class="col-md-4">
                                <strong>2º andar</strong><br>
                                <?= htmlspecialchars($veiculo['capacidadeSegundoAndar']) ?> lugares
                                <?= !empty($veiculo['leitoSegundoAndar']) ? '(' . htmlspecialchars($veiculo['leitoSegundoAndar']) . ')' : '' ?>
                            </div>
                        <?php endif; ?>

                        
                    </div>

                    <a href="/ValdirTur/frota" class="btn btn-outline-secondary mt-4">
                        <i class="bi bi-arrow-left"></i> Voltar pra Frota
                    </a>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <?php include(ROOT . '/includes/footer.php'); ?>
</body>

</html>