<?php
// Valida CNPJ (14 caracteres, sem pontuação). Aceita o formato numérico e o alfanumérico
function validarCNPJ($cnpj)
{
    $cnpj = strtoupper($cnpj);

    if (!preg_match('/^[0-9A-Z]{12}[0-9]{2}$/', $cnpj)) {
        return false;
    }

    // Rejeita CNPJ com todos os caracteres iguais
    if (preg_match('/^(.)\1{13}$/', $cnpj)) {
        return false;
    }

    // Dígitos verificadores (posições 13 e 14)
    for ($t = 12; $t < 14; $t++) {
        $soma = 0;
        $peso = 2;

        // Da direita para a esquerda, com pesos de 2 a 9
        for ($i = $t - 1; $i >= 0; $i--) {
            // Valor do caractere = código ASCII - 48 ('0' = 0, 'A' = 17)
            $soma += (ord($cnpj[$i]) - 48) * $peso;
            $peso = $peso == 9 ? 2 : $peso + 1;
        }

        $resto = $soma % 11;
        $digito = $resto < 2 ? 0 : 11 - $resto;

        if ($cnpj[$t] != $digito) {
            return false;
        }
    }

    return true;
}
