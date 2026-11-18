<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de dados do empreendedor/representante legal.
 *
 * Representa os dados pessoais e documentais do responsável
 * pela empresa, incluindo CPF, RG e papel no negócio.
 */
class EntrepreneurData
{
    // Constantes de papel do representante
    public const string ROLE_OWNER                = 'owner';
    public const string ROLE_LEGAL_REPRESENTATIVE = 'legal_representative';
    public const string ROLE_PARTNER              = 'partner';

    // Constantes de status de verificação
    public const string STATUS_PENDING  = 'pending';
    public const string STATUS_VERIFIED = 'verified';
    public const string STATUS_FAILED   = 'failed';

    private ?int    $id;
    private ?int    $businessId;
    private string  $fullName;
    private string  $cpf;
    private ?string $rgNumber;
    private ?string $rgIssuingAuthority;
    private ?string $rgIssuingState;
    private ?string $dateOfBirth;
    private string  $representativeRole;
    private string  $verificationStatus;
    private ?string $verifiedAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de EntrepreneurData a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados do empreendedor.
     */
    public function __construct(array $data)
    {
        $this->id                 = isset($data['id']) ? (int) $data['id'] : null;
        $this->businessId         = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $this->fullName           = (string) ($data['full_name'] ?? '');
        $this->cpf                = (string) ($data['cpf'] ?? '');
        $this->rgNumber           = $data['rg_number'] ?? null;
        $this->rgIssuingAuthority = $data['rg_issuing_authority'] ?? null;
        $this->rgIssuingState     = $data['rg_issuing_state'] ?? null;
        $this->dateOfBirth        = $data['date_of_birth'] ?? null;
        $this->representativeRole = (string) ($data['representative_role'] ?? self::ROLE_OWNER);
        $this->verificationStatus = (string) ($data['verification_status'] ?? self::STATUS_PENDING);
        $this->verifiedAt         = $data['verified_at'] ?? null;
        $this->createdAt          = $data['created_at'] ?? null;
        $this->updatedAt          = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID do registro.
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
     * Retorna o nome completo do empreendedor.
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * Retorna o CPF (armazenado como string para preservar zeros à esquerda).
     */
    public function getCpf(): string
    {
        return $this->cpf;
    }

    /**
     * Retorna o número do RG.
     */
    public function getRgNumber(): ?string
    {
        return $this->rgNumber;
    }

    /**
     * Retorna o órgão emissor do RG.
     */
    public function getRgIssuingAuthority(): ?string
    {
        return $this->rgIssuingAuthority;
    }

    /**
     * Retorna o estado emissor do RG (UF).
     */
    public function getRgIssuingState(): ?string
    {
        return $this->rgIssuingState;
    }

    /**
     * Retorna a data de nascimento.
     */
    public function getDateOfBirth(): ?string
    {
        return $this->dateOfBirth;
    }

    /**
     * Retorna o papel do representante na empresa.
     */
    public function getRepresentativeRole(): string
    {
        return $this->representativeRole;
    }

    /**
     * Retorna o status de verificação.
     */
    public function getVerificationStatus(): string
    {
        return $this->verificationStatus;
    }

    /**
     * Retorna a data/hora da verificação.
     */
    public function getVerifiedAt(): ?string
    {
        return $this->verifiedAt;
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
     * Mascara o CPF na saída, exibindo apenas os dois últimos dígitos
     * no formato: ***.***.***-XX
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                   => $this->id,
            'business_id'          => $this->businessId,
            'full_name'            => $this->fullName,
            'cpf'                  => $this->maskCpf(),
            'rg_number'            => $this->rgNumber,
            'rg_issuing_authority' => $this->rgIssuingAuthority,
            'rg_issuing_state'     => $this->rgIssuingState,
            'date_of_birth'        => $this->dateOfBirth,
            'representative_role'  => $this->representativeRole,
            'verification_status'  => $this->verificationStatus,
            'verified_at'          => $this->verifiedAt,
            'created_at'           => $this->createdAt,
            'updated_at'           => $this->updatedAt,
        ];
    }

    /**
     * Mascara o CPF exibindo apenas os dois últimos dígitos.
     *
     * @return string CPF mascarado no formato "***.***.***-XX".
     */
    private function maskCpf(): string
    {
        $sanitized = preg_replace('/\D/', '', $this->cpf) ?? '';

        if (strlen($sanitized) < 2) {
            return '***.***.***-**';
        }

        $lastTwo = substr($sanitized, -2);

        return "***.***.***-{$lastTwo}";
    }
}
