<?php 
session_start(); 
// Configurações e conexão com o banco
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/config/conexao.php');

// Login: roda só quando o formulário é enviado
if (isset($_POST["entrar"])) {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';

    // Busca o funcionário pelo e-mail (a senha é conferida logo abaixo, no PHP)
    $consulta = mysqli_prepare($conexao, "SELECT * FROM tbFuncionario WHERE email = ?");
    mysqli_stmt_bind_param($consulta, "s", $email);
    mysqli_stmt_execute($consulta);
    $funcionarios = mysqli_stmt_get_result($consulta);

    $logado = null;
    while ($funcionario = mysqli_fetch_assoc($funcionarios)) {
        $guardada = $funcionario['senha'];

        if (password_get_info($guardada)['algo'] !== null) {
            // Senha guardada com hash (cadastro pela tela): password_verify compara a senha digitada com o hash
            $confere = password_verify($senha, $guardada);
        } else {
            // Senha antiga, guardada em texto puro: confere e já converte para hash
            $confere = hash_equals($guardada, $senha);
            if ($confere) {
                $novoHash = password_hash($senha, PASSWORD_DEFAULT);
                $atualiza = mysqli_prepare($conexao, "UPDATE tbFuncionario SET senha = ? WHERE idFuncionario = ?");
                mysqli_stmt_bind_param($atualiza, "si", $novoHash, $funcionario['idFuncionario']);
                mysqli_stmt_execute($atualiza);
            }
        }

        if ($confere) {
            $logado = $funcionario;
            break;
        }
    }

    if ($logado) {
        // Guarda na sessão quem entrou (o nome aparece no painel)
        $_SESSION['admin'] = [
            'id'   => $logado['idFuncionario'],
            'nome' => $logado['nome'],
        ];
        header("Location: /ValdirTur/admin/adminvt.php");
        exit();
    } else {
        // Salva a mensagem na sessão em vez da URL
        $_SESSION['mensagemErro'] = "Usuário e/ou senha inválidos.";
        header("Location: index.php");
        exit();
    }
} 
?>
<!DOCTYPE html> 
<html lang="pt-BR"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Valdir Tur</title> 
    <link rel="stylesheet" href="/ValdirTur/vendor/bootstrap/css/bootstrap.min.css"> 
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/ValdirTur/assets/css/style.css">
    <link rel="icon" type="image/png" href="/ValdirTur/assets/logos/logo-menor.png">
    <link rel="icon" type="image/svg+xml" href="/ValdirTur/assets/logos/logo-vt.svg"> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.2/font/bootstrap-icons.min.css">
    <style> 
        .card-login { border-radius: 30px; } 
        .card-login .form-control { border-radius: 10px; } 
    </style> 
</head> 
<body class="bg-light"> 


<div class="m-3">
    <a href="/ValdirTur/index.php"> <i class="bi bi-arrow-left"></i> Voltar ao ínicio</a>
</div>

    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script> 
    <div class="d-flex justify-content-center align-items-center min-vh-100 "> 
        
        <div class="container"> 
            <div class="row justify-content-center"> 
                <div class="col-12 col-sm-10 col-md-8 col-lg-5"> 
                    <div class="card shadow-sm border-0 card-login"> 
                        <div class="card-body p-4 p-md-5"> 
                            <img class="rounded mx-auto d-block" src="/ValdirTur/assets/logos/logo-valdirtur.svg" width="auto" height="150px" alt="Logo Valdir Tur"> 
                            <form method="POST" action="/ValdirTur/login.php"> 
                                <div class="mb-3"> 
                                    <label class="form-label">Email</label> 
                                    <input type="text" class="form-control" id="email" name="email" placeholder="Digite seu Email"> 
                                </div> 
                                <div class="mb-3"> 
                                    <label class="form-label">Senha</label> 
                                    <input type="password" class="form-control" id="senha" name="senha" placeholder="Digite sua senha"> 
                                </div> 
                                <button type="submit" name="entrar" class="btn btn-primary btn-lg w-100">Entrar</button> 
                            </form> 
                        </div> 
                    </div> 
                </div> 
            </div> 
        </div> 
    </div> 
</body> 
</html>
