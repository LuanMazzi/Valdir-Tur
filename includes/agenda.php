<?php
// Agenda dos ônibus: impede dois fretamentos no mesmo veículo ao mesmo tempo

// Status que ocupam o ônibus na agenda (orçamento, rascunho e cancelado não bloqueiam)
const STATUS_QUE_BLOQUEIAM = ['Aprovado', 'Em andamento', 'Concluído'];

// Procura um fretamento que já ocupe o veículo no período. Devolve a linha do conflito, ou null se estiver livre.
// Independe do status do fretamento que está sendo cadastrado: só olha quem já reserva o ônibus.
function buscarConflitoAgenda($conexao, $idVeiculo, $saida, $retorno, $ignorarId = 0)
{
    $inicio = strtotime($saida);
    $fim    = strtotime($retorno);

    if (!$inicio || !$fim || $fim <= $inicio || !(int) $idVeiculo) {
        return null;
    }

    $idVeiculo  = (int) $idVeiculo;
    $ignorarId  = (int) $ignorarId;
    $saidaSql   = date('Y-m-d H:i:s', $inicio);
    $retornoSql = date('Y-m-d H:i:s', $fim);
    $statusSql  = "'" . implode("','", STATUS_QUE_BLOQUEIAM) . "'";

    // Há conflito quando o outro fretamento começa antes do nosso fim E termina depois do nosso início
    $sql = "SELECT f.idFretamento, f.destino, f.dataHoraSaida, f.dataHoraRetorno, v.nomeIdentificacao
            FROM tbFretamento f
            JOIN tbVeiculo v ON v.idVeiculo = f.idVeiculo
            WHERE f.idVeiculo = $idVeiculo
              AND f.idFretamento <> $ignorarId
              AND f.status IN ($statusSql)
              AND f.dataHoraSaida < '$retornoSql'
              AND f.dataHoraRetorno > '$saidaSql'
            LIMIT 1";

    return mysqli_fetch_assoc(mysqli_query($conexao, $sql)) ?: null;
}

// Texto do aviso de conflito
function mensagemConflitoAgenda($conflito)
{
    return sprintf(
        'O veículo %s já está reservado de %s até %s (fretamento nº %d, destino %s).',
        $conflito['nomeIdentificacao'],
        date('d/m/Y H:i', strtotime($conflito['dataHoraSaida'])),
        date('d/m/Y H:i', strtotime($conflito['dataHoraRetorno'])),
        $conflito['idFretamento'],
        $conflito['destino']
    );
}

// Valida as datas e confere se o veículo está livre no período (ao salvar).
// Devolve o texto do erro, ou '' se estiver tudo certo.
function validarAgendaFretamento($conexao, $idVeiculo, $saida, $retorno, $status, $ignorarId = 0)
{
    $inicio = strtotime($saida);
    $fim    = strtotime($retorno);

    if (!$inicio || !$fim) {
        return 'Informe a data de saída e a data de retorno.';
    }

    if ($fim <= $inicio) {
        return 'A data de retorno deve ser depois da data de saída.';
    }

    // Só quem reserva o ônibus precisa passar pela conferência de agenda
    if (!in_array($status, STATUS_QUE_BLOQUEIAM, true)) {
        return '';
    }

    $conflito = buscarConflitoAgenda($conexao, $idVeiculo, $saida, $retorno, $ignorarId);

    if (!$conflito) {
        return '';
    }

    return mensagemConflitoAgenda($conflito) . ' Escolha outro veículo ou outras datas.';
}
