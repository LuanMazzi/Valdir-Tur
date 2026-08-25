<?php
// Configurações do Banco de Dados
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'bdvaldirtur';

// Criando a conexão
$conexao = new mysqli($host, $usuario, $senha, $banco);

// Definindo o charset para evitar problemas com acentuação
$conexao->set_charset("utf8mb4");

// Verificando se houve erro na conexão
if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}
?>