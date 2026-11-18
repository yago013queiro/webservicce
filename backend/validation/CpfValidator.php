<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validador de CPF (Cadastro de Pessoas Físicas) brasileiro.
 *
 * Realiza validação algorítmica dos dois dígitos verificadores,
 * rejeita sequências inválidas conhecidas e fornece métodos utilitários
 * para formatação e sanitização.
 */
class CpfValidator
{
    /**
     * Sequências de CPF conhecidamente inválidas (todos os dígitos iguais).
     */
    private const array INVALID_SEQUENCES = [
        '00000000000',
        '11111111111',
        '22222222222',
        '33333333333',
        '44444444444',
        '55555555555',
        '66666666666',
        '77777777777',
        '88888888888',
        '99999999999',
    ];

    /**
     * Valida um CPF verificando formato, sequências inválidas e dígitos verificadores.
     *
     * @param string $cpf O CPF a ser validado (com ou sem formatação).
     *
     * @return bool True se o CPF for válido, false caso contrário.
     */
    public static function validate(string $cpf): bool
    {
        $cpf = self::sanitize($cpf);

        // Deve ter exatamente 11 dígitos
        if (strlen($cpf) !== 11 || !ctype_digit($cpf)) {
            return false;
        }

        // Rejeita sequências inválidas conhecidas
        if (in_array($cpf, self::INVALID_SEQUENCES, true)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }

        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $cpf[9] !== $firstDigit) {
            return false;
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }

        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $cpf[10] !== $secondDigit) {
            return false;
        }

        return true;
    }

    /**
     * Formata um CPF no padrão "000.000.000-00".
     *
     * @param string $cpf O CPF a ser formatado (com ou sem formatação prévia).
     *
     * @return string CPF formatado no padrão brasileiro.
     */
    public static function format(string $cpf): string
    {
        $cpf = self::sanitize($cpf);

        return substr($cpf, 0, 3) . '.' .
               substr($cpf, 3, 3) . '.' .
               substr($cpf, 6, 3) . '-' .
               substr($cpf, 9, 2);
    }

    /**
     * Remove formatação do CPF, retornando apenas os dígitos.
     *
     * @param string $cpf O CPF a ser sanitizado.
     *
     * @return string CPF contendo apenas dígitos numéricos.
     */
    public static function sanitize(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf) ?? '';
    }
}
