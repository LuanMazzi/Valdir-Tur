<?php
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');
require_once(__DIR__ . '/../includes/galeria.php');

$id = mysqli_real_escape_string($conexao, $_GET['id'] ?? '');
$sql = "SELECT * FROM tbVeiculo WHERE idVeiculo = '$id' AND status = 'Ativo'";
$resultado = mysqli_query($conexao, $sql);
$veiculo = mysqli_fetch_array($resultado);

// Dois andares: só mostra o bloco "Distribuição" se o veículo tiver dados por andar
$doisAndares = $veiculo && (!empty($veiculo['capacidadePrimeiroAndar']) || !empty($veiculo['capacidadeSegundoAndar']));
?>

<!DOCTYPE html>
<html lang="pt-BR">

    <title><?= $veiculo ? htmlspecialchars($veiculo['nomeIdentificacao']) . ' - Valdir Tur' : 'Veículo não encontrado - Valdir Tur' ?></title>
<?php include(__DIR__ . '/../includes/head.php'); ?>


<body>
    <?php include(ROOT . '/includes/header.php'); ?>
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <div class="container my-4 my-lg-5">

        <?php if (!$veiculo): ?>

            <div class="text-center py-5">
                <h2>Veículo não encontrado</h2>
                <p class="text-muted">Esse veículo pode não existir mais ou não estar disponível no momento.</p>
                <a href="/ValdirTur/frota" class="btn btn-primary mt-3">Voltar pra Frota</a>
            </div>

        <?php else: ?>

            <!-- Caminho da página -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/ValdirTur/index.php">Início</a></li>
                    <li class="breadcrumb-item"><a href="/ValdirTur/frota">Frota</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($veiculo['nomeIdentificacao']) ?></li>
                </ol>
            </nav>

            <div class="row g-4 g-lg-5">

                <!-- Coluna da esquerda: galeria, descrição e distribuição dos lugares -->
                <div class="col-lg-7">
                    <?php galeriaMidia(midiaLista($veiculo['midia']), $veiculo['nomeIdentificacao'], 'galeriaVeiculo'); ?>

                    <?php if (!empty($veiculo['descricao'])): ?>
                        <h2 class="h4 mt-4">Sobre o veículo</h2>
                        <p class="text-secondary"><?= nl2br(htmlspecialchars($veiculo['descricao'])) ?></p>
                    <?php endif; ?>

                    <?php if ($doisAndares): ?>
                        <h2 class="h4 mt-4">Distribuição dos lugares</h2>
                        <div class="row g-3">
                            <?php foreach ([['1º andar', 'capacidadePrimeiroAndar', 'leitoPrimeiroAndar'], ['2º andar', 'capacidadeSegundoAndar', 'leitoSegundoAndar']] as [$rotulo, $capacidade, $leito]): ?>
                                <?php if (!empty($veiculo[$capacidade])): ?>
                                    <div class="col-sm-6">
                                        <div class="border rounded-3 p-3 h-100">
                                            <div class="small text-secondary"><?= $rotulo ?></div>
                                            <div class="fs-4 fw-bold"><?= htmlspecialchars($veiculo[$capacidade]) ?> lugares</div>
                                            <?php if (!empty($veiculo[$leito])): ?>
                                                <span class="badge rounded-pill bg-light text-dark border"><?= htmlspecialchars($veiculo[$leito]) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Coluna da direita: resumo fixo -->
                <div class="col-lg-5">
                    <div class="sticky-lg-top" style="top: 1.5rem;">
                        <div class="bg-white border rounded-3 shadow-sm p-4">

                            <h1 class="h3 fw-bold"><?= htmlspecialchars($veiculo['nomeIdentificacao']) ?></h1>

                            <!-- Comodidades -->
                            <?php if (!empty($veiculo['tags'])): ?>
                                <!-- Mesmas etiquetas dos cartões da lista da frota -->
                                <div class="mt-2">
                                    <?php foreach (explode(',', $veiculo['tags']) as $tag): ?>
                                        <span class="badge rounded-pill bg-primary"><?= htmlspecialchars(trim($tag)) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Capacidade em destaque -->
                            <div class="mt-3">
                                <span class="text-secondary small">Capacidade</span>
                                <div class="fs-2 fw-bold lh-1"><?= htmlspecialchars($veiculo['capacidadeTotal']) ?> <span class="fs-5 fw-normal">lugares</span></div>
                            </div>

                            <hr>

                            <!-- Especificações -->
                            <ul class="list-unstyled mb-0 d-grid gap-3">
                                <li class="d-flex gap-3">
                                    <i class="bi bi-bus-front text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Tipo</div>
                                        <?= htmlspecialchars($veiculo['tipoVeiculo']) ?>
                                    </div>
                                </li>
                                <li class="d-flex gap-3">
                                    <i class="bi bi-calendar3 text-primary fs-5"></i>
                                    <div>
                                        <div class="small text-secondary">Ano</div>
                                        <?= htmlspecialchars($veiculo['ano']) ?>
                                    </div>
                                </li>
                                <?php if (!empty($veiculo['tipoLeito'])): ?>
                                    <li class="d-flex gap-3">
                                        <i class="bi bi-moon-stars text-primary fs-5"></i>
                                        <div>
                                            <div class="small text-secondary">Tipo de leito</div>
                                            <?= htmlspecialchars($veiculo['tipoLeito']) ?>
                                        </div>
                                    </li>
                                <?php endif; ?>
                            </ul>

                            <!-- Ação -->
                            <div class="d-grid gap-2 mt-4">
                                <a href="/ValdirTur/contato.php" class="btn btn-primary btn-lg">Pedir orçamento</a>
                                <a href="/ValdirTur/frota" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Ver toda a frota</a>
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
