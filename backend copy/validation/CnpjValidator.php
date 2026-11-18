<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validador de CNPJ (Cadastro Nacional da Pessoa Jurídica) brasileiro.
 *
 * Realiza validação algorítmica dos dois dígitos verificadores usando
 * os pesos multiplicadores corretos, rejeita sequências inválidas
 * e fornece métodos utilitários para formatação e sanitização.
 */
class CnpjValidator
{
    /**
     * Sequências de CNPJ conhecidamente inválidas (todos os dígitos iguais).
     */
    private const array INVALID_SEQUENCES = [
        '00000000000000',
        '11111111111111',
        '22222222222222',
        '33333333333333',
        '44444444444444',
        '55555555555555',
        '66666666666666',
        '77777777777777',
        '88888888888888',
        '99999999999999',
    ];

    /**
     * Pesos multiplicadores para o cálculo do primeiro dígito verificador.
     */
    private const array FIRST_DIGIT_WEIGHTS = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Pesos multiplicadores para o cálculo do segundo dígito verificador.
     */
    private const array SECOND_DIGIT_WEIGHTS = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Valida um CNPJ verificando formato, sequências inválidas e dígitos verificadores.
     *
     * @param string $cnpj O CNPJ a ser validado (com ou sem formatação).
     *
     * @return bool True se o CNPJ for válido, false caso contrário.
     */
    public static function validate(string $cnpj): bool
    {
        $cnpj = self::sanitize($cnpj);

        // Deve ter exatamente 14 dígitos
        if (strlen($cnpj) !== 14 || !ctype_digit($cnpj)) {
            return false;
        }

        // Rejeita sequências inválidas conhecidas
        if (in_array($cnpj, self::INVALID_SEQUENCES, true)) {
            return false;
        }

        // Validação do primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * self::FIRST_DIGIT_WEIGHTS[$i];
        }

        $remainder = $sum % 11;
        $firstDigit = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $cnpj[12] !== $firstDigit) {
            return false;
        }

        // Validação do segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * self::SECOND_DIGIT_WEIGHTS[$i];
        }

        $remainder = $sum % 11;
        $secondDigit = ($remainder < 2) ? 0 : (11 - $remainder);

        if ((int) $cnpj[13] !== $secondDigit) {
            return false;
        }

        return true;
    }

    /**
     * Formata um CNPJ no padrão "00.000.000/0000-00".
     *
     * @param string $cnpj O CNPJ a ser formatado (com ou sem formatação prévia).
     *
     * @return string CNPJ formatado no padrão brasileiro.
     */
    public static function format(string $cnpj): string
    {
        $cnpj = self::sanitize($cnpj);

        return substr($cnpj, 0, 2) . '.' .
               substr($cnpj, 2, 3) . '.' .
               substr($cnpj, 5, 3) . '/' .
               substr($cnpj, 8, 4) . '-' .
               substr($cnpj, 12, 2);
    }

    /**
     * Remove formatação do CNPJ, retornando apenas os dígitos.
     *
     * @param string $cnpj O CNPJ a ser sanitizado.
     *
     * @return string CNPJ contendo apenas dígitos numéricos.
     */
    public static function sanitize(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj) ?? '';
    }
}
