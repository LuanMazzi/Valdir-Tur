<?php
session_start();

// Limpa os dados da sessão
$_SESSION = [];

// Apaga o cookie de sessão do navegador
if (ini_get('session.use_cookies')) {
    $parametros = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $parametros['path'],
        $parametros['domain'],
        $parametros['secure'],
        $parametros['httponly']
    );
}

// Encerra a sessão no servidor
session_destroy();

header('Location: /ValdirTur/login.php');
exit;
