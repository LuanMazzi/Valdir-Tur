<?php
// Agenda dos ônibus: impede dois fretamentos no mesmo veículo ao mesmo tempo

// Status que ocupam o ônibus na agenda (orçamento, rascunho e cancelado não bloqueiam)
const STATUS_QUE_BLOQUEIAM = ['Aprovado', 'Em andamento', 'Concluído'];

// Valida as datas e confere se o veículo está livre no período.
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

    $conflito = mysqli_fetch_assoc(mysqli_query($conexao, $sql));

    if (!$conflito) {
        return '';
    }

    return sprintf(
        'O veículo %s já está reservado de %s até %s (fretamento nº %d, destino %s). Escolha outro veículo ou outras datas.',
        $conflito['nomeIdentificacao'],
        date('d/m/Y H:i', strtotime($conflito['dataHoraSaida'])),
        date('d/m/Y H:i', strtotime($conflito['dataHoraRetorno'])),
        $conflito['idFretamento'],
        $conflito['destino']
    );
}
