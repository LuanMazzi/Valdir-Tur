<?php
session_start();

// 1. Importa as configurações primeiro
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ . '/config/conexao.php');

// 2. Executa o bloco APENAS se o formulário foi enviado
if (isset($_POST["entrar"])) { 
    $email = $_POST['email'] ?? ''; 
    $senha = $_POST['senha'] ?? ''; 

    // O ideal aqui seria usar Prepared Statements para evitar Injeção de SQL
    $sql = "SELECT * FROM tbFuncionario WHERE email = '$email' AND senha = '$senha'"; 
    $resultado = mysqli_query($conexao, $sql); 
    
    if ($resultado) {
        $registros = mysqli_num_rows($resultado); 
        
        if($registros > 0) {
            $_SESSION['admin'] = true;
            header("Location: /ValdirTur/admin/adminvt.php");
        } else { 
            $mensagem = "Usuário ou senha inválidos."; 
            header("Location: index.php?mensagem=$mensagem"); 
            
        } 
    } else {
        die("Erro na consulta: " . mysqli_error($conexao));
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
    <link rel="icon" type="image/x-icon" href="/ValdirTur/assets/icons/logo-menor.png"> 
    <style> 
        .card-login { border-radius: 30px; } 
        .card-login .form-control { border-radius: 10px; } 
    </style> 
</head> 
<body> 
    <script src="/ValdirTur/vendor/bootstrap/js/bootstrap.bundle.min.js"></script> 
    <div class="d-flex justify-content-center align-items-center min-vh-100 bg-light"> 
        <div class="container"> 
            <div class="row justify-content-center"> 
                <div class="col-12 col-sm-10 col-md-8 col-lg-5"> 
                    <div class="card shadow-sm border-0 card-login"> 
                        <div class="card-body p-4 p-md-5"> 
                            <img class="rounded mx-auto d-block" src="/ValdirTur/assets/icons/logo-maior.png" width="auto" height="150px" alt="Logo Valdir Tur"> 
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
