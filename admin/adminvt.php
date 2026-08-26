<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: /ValdirTur/login.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<title>Painel do Admin</title>
<?php include(__DIR__ . '/../includes/head.php'); ?>

<body class="d-flex flex-nowrap">
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <?php include(__DIR__ . '/../includes/sidebar-admin.php'); ?>

    <!-- O main é "o berço" de tudo do painel -->
    <main class="w-100 p-4" style="overflow-y: auto;">
        <div class="container-fluid">
            <h2 class="fw-bold">Bem-vindo, admin!</h2>
            <h3 class="text-secondary fw-normal fs-5" id="data-atual">Carregando data...</h3>

           <!-- O segredo está nesta DIV com a classe 'row' envolta de todos os cards -->
<div class="row g-4 mt-2"> 
    
    <!-- Card 1 -->
    <div class="col-md-4">
        <div class="card-desenho h-100">
            <div class="card-informacoes">
                <p><strong>Fretamentos do mês</strong></p>
                <h1 class="numero-total">0</h1>
                <small>Nenhum fretamento esse mês.</small>
            </div>
        </div>
    </div>

    <!-- Card 2 -->
    <div class="col-md-4">
        <div class="card-desenho h-100">
            <div class="card-informacoes">
                <p><strong>Viagens Ativas</strong></p>
                <h1 class="numero-total">3</h1>
                <small>Ônibus em trânsito agora.</small>
            </div>
        </div>
    </div>

    <!-- Card 3 -->
    <div class="col-md-4">
        <div class="card-desenho h-100">
            <div class="card-informacoes">
                <p><strong>Manutenção</strong></p>
                <h1 class="numero-total">1</h1>
                <small>Veículo em revisão técnica.</small>
            </div>
        </div>
    </div>
    

</div>
        </div>


    </main>




    <script> // Função de data formatada
        (function () {
            const elementoData = document.getElementById('data-atual');
            const hoje = new Date();

            const opcoes = {
                weekday: 'long',
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            };

            // Formata a data "sexta-feira, 01 de maio de 2026"
            let dataFormatada = hoje.toLocaleDateString('pt-BR', opcoes);


            dataFormatada = dataFormatada.charAt(0).toUpperCase() + dataFormatada.slice(1);

            elementoData.textContent = dataFormatada;
        })();
    </script>

</body>

</html>