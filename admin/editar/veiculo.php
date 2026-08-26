<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

$erro = "";
$sucesso = false;
$id = $_POST['id'] ?? $_GET['id'] ?? '';

// Se o formulário foi enviado, faz o UPDATE
if (isset($_POST['salvar'])) {

    $id                      = mysqli_real_escape_string($conexao, $_POST['id'] ?? '');
    $tipoVeiculo             = mysqli_real_escape_string($conexao, $_POST['tipoVeiculo'] ?? '');
    $nomeIdentificacao       = mysqli_real_escape_string($conexao, $_POST['nomeIdentificacao'] ?? '');
    $numeracao               = mysqli_real_escape_string($conexao, $_POST['numeracao'] ?? '');
    $capacidadeTotal         = mysqli_real_escape_string($conexao, $_POST['capacidadeTotal'] ?? '');
    $capacidadePrimeiroAndar = mysqli_real_escape_string($conexao, $_POST['capacidadePrimeiroAndar'] ?? '');
    $capacidadeSegundoAndar  = mysqli_real_escape_string($conexao, $_POST['capacidadeSegundoAndar'] ?? '');
    $descricao               = mysqli_real_escape_string($conexao, $_POST['descricao'] ?? '');
    $tags                    = mysqli_real_escape_string($conexao, $_POST['tags'] ?? '');
    $ano                     = mysqli_real_escape_string($conexao, $_POST['ano'] ?? '');
    $placa                   = mysqli_real_escape_string($conexao, strtoupper($_POST['placa'] ?? ''));
    $tipoLeito               = mysqli_real_escape_string($conexao, $_POST['tipoLeito'] ?? '');
    $leitoPrimeiroAndar      = mysqli_real_escape_string($conexao, $_POST['leitoPrimeiroAndar'] ?? '');
    $leitoSegundoAndar       = mysqli_real_escape_string($conexao, $_POST['leitoSegundoAndar'] ?? '');
    $status                  = mysqli_real_escape_string($conexao, $_POST['status'] ?? '');

    // Upload de mídia (opcional). Só troca se enviarem um arquivo novo.
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $novaMidiaSql = ""; // se continuar vazio, o UPDATE não mexe na coluna `midia`

    if (isset($_FILES['midia']) && $_FILES['midia']['error'] === UPLOAD_ERR_OK) {
        $nomeOriginal = basename($_FILES['midia']['name']);
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (in_array($extensao, $extensoesPermitidas, true)) {
            $nomeSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeOriginal);
            $nomeMidia  = time() . "_" . $nomeSeguro;
            $diretorio  = __DIR__ . '/../../assets/uploads/';
            move_uploaded_file($_FILES['midia']['tmp_name'], $diretorio . $nomeMidia);
            $novaMidiaSql = "`midia` = '$nomeMidia',";
        } else {
            $erro = "Tipo de arquivo não permitido. Envie imagem ou vídeo.";
        }
    }

    if ($erro === "") {
        // Campos opcionais: se vier vazio, grava NULL no banco em vez de string vazia
        $capacidadePrimeiroAndarSql = $capacidadePrimeiroAndar === '' ? "NULL" : "'$capacidadePrimeiroAndar'";
        $capacidadeSegundoAndarSql  = $capacidadeSegundoAndar === '' ? "NULL" : "'$capacidadeSegundoAndar'";
        $tipoLeitoSql               = $tipoLeito === '' ? "NULL" : "'$tipoLeito'";
        $leitoPrimeiroAndarSql      = $leitoPrimeiroAndar === '' ? "NULL" : "'$leitoPrimeiroAndar'";
        $leitoSegundoAndarSql       = $leitoSegundoAndar === '' ? "NULL" : "'$leitoSegundoAndar'";

        $sql = "UPDATE `tbVeiculo` SET
            `tipoVeiculo` = '$tipoVeiculo',
            `nomeIdentificacao` = '$nomeIdentificacao',
            `numeracao` = '$numeracao',
            `capacidadeTotal` = '$capacidadeTotal',
            `capacidadePrimeiroAndar` = $capacidadePrimeiroAndarSql,
            `capacidadeSegundoAndar` = $capacidadeSegundoAndarSql,
            $novaMidiaSql
            `descricao` = '$descricao',
            `tags` = '$tags',
            `ano` = '$ano',
            `placa` = '$placa',
            `tipoLeito` = $tipoLeitoSql,
            `leitoPrimeiroAndar` = $leitoPrimeiroAndarSql,
            `leitoSegundoAndar` = $leitoSegundoAndarSql,
            `status` = '$status'
            WHERE `idVeiculo` = '$id'";

        try {
            mysqli_query($conexao, $sql);
            $sucesso = true; // sucesso, segue e recarrega os dados atualizados abaixo
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $erro = "Já existe outro veículo cadastrado com essa placa.";
            } else {
                $erro = "Erro ao alterar: " . $e->getMessage();
            }
        }
    }
}

// Busca os dados atuais do veículo, pra preencher o formulário
$idBusca = mysqli_real_escape_string($conexao, $id);
$sql = "SELECT * FROM tbVeiculo WHERE idVeiculo = '$idBusca'";
$resultado = mysqli_query($conexao, $sql);
$veiculo = mysqli_fetch_array($resultado);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Alterar veículo</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <form method="POST" enctype="multipart/form-data">
            <button type="button" class="botao-voltar" onclick="window.location.href='../listas/veiculos.php'">
                <i class="bi bi-bus-front-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Alterar veículo</strong></label>

            <?php if ($sucesso): ?>
                <div class="alert alert-success">Edição feita com sucesso!</div>
            <?php endif; ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <!-- Campo escondido: carrega o ID do veículo junto no POST -->
            <input type="hidden" name="id" value="<?= $veiculo['idVeiculo'] ?>">

            <div class="row">
                <div class="col-lg-8 col-md-12"> <!-- Coluna da esquerda -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome de identificação</label>
                            <input class="form-control" type="text" name="nomeIdentificacao"
                                value="<?= htmlspecialchars($veiculo['nomeIdentificacao']) ?>" required>

                            <label class="py-2">Numeração</label>
                            <input class="form-control" type="number" name="numeracao"
                                value="<?= htmlspecialchars($veiculo['numeracao']) ?>" required>

                            <div class="mb-3">
                                <label class="py-2">Descrição</label>
                                <textarea class="form-control" name="descricao" id="descricao" rows="3"><?= htmlspecialchars($veiculo['descricao']) ?></textarea>
                            </div>

                            <label class="py-1">Mídia (Imagem ou Vídeo)</label>
                            <div class="upload-container">
                                <div class="upload-area d-flex flex-wrap align-items-center justify-content-center p-3">
                                    <input type="file" name="midia" id="fileUpload" class="d-none" accept="image/*,video/*">
                                    <label for="fileUpload" class="text-center w-100 py-4 cursor-pointer border border-secondary border-dashed">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <span class="d-block text-secondary" id="fileUploadLabel">
                                            <?= $veiculo['midia'] ? htmlspecialchars($veiculo['midia']) . ' (clique para trocar)' : 'Clique para selecionar' ?>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Informações</label></strong> <br>

                            <div class="row g-4 align-items-end">
                                <div class="col-auto">
                                    <label class="d-block py-2">Tipo de Veículo</label>
                                    <select class="form-select" name="tipoVeiculo" required>
                                        <option value="" disabled>Selecione</option>
                                        <option value="Ônibus" <?= $veiculo['tipoVeiculo'] === 'Ônibus' ? 'selected' : '' ?>>Ônibus</option>
                                        <option value="Micro-ônibus" <?= $veiculo['tipoVeiculo'] === 'Micro-ônibus' ? 'selected' : '' ?>>Micro-ônibus</option>
                                        <option value="Van" <?= $veiculo['tipoVeiculo'] === 'Van' ? 'selected' : '' ?>>Van</option>
                                        <option value="Carro" <?= $veiculo['tipoVeiculo'] === 'Carro' ? 'selected' : '' ?>>Carro</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Capacidade total</label>
                                    <input style="width: 100px;" class="form-control" type="number"
                                        name="capacidadeTotal" value="<?= htmlspecialchars($veiculo['capacidadeTotal']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Tipo de leito</label>
                                    <select class="form-select" name="tipoLeito">
                                        <option value="" <?= empty($veiculo['tipoLeito']) ? 'selected' : '' ?>>Selecione</option>
                                        <option value="Convencional" <?= $veiculo['tipoLeito'] === 'Convencional' ? 'selected' : '' ?>>Convencional</option>
                                        <option value="Semi-leito" <?= $veiculo['tipoLeito'] === 'Semi-leito' ? 'selected' : '' ?>>Semi-leito</option>
                                        <option value="Leito" <?= $veiculo['tipoLeito'] === 'Leito' ? 'selected' : '' ?>>Leito</option>
                                        <option value="Leito Cama" <?= $veiculo['tipoLeito'] === 'Leito Cama' ? 'selected' : '' ?>>Leito Cama</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Ano</label>
                                    <input style="width: 100px;" class="form-control" type="number" name="ano"
                                        value="<?= htmlspecialchars($veiculo['ano']) ?>" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Placa</label>
                                    <input style="width: 100px;" class="form-control" type="text" name="placa"
                                        value="<?= htmlspecialchars($veiculo['placa']) ?>" maxlength="7" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <small class="text-muted">Preencha apenas se o veículo tiver 2 andares</small>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6 border-end">
                                    <small class="text-muted">1º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadePrimeiroAndar" value="<?= htmlspecialchars($veiculo['capacidadePrimeiroAndar'] ?? '') ?>" placeholder="Cap.">
                                        <select class="form-select" name="leitoPrimeiroAndar">
                                            <option value="" <?= empty($veiculo['leitoPrimeiroAndar']) ? 'selected' : '' ?>>Leito 1º andar</option>
                                            <option value="Convencional" <?= $veiculo['leitoPrimeiroAndar'] === 'Convencional' ? 'selected' : '' ?>>Convencional</option>
                                            <option value="Semi-Leito" <?= $veiculo['leitoPrimeiroAndar'] === 'Semi-Leito' ? 'selected' : '' ?>>Semi-Leito</option>
                                            <option value="Leito" <?= $veiculo['leitoPrimeiroAndar'] === 'Leito' ? 'selected' : '' ?>>Leito</option>
                                            <option value="Leito Cama" <?= $veiculo['leitoPrimeiroAndar'] === 'Leito Cama' ? 'selected' : '' ?>>Leito Cama</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">2º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadeSegundoAndar" value="<?= htmlspecialchars($veiculo['capacidadeSegundoAndar'] ?? '') ?>" placeholder="Cap.">
                                        <select class="form-select" name="leitoSegundoAndar">
                                            <option value="" <?= empty($veiculo['leitoSegundoAndar']) ? 'selected' : '' ?>>Leito 2º andar</option>
                                            <option value="Convencional" <?= $veiculo['leitoSegundoAndar'] === 'Convencional' ? 'selected' : '' ?>>Convencional</option>
                                            <option value="Semi-Leito" <?= $veiculo['leitoSegundoAndar'] === 'Semi-Leito' ? 'selected' : '' ?>>Semi-Leito</option>
                                            <option value="Leito" <?= $veiculo['leitoSegundoAndar'] === 'Leito' ? 'selected' : '' ?>>Leito</option>
                                            <option value="Leito Cama" <?= $veiculo['leitoSegundoAndar'] === 'Leito Cama' ? 'selected' : '' ?>>Leito Cama</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-lg-4 col-md-12"> <!-- Coluna da direita -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status" required>
                                <option value="Ativo" <?= $veiculo['status'] === 'Ativo' ? 'selected' : '' ?>>Ativo</option>
                                <option value="Inativo" <?= $veiculo['status'] === 'Inativo' ? 'selected' : '' ?>>Inativo</option>
                            </select>

                            <div class="mb-3">
                                <label class="form-label pt-2">Tags</label>
                                <div class="tag-container form-control d-flex flex-wrap align-items-center"
                                    id="tagContainer">
                                    <input type="text" id="tagInput" placeholder="Adicionar tags"
                                        class="border-0 flex-grow-1" style="outline: none; min-width: 120px; background: transparent; color: black;">
                                </div>
                                <!-- Campo oculto: já vem preenchido com as tags salvas -->
                                <input type="hidden" name="tags" id="tagsHidden" value="<?= htmlspecialchars($veiculo['tags'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button style="background-color: #0b5ed7; color: white;" class="btn me-md-2" name="salvar"
                    type="submit">Salvar</button>
            </div>
        </form>
    </section>

    <script src="/ValdirTur/assets/js/script.js"></script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>
