<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');

$erro = "";
$mensagem = "";
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Veículo cadastrado com sucesso!</div>";
}

if (isset($_POST['salvar'])) {

    $tipoVeiculo             = mysqli_real_escape_string($conexao, $_POST['tipoVeiculo']);
    $nomeIdentificacao       = mysqli_real_escape_string($conexao, $_POST['nomeIdentificacao']);
    $numeracao               = mysqli_real_escape_string($conexao, $_POST['numeracao']);
    $capacidadeTotal         = mysqli_real_escape_string($conexao, $_POST['capacidadeTotal']);
    $capacidadePrimeiroAndar = mysqli_real_escape_string($conexao, $_POST['capacidadePrimeiroAndar'] ?? '');
    $capacidadeSegundoAndar  = mysqli_real_escape_string($conexao, $_POST['capacidadeSegundoAndar'] ?? '');
    $descricao               = mysqli_real_escape_string($conexao, $_POST['descricao']);
    $tags                    = mysqli_real_escape_string($conexao, $_POST['tags']);
    $ano                     = mysqli_real_escape_string($conexao, $_POST['ano']);
    $placa                   = mysqli_real_escape_string($conexao, strtoupper($_POST['placa']));
    $tipoLeito               = mysqli_real_escape_string($conexao, $_POST['tipoLeito'] ?? '');
    $leitoPrimeiroAndar      = mysqli_real_escape_string($conexao, $_POST['leitoPrimeiroAndar'] ?? '');
    $leitoSegundoAndar       = mysqli_real_escape_string($conexao, $_POST['leitoSegundoAndar'] ?? '');
    $status                  = mysqli_real_escape_string($conexao, $_POST['status']);

    // Upload de mídia (opcional, vários arquivos). Cada nome salvo vira um item
    // da lista separada por vírgula guardada na coluna `midia`.
    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $nomesMidia = [];
    $diretorio  = __DIR__ . '/../assets/uploads/';

    if (isset($_FILES['midia']) && is_array($_FILES['midia']['name'])) {
        foreach ($_FILES['midia']['name'] as $i => $nomeOriginal) {
            if ($_FILES['midia']['error'][$i] !== UPLOAD_ERR_OK || $nomeOriginal === '') {
                continue;
            }

            $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

            if (!in_array($extensao, $extensoesPermitidas, true)) {
                $erro = "Tipo de arquivo não permitido. Envie apenas imagens ou vídeos.";
                break;
            }

            $nomeSeguro = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($nomeOriginal));
            $nomeSalvo  = time() . "_" . $i . "_" . $nomeSeguro;
            move_uploaded_file($_FILES['midia']['tmp_name'][$i], $diretorio . $nomeSalvo);
            $nomesMidia[] = $nomeSalvo;
        }
    }

    $nomeMidia = $nomesMidia ? implode(',', $nomesMidia) : null;

    if ($erro === "") {
        // Campos opcionais: se vier vazio, grava NULL no banco em vez de string vazia
        $capacidadePrimeiroAndar = $capacidadePrimeiroAndar === '' ? "NULL" : "'$capacidadePrimeiroAndar'";
        $capacidadeSegundoAndar  = $capacidadeSegundoAndar === '' ? "NULL" : "'$capacidadeSegundoAndar'";
        $tipoLeito               = $tipoLeito === '' ? "NULL" : "'$tipoLeito'";
        $leitoPrimeiroAndar      = $leitoPrimeiroAndar === '' ? "NULL" : "'$leitoPrimeiroAndar'";
        $leitoSegundoAndar       = $leitoSegundoAndar === '' ? "NULL" : "'$leitoSegundoAndar'";
        $nomeMidia               = $nomeMidia === null ? "NULL" : "'$nomeMidia'";

        $sql = "INSERT INTO `tbVeiculo` (
            `tipoVeiculo`, `nomeIdentificacao`, `numeracao`, `capacidadeTotal`,
            `capacidadePrimeiroAndar`, `capacidadeSegundoAndar`, `midia`, `descricao`,
            `tags`, `ano`, `placa`, `tipoLeito`, `leitoPrimeiroAndar`, `leitoSegundoAndar`, `status`
        ) VALUES (
            '$tipoVeiculo', '$nomeIdentificacao', '$numeracao', '$capacidadeTotal',
            $capacidadePrimeiroAndar, $capacidadeSegundoAndar, $nomeMidia, '$descricao',
            '$tags', '$ano', '$placa', $tipoLeito, $leitoPrimeiroAndar, $leitoSegundoAndar, '$status'
        )";

        try {
            mysqli_query($conexao, $sql);
            // Redireciona pra evitar reenvio do formulário (e reenvio do upload) ao atualizar a página (F5)
            header('Location: veiculo.php?sucesso=1');
            exit;
        } catch (mysqli_sql_exception $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                $erro = "Já existe um veículo cadastrado com essa placa.";
            } else {
                $erro = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Cadastro de veículo</title>
<?php include(__DIR__ . '/../../includes/head.php'); ?>

<body class="d-flex flex-nowrap" style="background-color: #f1f1f1">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../../includes/sidebar-admin.php'); ?>

    <main class="w-100 p-4" style="overflow-y: auto;">
    <section class="container py-5">

        <form method="POST" enctype="multipart/form-data">
            <button class="botao-voltar" onclick="window.location.href='../listas/veiculos.php'">
                <i class="bi bi-bus-front-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Adicionar veículo</strong></label>

            <?= $mensagem ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8 col-md-12"> <!-- Coluna da esquerda -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome de identificação</label>
                            <input class="form-control" type="text" name="nomeIdentificacao"
                                placeholder="Ônibus 2026 DD" required>

                            <label class="py-2">Numeração</label>
                            <input class="form-control" type="number" name="numeracao" placeholder="4000" required>

                            <div class="mb-3">
                                <label class="py-2">Descrição</label>
                                <textarea class="form-control" name="descricao" id="descricao" rows="3"></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="fileUpload" class="form-label py-1">Mídia (Imagens ou Vídeos)</label>
                                <input class="form-control" type="file" name="midia[]" id="fileUpload" accept="image/*,video/*" multiple>
                                <div class="form-text">Você pode selecionar várias imagens e vídeos de uma vez.</div>
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
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Ônibus">Ônibus</option>
                                        <option value="Micro-ônibus">Micro-ônibus</option>
                                        <option value="Van">Van</option>
                                        <option value="Carro">Carro</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Capacidade total</label>
                                    <input style="width: 100px;" class="form-control" type="number"
                                        name="capacidadeTotal" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Tipo de leito</label>
                                    <select class="form-select" name="tipoLeito">
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Convencional">Convencional</option>
                                        <option value="Semi-leito">Semi-leito</option>
                                        <option value="Leito">Leito</option>
                                        <option value="Leito Cama">Leito Cama</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Ano</label>
                                    <input style="width: 100px;" class="form-control" type="number" name="ano"
                                        placeholder="2020" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Placa</label>
                                    <input style="width: 100px;" class="form-control" type="text" name="placa"
                                        placeholder="ABC1D23" maxlength="7" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <small class="text-muted">Preencha apenas se o veículo tiver 2 andares</small>
                            <div class="row g-3 mt-1">
                                <div class="col-md-6 border-end">
                                    <small class="text-muted">1º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadePrimeiroAndar" placeholder="Cap.">
                                        <select class="form-select" name="leitoPrimeiroAndar">
                                            <option value="" selected disabled>Leito 1º andar</option>
                                            <option value="Convencional">Convencional</option>
                                            <option value="Semi-Leito">Semi-Leito</option>
                                            <option value="Leito">Leito</option>
                                            <option value="Leito Cama">Leito Cama</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">2º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadeSegundoAndar" placeholder="Cap.">
                                        <select class="form-select" name="leitoSegundoAndar">
                                            <option value="" selected disabled>Leito 2º andar</option>
                                            <option value="Convencional">Convencional</option>
                                            <option value="Semi-Leito">Semi-Leito</option>
                                            <option value="Leito">Leito</option>
                                            <option value="Leito Cama">Leito Cama</option>
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
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                            </select>

                            <div class="mb-3">
                                <label class="form-label pt-2">Tags</label>
                                <div class="tag-container form-control d-flex flex-wrap align-items-center"
                                    id="tagContainer">
                                    <input type="text" id="tagInput" placeholder="Adicionar tags"
                                        class="border-0 flex-grow-1" style="outline: none; min-width: 120px; background: transparent; color: black;">
                                </div>
                                <!-- Campo oculto para enviar as tags via POST no PHP -->
                                <input type="hidden" name="tags" id="tagsHidden">
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