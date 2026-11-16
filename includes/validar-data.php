<?php
// Confere se uma data/hora (ex.: "2026-11-20T08:00", vinda do campo datetime-local) já passou
function dataNoPassado($dataHora)
{
    // Data vazia: quem cuida disso é o "required" do formulário
    if (trim($dataHora) === '') {
        return false;
    }

    // Usa o horário de Brasília, independente do fuso configurado no PHP
    $fuso = new DateTimeZone('America/Sao_Paulo');

    $data  = new DateTime($dataHora, $fuso);
    $agora = new DateTime('now', $fuso);

    return $data < $agora;
}
