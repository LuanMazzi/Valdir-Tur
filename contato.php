<?php
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/config/conexao.php');



if (isset($_POST['enviar'])) {

    // 1. Coleta e sanitização dos dados
    $nome = urlencode($_POST['nome'] ?? '');
    $email = urlencode($_POST['email'] ?? '');
    $mensagem = urlencode($_POST['mensagem'] ?? '');

    // 2. Número de destino (deve incluir código do país, DDD e número)
    $numero_whatsapp = "5544984641251"; 

    // 3. Montagem da mensagem
    $texto = "Olá, sou $nome." . " E-mail: $email. " . "Mensagem: $mensagem";

    // 4. URL de redirecionamento
    $url = "https://wa.me/{$numero_whatsapp}?text={$texto}";

    // 5. Redirecionar
    header("Location: $url");
    exit;

}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<title>Contato - Valdir Tur</title>
<?php include(__DIR__ . '/includes/head.php'); ?>

<body>
    <?php include 'includes/header.php'; ?>

    <section class="container py-5">
        <form method="POST" class="col-lg-12">
            <div class="card-principal">
                <div class="card-principal-conteudo">

                    <div style="margin-bottom: 16px;">
                        <p class="h1" style="margin-bottom: 0px;">Contato</p>
                        <small>Estamos aqui para lhe atender da melhor forma possível!</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nome completo</label>
                        <input type="text" name="nome" class="form-control" id="nome" placeholder="Seu nome completo">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="seu@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mensagem</label>
                        <textarea class="form-control" name="mensagem" id="mensagem" rows="3" placeholder="Digite sua mensagem"></textarea>
                    </div>

                    
                    <button style="background-color: #2B5FED; color: white;" class="btn me-md-2" name="enviar"
                    type="submit">ENVIAR MENSAGEM</button>

                </div>
            </div>
        </form>
    </section>

    <?php include 'includes/footer.php'; ?>

</body>

</html>