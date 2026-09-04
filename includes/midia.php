<?php
// Funções auxiliares pra lidar com a coluna `midia`, que guarda vários nomes
// de arquivo (imagens e/ou vídeos) separados por vírgula num único campo.

function midiaLista($midia)
{
    if (empty($midia)) {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $midia))));
}

function midiaEhVideo($nomeArquivo)
{
    $extensoesVideo = ['mp4', 'mov', 'avi', 'webm'];
    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    return in_array($extensao, $extensoesVideo, true);
}

// Pra cards/miniaturas: pega a primeira imagem da lista (pula vídeos)
function midiaPrimeiraImagem($midia)
{
    foreach (midiaLista($midia) as $nome) {
        if (!midiaEhVideo($nome)) {
            return $nome;
        }
    }
    return null;
}
