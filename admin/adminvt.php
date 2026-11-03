<?php
session_start();

// Acesso apenas para admin logado
if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

// Configurações e conexão com o banco
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');

// Quantidade de registros ativos de uma tabela (o nome da tabela vem só da lista abaixo)
function totalAtivos($conexao, $tabela)
{
    $linha = mysqli_fetch_row(mysqli_query($conexao, "SELECT COUNT(*) FROM $tabela WHERE status = 'Ativo'"));
    return (int) $linha[0];
}

// Cartões do painel: rótulo, tabela, página da lista e ícone
$cartoes = [
    ['Veículos ativos',     'tbVeiculo',     'listas/veiculos.php',     'bi-bus-front-fill'],
    ['Funcionários ativos', 'tbFuncionario', 'listas/funcionarios.php', 'bi-person-badge'],
    ['Clientes ativos',     'tbCliente',     'listas/clientes.php',     'bi-person-fill'],
    ['Pacotes ativos',      'tbPacote',      'listas/pacotes.php',      'bi-luggage-fill'],
];

// Nome de quem entrou (guardado na sessão pelo login)
$nome = $_SESSION['admin']['nome'] ?? 'admin';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Painel do Admin</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../includes/sidebar-admin.php'); ?>

   
    <main class="w-100 p-4" style="overflow-y: auto;">
        <div class="container-fluid">
            <h2 class="fw-bold">Bem-vindo, <?= htmlspecialchars($nome) ?>!</h2>
                <h3 class="text-secondary fw-normal fs-5" id="data-atual">Carregando data...</h3>

            <!-- Cartões com os totais (cada um leva para a lista) -->
            <div class="row g-4 mt-2">
                <?php foreach ($cartoes as [$rotulo, $tabela, $pagina, $icone]): ?>
                    <div class="col-12 col-sm-6 col-xl-3">
                        <a href="<?= $pagina ?>" class="text-decoration-none text-body d-block">
                            <div class="card-principal">
                                <div class="card-principal-conteudo d-flex align-items-center gap-3">
                                    <i class="bi <?= $icone ?> fs-1 text-secondary"></i>
                                    <div>
                                        <div class="fs-2 fw-bold lh-1"><?= totalAtivos($conexao, $tabela) ?></div>
                                        <div class="text-secondary"><?= $rotulo ?></div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>


    </main>




    <!-- Data de hoje -->
    <script>
        (function () {
            const elementoData = document.getElementById('data-atual');
            const hoje = new Date();

            const opcoes = {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            };

            // Formato: "sexta-feira, 01 de maio de 2026"
            let dataFormatada = hoje.toLocaleDateString('pt-BR', opcoes);


            dataFormatada = dataFormatada.charAt(0).toUpperCase() + dataFormatada.slice(1);

            elementoData.textContent = dataFormatada;
        })();
    </script>

</body>

</html>