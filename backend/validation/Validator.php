<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validador de propósito geral para dados de formulário.
 *
 * Suporta regras: required, string, email, min:N, max:N, numeric,
 * in:val1,val2,..., date, file_max_size:N, mime_types:type1,type2,
 * unique, regex:pattern.
 *
 * Todas as mensagens de erro são em português brasileiro (pt-BR).
 */
class Validator
{
    /**
     * Valida os dados fornecidos contra um conjunto de regras.
     *
     * @param array<string, mixed>    $data  Dados a serem validados (campo => valor).
     * @param array<string, string[]> $rules Regras de validação (campo => [regras]).
     *
     * @return array<string, string[]> Erros agrupados por campo. Vazio se válido.
     */
    public function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule) {
                $error = $this->applyRule($field, $value, $rule, $data);

                if ($error !== null) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * Aplica uma regra individual a um valor e retorna a mensagem de erro ou null.
     *
     * @param string $field Nome do campo.
     * @param mixed  $value Valor do campo.
     * @param string $rule  Regra a ser aplicada (ex.: "required", "min:3").
     * @param array  $data  Todos os dados (para referência cruzada, se necessário).
     *
     * @return string|null Mensagem de erro em pt-BR ou null se a regra passou.
     */
    private function applyRule(string $field, mixed $value, string $rule, array $data): ?string
    {
        // Separa o nome da regra dos seus parâmetros (ex.: "min:3" → ["min", "3"])
        $parts     = explode(':', $rule, 2);
        $ruleName  = $parts[0];
        $parameter = $parts[1] ?? null;

        return match ($ruleName) {
            'required'      => $this->validateRequired($field, $value),
            'string'        => $this->validateString($field, $value),
            'email'         => $this->validateEmail($field, $value),
            'min'           => $this->validateMin($field, $value, (int) $parameter),
            'max'           => $this->validateMax($field, $value, (int) $parameter),
            'numeric'       => $this->validateNumeric($field, $value),
            'in'            => $this->validateIn($field, $value, $parameter ?? ''),
            'date'          => $this->validateDate($field, $value),
            'file_max_size' => $this->validateFileMaxSize($field, $value, (int) $parameter),
            'mime_types'    => $this->validateMimeTypes($field, $value, $parameter ?? ''),
            'unique'        => $this->validateUnique($field, $value),
            'regex'         => $this->validateRegex($field, $value, $parameter ?? ''),
            default         => null,
        };
    }

    /**
     * Valida que o campo é obrigatório e não está vazio.
     */
    private function validateRequired(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '' || (is_array($value) && count($value) === 0)) {
            return "O campo {$field} é obrigatório.";
        }

        return null;
    }

    /**
     * Valida que o valor é uma string.
     */
    private function validateString(string $field, mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !is_string($value)) {
            return "O campo {$field} deve ser um texto.";
        }

        return null;
    }

    /**
     * Valida que o valor é um e-mail válido.
     */
    private function validateEmail(string $field, mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return "O campo {$field} deve ser um endereço de e-mail válido.";
        }

        return null;
    }

    /**
     * Valida o tamanho mínimo (string = caracteres, numérico = valor).
     */
    private function validateMin(string $field, mixed $value, int $min): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && mb_strlen($value) < $min) {
            return "O campo {$field} deve ter no mínimo {$min} caracteres.";
        }

        if (is_numeric($value) && (float) $value < $min) {
            return "O campo {$field} deve ser no mínimo {$min}.";
        }

        return null;
    }

    /**
     * Valida o tamanho máximo (string = caracteres, numérico = valor).
     */
    private function validateMax(string $field, mixed $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value) && mb_strlen($value) > $max) {
            return "O campo {$field} deve ter no máximo {$max} caracteres.";
        }

        if (is_numeric($value) && (float) $value > $max) {
            return "O campo {$field} deve ser no máximo {$max}.";
        }

        return null;
    }

    /**
     * Valida que o valor é numérico.
     */
    private function validateNumeric(string $field, mixed $value): ?string
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            return "O campo {$field} deve ser um valor numérico.";
        }

        return null;
    }

    /**
     * Valida que o valor está em uma lista de opções permitidas.
     */
    private function validateIn(string $field, mixed $value, string $options): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $allowed = explode(',', $options);

        if (!in_array((string) $value, $allowed, true)) {
            $optionsList = implode(', ', $allowed);
            return "O campo {$field} deve ser um dos seguintes valores: {$optionsList}.";
        }

        return null;
    }

    /**
     * Valida que o valor é uma data válida (Y-m-d).
     */
    private function validateDate(string $field, mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d', (string) $value);

        if (!$date || $date->format('Y-m-d') !== (string) $value) {
            return "O campo {$field} deve ser uma data válida no formato AAAA-MM-DD.";
        }

        return null;
    }

    /**
     * Valida o tamanho máximo de um arquivo (em bytes).
     */
    private function validateFileMaxSize(string $field, mixed $value, int $maxBytes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $size = is_array($value) ? ($value['size'] ?? 0) : (int) $value;

        if ($size > $maxBytes) {
            $maxMb = round($maxBytes / (1024 * 1024), 2);
            return "O arquivo {$field} deve ter no máximo {$maxMb} MB.";
        }

        return null;
    }

    /**
     * Valida o tipo MIME de um arquivo.
     */
    private function validateMimeTypes(string $field, mixed $value, string $allowedTypes): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $mime = is_array($value) ? ($value['type'] ?? '') : (string) $value;
        $allowed = explode(',', $allowedTypes);

        if (!in_array($mime, $allowed, true)) {
            $typesList = implode(', ', $allowed);
            return "O campo {$field} deve ser do tipo: {$typesList}.";
        }

        return null;
    }

    /**
     * Placeholder para validação de unicidade (requer integração com banco de dados).
     */
    private function validateUnique(string $field, mixed $value): ?string
    {
        // Implementação real requer conexão com o banco de dados.
        // Este placeholder sempre retorna null (válido).
        // Integre com o repositório apropriado quando necessário.
        return null;
    }

    /**
     * Valida que o valor corresponde a uma expressão regular.
     */
    private function validateRegex(string $field, mixed $value, string $pattern): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!preg_match($pattern, (string) $value)) {
            return "O campo {$field} possui um formato inválido.";
        }

        return null;
    }
}
