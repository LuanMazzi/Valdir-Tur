<?php
// Dados de acesso ao banco
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'bdvaldirtur';

// Conexão
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Charset (acentuação)
$conexao->set_charset("utf8mb4");

// Erro de conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>