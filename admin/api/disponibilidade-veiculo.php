<?php
session_start();

// Acesso apenas para admin logado
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit;
}

// Configurações e conexão com o banco
require_once(__DIR__ . '/../../config/config.php');
require_once(__DIR__ . '/../../config/conexao.php');
require_once(__DIR__ . '/../../includes/agenda.php');

// Responde se o veículo já está reservado no período
header('Content-Type: application/json; charset=utf-8');

$conflito = buscarConflitoAgenda(
    $conexao,
    $_GET['idVeiculo'] ?? 0,
    $_GET['saida'] ?? '',
    $_GET['retorno'] ?? '',
    $_GET['ignorar'] ?? 0
);

echo json_encode($conflito
    ? ['conflito' => true, 'mensagem' => mensagemConflitoAgenda($conflito)]
    : ['conflito' => false]);
