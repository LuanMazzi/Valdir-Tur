<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Valdir Tur</title>
    <link rel="stylesheet" href="/ValdirTur/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="icon" type="image/x-icon" href="/ValdirTur/assets/icons/logo-menor.png">

    <style>
        .card-login {
            border-radius: 30px;
        }

        .card-login .form-control {
            border-radius: 10px;
        }
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
                            <img class="rounded mx-auto d-block" src="/ValdirTur/assets/icons/logo-maior.png"
                                width="auto" height="150px" alt="Logo Valdir Tur">
                            <!-- <h2 class="card-title text-center mb-4">Acessar Conta</h2> -->
                            <?php
                            session_start();

                            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                $usuario = $_POST['campoUsuario'];
                                $senha = $_POST['campoSenha'];

                                // Substitua pelos dados reais do seu banco
                                if ($usuario === 'admin' && $senha === '1234') {
                                    $_SESSION['admin'] = true;
                                    header('Location: /ValdirTur/admin/adminvt.php');
                                    exit;
                                } else {
                                    $erro = "Usuário ou senha incorretos.";
                                }
                            }
                            ?>

                            <form method="POST" action="/ValdirTur/login.php">
                                <div class="mb-3">
                                    <label class="form-label">Usuário</label>
                                    <input type="text" class="form-control" id="campoUsuario" name="campoUsuario"
                                        placeholder="Digite seu usuário">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Senha</label>
                                    <input type="password" class="form-control" id="campoSenha" name="campoSenha"
                                        placeholder="Digite sua senha">
                                </div>

                                <!-- <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="exampleCheck1">
                                    <label class="form-check-label" for="exampleCheck1">Lembrar de mim</label>
                                </div> -->

                                <button type="submit" class="btn btn-primary btn-lg w-100">Entrar</button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>