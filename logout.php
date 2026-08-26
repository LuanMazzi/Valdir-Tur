<?php
session_start();

// Limpa as variáveis da sessão
$_SESSION = [];

// Apaga o cookie de sessão do navegador, senão o PHP continua mandando o mesmo ID
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

// Destrói a sessão no servidor
session_destroy();

header('Location: /ValdirTur/login.php');
exit;
