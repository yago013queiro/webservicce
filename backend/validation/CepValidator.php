<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validador de CEP (Código de Endereçamento Postal) brasileiro.
 *
 * Valida o formato de 8 dígitos (com ou sem hífen) e fornece
 * métodos utilitários para formatação e sanitização.
 */
class CepValidator
{
    /**
     * Valida um CEP brasileiro.
     *
     * Aceita formatos: "12345678" ou "12345-678".
     *
     * @param string $cep O CEP a ser validado.
     *
     * @return bool True se o CEP for válido, false caso contrário.
     */
    public static function validate(string $cep): bool
    {
        $cep = self::sanitize($cep);

        // Deve ter exatamente 8 dígitos
        if (strlen($cep) !== 8 || !ctype_digit($cep)) {
            return false;
        }

        // CEP não pode ser composto apenas por zeros
        if ($cep === '00000000') {
            return false;
        }

        return true;
    }

    /**
     * Formata um CEP no padrão "00000-000".
     *
     * @param string $cep O CEP a ser formatado.
     *
     * @return string CEP formatado no padrão brasileiro.
     */
    public static function format(string $cep): string
    {
        $cep = self::sanitize($cep);

        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    /**
     * Remove formatação do CEP, retornando apenas os dígitos.
     *
     * @param string $cep O CEP a ser sanitizado.
     *
     * @return string CEP contendo apenas dígitos numéricos.
     */
    public static function sanitize(string $cep): string
    {
        return preg_replace('/\D/', '', $cep) ?? '';
    }
}
