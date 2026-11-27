<?php
session_start();

// Acesso apenas para admin logado
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit;
}

// Configurações e conexão com o banco
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/conexao.php');
require_once(__DIR__ . '/../includes/agenda.php');
require_once(__DIR__ . '/../includes/validar-data.php');

// Responde se o veículo já está reservado no período
header('Content-Type: application/json; charset=utf-8');

// Saída no passado: avisa antes de olhar a agenda.
// Na edição, manter a data que já estava salva é permitido (mesma regra do salvar).
$saida      = $_GET['saida'] ?? '';
$saidaSalva = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT dataHoraSaida FROM tbFretamento WHERE idFretamento = " . (int) ($_GET['ignorar'] ?? 0)))['dataHoraSaida'] ?? '';

if (strtotime($saida) !== strtotime($saidaSalva) && dataNoPassado($saida)) {
    echo json_encode(['passado' => true, 'mensagem' => 'A data de saída não pode estar no passado.']);
    exit;
}

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
