<?php
// Confere se a pessoa tem a idade mínima (18 anos completos) na data de hoje
function maiorDeIdade($dataNascimento, $idadeMinima = 18, $hoje = 'today')
{
    $nascimento = DateTime::createFromFormat('!Y-m-d', $dataNascimento);

    // Data vazia, fora do formato ou que não existe (ex.: 2000-02-30)
    if (!$nascimento || $nascimento->format('Y-m-d') !== $dataNascimento) {
        return false;
    }

    $hoje = new DateTime($hoje);

    // Data no futuro
    if ($nascimento > $hoje) {
        return false;
    }

    // diff()->y = anos completos entre a data de nascimento e hoje
    return $nascimento->diff($hoje)->y >= $idadeMinima;
}
