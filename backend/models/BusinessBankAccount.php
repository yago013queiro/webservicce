<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de conta bancária empresarial do sistema de marketplace.
 *
 * Representa uma conta bancária associada a uma empresa,
 * incluindo dados do banco, agência, conta e verificação de titularidade.
 */
class BusinessBankAccount
{
    // Constantes de tipo de conta
    public const string TYPE_CORRENTE  = 'corrente';
    public const string TYPE_POUPANCA  = 'poupanca';

    // Constantes de tipo de titular
    public const string HOLDER_PF = 'PF';
    public const string HOLDER_PJ = 'PJ';

    // Constantes de status de verificação
    public const string STATUS_PENDING  = 'pending';
    public const string STATUS_VERIFIED = 'verified';
    public const string STATUS_FAILED   = 'failed';

    private ?int    $id;
    private ?int    $businessId;
    private string  $bankCode;
    private string  $bankName;
    private string  $branchNumber;
    private ?string $branchDigit;
    private string  $accountNumber;
    private ?string $accountDigit;
    private string  $accountType;
    private string  $accountHolderName;
    private ?string $accountHolderCpf;
    private ?string $accountHolderCnpj;
    private string  $holderEntityType;
    private string  $ownershipVerificationStatus;
    private ?string $verificationMethod;
    private ?string $verifiedAt;
    private ?string $verificationNotes;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de BusinessBankAccount a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados da conta bancária.
     */
    public function __construct(array $data)
    {
        $this->id                          = isset($data['id']) ? (int) $data['id'] : null;
        $this->businessId                  = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $this->bankCode                    = (string) ($data['bank_code'] ?? '');
        $this->bankName                    = (string) ($data['bank_name'] ?? '');
        $this->branchNumber                = (string) ($data['branch_number'] ?? '');
        $this->branchDigit                 = $data['branch_digit'] ?? null;
        $this->accountNumber               = (string) ($data['account_number'] ?? '');
        $this->accountDigit                = $data['account_digit'] ?? null;
        $this->accountType                 = (string) ($data['account_type'] ?? self::TYPE_CORRENTE);
        $this->accountHolderName           = (string) ($data['account_holder_name'] ?? '');
        $this->accountHolderCpf            = $data['account_holder_cpf'] ?? null;
        $this->accountHolderCnpj           = $data['account_holder_cnpj'] ?? null;
        $this->holderEntityType            = (string) ($data['holder_entity_type'] ?? self::HOLDER_PJ);
        $this->ownershipVerificationStatus = (string) ($data['ownership_verification_status'] ?? self::STATUS_PENDING);
        $this->verificationMethod          = $data['verification_method'] ?? null;
        $this->verifiedAt                  = $data['verified_at'] ?? null;
        $this->verificationNotes           = $data['verification_notes'] ?? null;
        $this->createdAt                   = $data['created_at'] ?? null;
        $this->updatedAt                   = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID da conta bancária.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retorna o ID da empresa associada.
     */
    public function getBusinessId(): ?int
    {
        return $this->businessId;
    }

    /**
     * Retorna o código do banco.
     */
    public function getBankCode(): string
    {
        return $this->bankCode;
    }

    /**
     * Retorna o nome do banco.
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * Retorna o número da agência.
     */
    public function getBranchNumber(): string
    {
        return $this->branchNumber;
    }

    /**
     * Retorna o dígito da agência.
     */
    public function getBranchDigit(): ?string
    {
        return $this->branchDigit;
    }

    /**
     * Retorna o número da conta.
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * Retorna o dígito da conta.
     */
    public function getAccountDigit(): ?string
    {
        return $this->accountDigit;
    }

    /**
     * Retorna o tipo da conta (corrente ou poupança).
     */
    public function getAccountType(): string
    {
        return $this->accountType;
    }

    /**
     * Retorna o nome do titular da conta.
     */
    public function getAccountHolderName(): string
    {
        return $this->accountHolderName;
    }

    /**
     * Retorna o CPF do titular da conta.
     */
    public function getAccountHolderCpf(): ?string
    {
        return $this->accountHolderCpf;
    }

    /**
     * Retorna o CNPJ do titular da conta.
     */
    public function getAccountHolderCnpj(): ?string
    {
        return $this->accountHolderCnpj;
    }

    /**
     * Retorna o tipo de entidade do titular (PF ou PJ).
     */
    public function getHolderEntityType(): string
    {
        return $this->holderEntityType;
    }

    /**
     * Retorna o status de verificação de titularidade.
     */
    public function getOwnershipVerificationStatus(): string
    {
        return $this->ownershipVerificationStatus;
    }

    /**
     * Retorna o método de verificação utilizado.
     */
    public function getVerificationMethod(): ?string
    {
        return $this->verificationMethod;
    }

    /**
     * Retorna a data/hora da verificação.
     */
    public function getVerifiedAt(): ?string
    {
        return $this->verifiedAt;
    }

    /**
     * Retorna as notas de verificação.
     */
    public function getVerificationNotes(): ?string
    {
        return $this->verificationNotes;
    }

    /**
     * Retorna a data/hora de criação do registro.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    /**
     * Retorna a data/hora da última atualização.
     */
    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    /**
     * Converte o modelo para array associativo.
     *
     * Mascara o número da conta na saída, exibindo apenas os dois últimos dígitos.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                            => $this->id,
            'business_id'                   => $this->businessId,
            'bank_code'                     => $this->bankCode,
            'bank_name'                     => $this->bankName,
            'branch_number'                 => $this->branchNumber,
            'branch_digit'                  => $this->branchDigit,
            'account_number'                => $this->maskAccountNumber(),
            'account_digit'                 => $this->accountDigit,
            'account_type'                  => $this->accountType,
            'account_holder_name'           => $this->accountHolderName,
            'account_holder_cpf'            => $this->accountHolderCpf,
            'account_holder_cnpj'           => $this->accountHolderCnpj,
            'holder_entity_type'            => $this->holderEntityType,
            'ownership_verification_status' => $this->ownershipVerificationStatus,
            'verification_method'           => $this->verificationMethod,
            'verified_at'                   => $this->verifiedAt,
            'verification_notes'            => $this->verificationNotes,
            'created_at'                    => $this->createdAt,
            'updated_at'                    => $this->updatedAt,
        ];
    }

    /**
     * Mascara o número da conta, exibindo apenas os dois últimos dígitos.
     *
     * @return string Número da conta mascarado (ex.: "****56").
     */
    private function maskAccountNumber(): string
    {
        $number = $this->accountNumber;

        if (strlen($number) <= 2) {
            return $number;
        }

        $lastTwo   = substr($number, -2);
        $maskLen   = strlen($number) - 2;
        $mask      = str_repeat('*', $maskLen);

        return $mask . $lastTwo;
    }
}
