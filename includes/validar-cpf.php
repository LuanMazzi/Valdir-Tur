<?php
// Valida CPF (11 números, sem pontuação)
function validarCPF($cpf)
{
    // Formato: exatamente 11 números
    if (!preg_match('/^[0-9]{11}$/', $cpf)) {
        return false;
    }

    // Rejeita CPF com todos os dígitos iguais
    if (preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }

    // Dígitos verificadores (posições 10 e 11)
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += $cpf[$i] * (($t + 1) - $i);
        }
        $digito = ((10 * $soma) % 11) % 10;

        if ($cpf[$t] != $digito) {
            return false;
        }
    }

    return true;
}
