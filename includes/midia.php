<?php
// Funções da coluna `midia` (vários arquivos separados por vírgula)

// Transforma o texto do banco em lista de arquivos
function midiaLista($midia)
{
    if (empty($midia)) {
        return [];
    }
    return array_values(array_filter(array_map('trim', explode(',', $midia))));
}

// Diz se o arquivo é vídeo (pela extensão)
function midiaEhVideo($nomeArquivo)
{
    $extensoesVideo = ['mp4', 'mov', 'avi', 'webm'];
    $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    return in_array($extensao, $extensoesVideo, true);
}

// Primeira imagem da lista (usada nos cards), ignorando vídeos
function midiaPrimeiraImagem($midia)
{
    foreach (midiaLista($midia) as $nome) {
        if (!midiaEhVideo($nome)) {
            return $nome;
        }
    }
    return null;
}
