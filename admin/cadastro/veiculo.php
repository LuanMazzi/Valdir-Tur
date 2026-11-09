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

// Devolve o valor digitado (mantém os dados no formulário após um erro)
function valor($campo)
{
    return htmlspecialchars($_POST[$campo] ?? '');
}

// Marca a opção escolhida do <select>
function selecionado($campo, $opcao)
{
    return ($_POST[$campo] ?? '') === $opcao ? 'selected' : '';
}

$erro = "";
$mensagem = "";
$doisAndares = isset($_POST['doisAndares']);
if (isset($_GET['sucesso'])) {
    $mensagem = "<div class='alert alert-success'>Veículo cadastrado com sucesso!</div>";
}

// Salvar: recebe os dados do formulário e grava no banco
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

    // Tipo de leito único (1 andar) OU por andar (2 andares), nunca os dois
    if ($doisAndares) {
        $tipoLeito = '';
    } else {
        $capacidadePrimeiroAndar = $capacidadeSegundoAndar = $leitoPrimeiroAndar = $leitoSegundoAndar = '';
    }

    $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'avi', 'webm'];
    $nomesMidia = [];
    $diretorio  = __DIR__ . '/../../assets/uploads/';

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
     
        $capacidadePrimeiroAndar = $capacidadePrimeiroAndar === '' ? "NULL" : "'$capacidadePrimeiroAndar'";
        $capacidadeSegundoAndar  = $capacidadeSegundoAndar === '' ? "NULL" : "'$capacidadeSegundoAndar'";
        $tipoLeito               = $tipoLeito === '' ? "NULL" : "'$tipoLeito'";
        $leitoPrimeiroAndar      = $leitoPrimeiroAndar === '' ? "NULL" : "'$leitoPrimeiroAndar'";
        $leitoSegundoAndar       = $leitoSegundoAndar === '' ? "NULL" : "'$leitoSegundoAndar'";
        $nomeMidia               = $nomeMidia === null ? "NULL" : "'$nomeMidia'";

        // Comando SQL
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
            <button type="button" class="botao-voltar" onclick="window.location.href='../listas/veiculos.php'">
                <i class="bi bi-bus-front-fill fs-4"></i>
            </button>
            <i class="bi bi-arrow-right-short"></i>
            <label class="pb-2"><strong>Adicionar veículo</strong></label>

            <?= $mensagem ?>
            <?php if ($erro !== ""): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="row">
                <!-- Coluna da esquerda -->
                <div class="col-lg-8 col-md-12">
                    <!-- Card: Dados do veículo -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <label class="py-2">Nome de identificação</label>
                            <input class="form-control" type="text" name="nomeIdentificacao" value="<?= valor('nomeIdentificacao') ?>"
                                placeholder="Ônibus 2026 DD" required>

                            <label class="py-2">Numeração</label>
                            <input class="form-control" type="text" name="numeracao" value="<?= valor('numeracao') ?>" placeholder="4000" required>

                            <div class="mb-3">
                                <label class="py-2">Descrição</label>
                                <textarea class="form-control" name="descricao" id="descricao" rows="3"><?= valor('descricao') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="fileUpload" class="form-label py-1">Mídia (Imagens ou Vídeos)</label>
                                <input class="form-control" type="file" name="midia[]" id="fileUpload" accept="image/*,video/*" multiple>
                                <div class="form-text">Você pode selecionar várias imagens e vídeos de uma vez.</div>
                            </div>


                        </div>
                    </div>

                    <!-- Card: Informações do veículo -->
                    <div class="card-principal mt-4">
                        <div class="card-principal-conteudo">
                            <strong><label>Informações</label></strong> <br>

                            <div class="row g-4 align-items-end">
                                <div class="col-auto">
                                    <label class="d-block py-2">Tipo de Veículo</label>
                                    <select class="form-select" name="tipoVeiculo" required>
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Ônibus" <?= selecionado('tipoVeiculo', 'Ônibus') ?>>Ônibus</option>
                                        <option value="Micro-ônibus" <?= selecionado('tipoVeiculo', 'Micro-ônibus') ?>>Micro-ônibus</option>
                                        <option value="Van" <?= selecionado('tipoVeiculo', 'Van') ?>>Van</option>
                                        <option value="Carro" <?= selecionado('tipoVeiculo', 'Carro') ?>>Carro</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Capacidade total</label>
                                    <input style="width: 100px;" class="form-control" type="number"
                                        name="capacidadeTotal" value="<?= valor('capacidadeTotal') ?>" required>
                                </div>

                                <div class="col-auto <?= $doisAndares ? 'd-none' : '' ?>" id="blocoTipoLeito">
                                    <label class="d-block py-2">Tipo de leito</label>
                                    <select class="form-select" name="tipoLeito">
                                        <option value="" selected disabled>Selecione</option>
                                        <option value="Convencional" <?= selecionado('tipoLeito', 'Convencional') ?>>Convencional</option>
                                        <option value="Semi-leito" <?= selecionado('tipoLeito', 'Semi-leito') ?>>Semi-leito</option>
                                        <option value="Leito" <?= selecionado('tipoLeito', 'Leito') ?>>Leito</option>
                                        <option value="Leito Cama" <?= selecionado('tipoLeito', 'Leito Cama') ?>>Leito Cama</option>
                                    </select>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Ano</label>
                                    <input style="width: 100px;" class="form-control" type="number" name="ano" value="<?= valor('ano') ?>"
                                        placeholder="2020" required>
                                </div>

                                <div class="col-auto">
                                    <label class="d-block py-2">Placa</label>
                                    <input style="width: 100px;" class="form-control" type="text" name="placa" value="<?= valor('placa') ?>"
                                        placeholder="ABC1D23" maxlength="7" required>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="doisAndares" id="doisAndares"
                                    <?= $doisAndares ? 'checked' : '' ?>>
                                <label class="form-check-label" for="doisAndares">Veículo de 2 andares</label>
                            </div>
                            <div class="row g-3 mt-1 <?= $doisAndares ? '' : 'd-none' ?>" id="blocoAndares">
                                <div class="col-md-6 border-end">
                                    <small class="text-muted">1º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadePrimeiroAndar" value="<?= valor('capacidadePrimeiroAndar') ?>" placeholder="Cap.">
                                        <select class="form-select" name="leitoPrimeiroAndar">
                                            <option value="" selected disabled>Leito 1º andar</option>
                                            <option value="Convencional" <?= selecionado('leitoPrimeiroAndar', 'Convencional') ?>>Convencional</option>
                                            <option value="Semi-Leito" <?= selecionado('leitoPrimeiroAndar', 'Semi-Leito') ?>>Semi-Leito</option>
                                            <option value="Leito" <?= selecionado('leitoPrimeiroAndar', 'Leito') ?>>Leito</option>
                                            <option value="Leito Cama" <?= selecionado('leitoPrimeiroAndar', 'Leito Cama') ?>>Leito Cama</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">2º Andar</small>
                                    <div class="d-flex gap-2 mt-2">
                                        <input style="width: 100px;" class="form-control" type="number"
                                            name="capacidadeSegundoAndar" value="<?= valor('capacidadeSegundoAndar') ?>" placeholder="Cap.">
                                        <select class="form-select" name="leitoSegundoAndar">
                                            <option value="" selected disabled>Leito 2º andar</option>
                                            <option value="Convencional" <?= selecionado('leitoSegundoAndar', 'Convencional') ?>>Convencional</option>
                                            <option value="Semi-Leito" <?= selecionado('leitoSegundoAndar', 'Semi-Leito') ?>>Semi-Leito</option>
                                            <option value="Leito" <?= selecionado('leitoSegundoAndar', 'Leito') ?>>Leito</option>
                                            <option value="Leito Cama" <?= selecionado('leitoSegundoAndar', 'Leito Cama') ?>>Leito Cama</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Coluna da direita -->
                <div class="col-lg-4 col-md-12">
                    <!-- Card: Status e tags -->
                    <div class="card-principal">
                        <div class="card-principal-conteudo">
                            <strong><label>Status</label></strong>
                            <select class="form-select" name="status" required>
                                <option value="Ativo" <?= selecionado('status', 'Ativo') ?>>Ativo</option>
                                <option value="Inativo" <?= selecionado('status', 'Inativo') ?>>Inativo</option>
                            </select>

                            <div class="mb-3">
                                <label class="form-label pt-2">Tags</label>
                                <div class="tag-container form-control d-flex flex-wrap align-items-center"
                                    id="tagContainer">
                                    <input type="text" id="tagInput" placeholder="Adicionar tags"
                                        class="border-0 flex-grow-1" style="outline: none; min-width: 120px; background: transparent; color: black;">
                                </div>
                                <!-- Tags (preenchido pelo script) -->
                                <input type="hidden" name="tags" id="tagsHidden" value="<?= valor('tags') ?>">
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

    <script>

        const checkAndares = document.getElementById('doisAndares');
        const blocoTipoLeito = document.getElementById('blocoTipoLeito');
        const blocoAndares = document.getElementById('blocoAndares');

        function alternarAndares() {
            const dois = checkAndares.checked;
            blocoTipoLeito.classList.toggle('d-none', dois);
            blocoAndares.classList.toggle('d-none', !dois);
            blocoTipoLeito.querySelectorAll('input, select').forEach(c => c.disabled = dois);
            blocoAndares.querySelectorAll('input, select').forEach(c => c.disabled = !dois);
        }

        checkAndares.addEventListener('change', alternarAndares);
        alternarAndares();
    </script>
    <script src="/ValdirTur/assets/js/script.js"></script>
    <script src="/ValdirTur/assets/js/protecao-form.js"></script>
    </main>
</body>

</html>