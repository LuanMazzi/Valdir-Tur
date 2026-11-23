<?php
// Valida RG: de 5 a 14 caracteres, só números (o último pode ser a letra X)
function validarRG($rg)
{
    if (!preg_match('/^[0-9]{4,13}[0-9Xx]$/', $rg)) {
        return false;
    }

    // Rejeita RG com todos os números iguais (ex.: 11111111)
    return !preg_match('/^(\d)\1+$/', $rg);
}

// Valida telefone brasileiro: DDD + número, só números (10 dígitos fixo ou 11 dígitos celular)
function validarTelefone($telefone)
{
    if (!preg_match('/^[0-9]{10,11}$/', $telefone)) {
        return false;
    }

    // Rejeita números repetidos (ex.: 44444444444)
    if (preg_match('/^(\d)\1+$/', $telefone)) {
        return false;
    }

    // DDDs existentes no Brasil
    $ddds = [11, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 24, 27, 28, 31, 32, 33, 34, 35, 37, 38,
             41, 42, 43, 44, 45, 46, 47, 48, 49, 51, 53, 54, 55, 61, 62, 63, 64, 65, 66, 67, 68, 69,
             71, 73, 74, 75, 77, 79, 81, 82, 83, 84, 85, 86, 87, 88, 89, 91, 92, 93, 94, 95, 96, 97, 98, 99];
    if (!in_array((int) substr($telefone, 0, 2), $ddds, true)) {
        return false;
    }

    $numero = substr($telefone, 2);

    // Celular: 9 dígitos começando com 9. Fixo: 8 dígitos começando de 2 a 5
    return strlen($numero) === 9 ? $numero[0] === '9' : (bool) preg_match('/^[2-5]/', $numero);
}

// Valida CEP: 8 números, sem traço
function validarCEP($cep)
{
    return (bool) preg_match('/^[0-9]{8}$/', $cep);
}
