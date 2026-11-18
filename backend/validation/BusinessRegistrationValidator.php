<?php

declare(strict_types=1);

namespace App\Validation;

/**
 * Validador especializado para o formulário de registro de empresa.
 *
 * Valida todos os campos obrigatórios do cadastro empresarial,
 * incluindo nome fantasia, razão social, CNPJ, porte da empresa,
 * natureza jurídica, categoria de negócio e registros estadual/municipal.
 *
 * Utiliza o CnpjValidator internamente para validação do CNPJ.
 * Todas as mensagens de erro são em português brasileiro (pt-BR).
 */
class BusinessRegistrationValidator
{
    /**
     * Portes de empresa permitidos.
     */
    private const array ALLOWED_COMPANY_SIZES = ['MEI', 'ME', 'EPP', 'MEDIO', 'GRANDE'];

    /**
     * Naturezas jurídicas permitidas.
     */
    private const array ALLOWED_LEGAL_ENTITY_TYPES = ['EI', 'EIRELI', 'LTDA', 'SLU', 'SA', 'MEI'];

    /**
     * Categorias de negócio permitidas.
     */
    private const array ALLOWED_BUSINESS_CATEGORIES = [
        'alimentacao',
        'supermercados',
        'farmacias',
        'roupas',
        'eletronicos',
        'beleza',
        'casa',
        'outros',
    ];

    /**
     * Valida os dados de registro de empresa.
     *
     * @param array<string, mixed> $data Dados do formulário de registro.
     *
     * @return array<string, string[]> Erros agrupados por campo. Vazio se válido.
     */
    public function validate(array $data): array
    {
        $errors = [];

        // Nome Fantasia
        $this->validateTradeName($data, $errors);

        // Razão Social
        $this->validateLegalName($data, $errors);

        // CNPJ
        $this->validateCnpj($data, $errors);

        // Porte da Empresa
        $this->validateCompanySize($data, $errors);

        // Natureza Jurídica
        $this->validateLegalEntityType($data, $errors);

        // Categoria de Negócio
        $this->validateBusinessCategory($data, $errors);

        // Inscrição Estadual (opcional)
        $this->validateStateRegistration($data, $errors);

        // Inscrição Municipal (opcional)
        $this->validateMunicipalRegistration($data, $errors);

        return $errors;
    }

    /**
     * Valida o nome fantasia (trade_name).
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateTradeName(array $data, array &$errors): void
    {
        $value = $data['trade_name'] ?? null;

        if ($value === null || $value === '') {
            $errors['trade_name'][] = 'O nome fantasia é obrigatório.';
            return;
        }

        if (!is_string($value)) {
            $errors['trade_name'][] = 'O nome fantasia deve ser um texto.';
            return;
        }

        if (mb_strlen($value) < 3) {
            $errors['trade_name'][] = 'O nome fantasia deve ter no mínimo 3 caracteres.';
        }

        if (mb_strlen($value) > 255) {
            $errors['trade_name'][] = 'O nome fantasia deve ter no máximo 255 caracteres.';
        }
    }

    /**
     * Valida a razão social (legal_name).
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateLegalName(array $data, array &$errors): void
    {
        $value = $data['legal_name'] ?? null;

        if ($value === null || $value === '') {
            $errors['legal_name'][] = 'A razão social é obrigatória.';
            return;
        }

        if (!is_string($value)) {
            $errors['legal_name'][] = 'A razão social deve ser um texto.';
            return;
        }

        if (mb_strlen($value) < 3) {
            $errors['legal_name'][] = 'A razão social deve ter no mínimo 3 caracteres.';
        }

        if (mb_strlen($value) > 255) {
            $errors['legal_name'][] = 'A razão social deve ter no máximo 255 caracteres.';
        }
    }

    /**
     * Valida o CNPJ utilizando o CnpjValidator.
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateCnpj(array $data, array &$errors): void
    {
        $value = $data['cnpj'] ?? null;

        if ($value === null || $value === '') {
            $errors['cnpj'][] = 'O CNPJ é obrigatório.';
            return;
        }

        if (!CnpjValidator::validate((string) $value)) {
            $errors['cnpj'][] = 'O CNPJ informado é inválido.';
        }
    }

    /**
     * Valida o porte da empresa (company_size).
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateCompanySize(array $data, array &$errors): void
    {
        $value = $data['company_size'] ?? null;

        if ($value === null || $value === '') {
            $errors['company_size'][] = 'O porte da empresa é obrigatório.';
            return;
        }

        if (!in_array((string) $value, self::ALLOWED_COMPANY_SIZES, true)) {
            $allowed = implode(', ', self::ALLOWED_COMPANY_SIZES);
            $errors['company_size'][] = "O porte da empresa deve ser um dos seguintes: {$allowed}.";
        }
    }

    /**
     * Valida a natureza jurídica (legal_entity_type).
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateLegalEntityType(array $data, array &$errors): void
    {
        $value = $data['legal_entity_type'] ?? null;

        if ($value === null || $value === '') {
            $errors['legal_entity_type'][] = 'A natureza jurídica é obrigatória.';
            return;
        }

        if (!in_array((string) $value, self::ALLOWED_LEGAL_ENTITY_TYPES, true)) {
            $allowed = implode(', ', self::ALLOWED_LEGAL_ENTITY_TYPES);
            $errors['legal_entity_type'][] = "A natureza jurídica deve ser uma das seguintes: {$allowed}.";
        }
    }

    /**
     * Valida a categoria de negócio (business_category).
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateBusinessCategory(array $data, array &$errors): void
    {
        $value = $data['business_category'] ?? null;

        if ($value === null || $value === '') {
            $errors['business_category'][] = 'A categoria de negócio é obrigatória.';
            return;
        }

        if (!in_array((string) $value, self::ALLOWED_BUSINESS_CATEGORIES, true)) {
            $allowed = implode(', ', self::ALLOWED_BUSINESS_CATEGORIES);
            $errors['business_category'][] = "A categoria de negócio deve ser uma das seguintes: {$allowed}.";
        }
    }

    /**
     * Valida a inscrição estadual (state_registration) — campo opcional.
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateStateRegistration(array $data, array &$errors): void
    {
        $value = $data['state_registration'] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            $errors['state_registration'][] = 'A inscrição estadual deve ser um texto.';
            return;
        }

        if (mb_strlen($value) > 30) {
            $errors['state_registration'][] = 'A inscrição estadual deve ter no máximo 30 caracteres.';
        }
    }

    /**
     * Valida a inscrição municipal (municipal_registration) — campo opcional.
     *
     * @param array<string, mixed>    $data   Dados do formulário.
     * @param array<string, string[]> $errors Array de erros (por referência).
     */
    private function validateMunicipalRegistration(array $data, array &$errors): void
    {
        $value = $data['municipal_registration'] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            $errors['municipal_registration'][] = 'A inscrição municipal deve ser um texto.';
            return;
        }

        if (mb_strlen($value) > 30) {
            $errors['municipal_registration'][] = 'A inscrição municipal deve ter no máximo 30 caracteres.';
        }
    }
}
