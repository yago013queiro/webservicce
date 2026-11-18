<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de empresa (negócio) do sistema de marketplace.
 *
 * Representa uma empresa cadastrada com todos os seus dados empresariais,
 * incluindo CNPJ, porte, natureza jurídica, categoria, CNAEs,
 * status de registro e verificação.
 */
class Business
{
    // Constantes de status de registro
    public const string STATUS_DRAFT               = 'draft';
    public const string STATUS_PENDING_REVIEW       = 'pending_review';
    public const string STATUS_UNDER_ANALYSIS       = 'under_analysis';
    public const string STATUS_DOCUMENTS_REQUESTED  = 'documents_requested';
    public const string STATUS_APPROVED             = 'approved';
    public const string STATUS_REJECTED             = 'rejected';
    public const string STATUS_SUSPENDED            = 'suspended';

    // Constantes de status do CNPJ
    public const string CNPJ_PENDING  = 'pending';
    public const string CNPJ_VERIFIED = 'verified';
    public const string CNPJ_INVALID  = 'invalid';

    // Constantes de porte da empresa
    public const string SIZE_MEI    = 'MEI';
    public const string SIZE_ME     = 'ME';
    public const string SIZE_EPP    = 'EPP';
    public const string SIZE_MEDIO  = 'MEDIO';
    public const string SIZE_GRANDE = 'GRANDE';

    private ?int    $id;
    private ?int    $ownerUserId;
    private string  $legalName;
    private string  $tradeName;
    private string  $cnpj;
    private ?string $companySize;
    private ?string $legalEntityType;
    private ?string $businessCategory;
    private ?string $primaryCnae;
    private ?string $secondaryCnaes;
    private ?string $stateRegistration;
    private ?string $municipalRegistration;
    private string  $cnpjStatus;
    private ?string $cnpjVerifiedAt;
    private string  $registrationStatus;
    private ?string $verificationNotes;
    private ?string $submittedAt;
    private ?string $approvedAt;
    private ?string $rejectedAt;
    private ?string $createdAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de Business a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados da empresa.
     */
    public function __construct(array $data)
    {
        $this->id                    = isset($data['id']) ? (int) $data['id'] : null;
        $this->ownerUserId           = isset($data['owner_user_id']) ? (int) $data['owner_user_id'] : null;
        $this->legalName             = (string) ($data['legal_name'] ?? '');
        $this->tradeName             = (string) ($data['trade_name'] ?? '');
        $this->cnpj                  = (string) ($data['cnpj'] ?? '');
        $this->companySize           = $data['company_size'] ?? null;
        $this->legalEntityType       = $data['legal_entity_type'] ?? null;
        $this->businessCategory      = $data['business_category'] ?? null;
        $this->primaryCnae           = $data['primary_cnae'] ?? null;
        $this->secondaryCnaes        = $data['secondary_cnaes'] ?? null;
        $this->stateRegistration     = $data['state_registration'] ?? null;
        $this->municipalRegistration = $data['municipal_registration'] ?? null;
        $this->cnpjStatus            = (string) ($data['cnpj_status'] ?? self::CNPJ_PENDING);
        $this->cnpjVerifiedAt        = $data['cnpj_verified_at'] ?? null;
        $this->registrationStatus    = (string) ($data['registration_status'] ?? self::STATUS_DRAFT);
        $this->verificationNotes     = $data['verification_notes'] ?? null;
        $this->submittedAt           = $data['submitted_at'] ?? null;
        $this->approvedAt            = $data['approved_at'] ?? null;
        $this->rejectedAt            = $data['rejected_at'] ?? null;
        $this->createdAt             = $data['created_at'] ?? null;
        $this->updatedAt             = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID da empresa.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retorna o ID do usuário proprietário.
     */
    public function getOwnerUserId(): ?int
    {
        return $this->ownerUserId;
    }

    /**
     * Retorna a razão social.
     */
    public function getLegalName(): string
    {
        return $this->legalName;
    }

    /**
     * Retorna o nome fantasia.
     */
    public function getTradeName(): string
    {
        return $this->tradeName;
    }

    /**
     * Retorna o CNPJ (armazenado como string para preservar zeros à esquerda).
     */
    public function getCnpj(): string
    {
        return $this->cnpj;
    }

    /**
     * Retorna o porte da empresa.
     */
    public function getCompanySize(): ?string
    {
        return $this->companySize;
    }

    /**
     * Retorna a natureza jurídica.
     */
    public function getLegalEntityType(): ?string
    {
        return $this->legalEntityType;
    }

    /**
     * Retorna a categoria de negócio.
     */
    public function getBusinessCategory(): ?string
    {
        return $this->businessCategory;
    }

    /**
     * Retorna o CNAE principal.
     */
    public function getPrimaryCnae(): ?string
    {
        return $this->primaryCnae;
    }

    /**
     * Retorna os CNAEs secundários.
     */
    public function getSecondaryCnaes(): ?string
    {
        return $this->secondaryCnaes;
    }

    /**
     * Retorna a inscrição estadual.
     */
    public function getStateRegistration(): ?string
    {
        return $this->stateRegistration;
    }

    /**
     * Retorna a inscrição municipal.
     */
    public function getMunicipalRegistration(): ?string
    {
        return $this->municipalRegistration;
    }

    /**
     * Retorna o status de verificação do CNPJ.
     */
    public function getCnpjStatus(): string
    {
        return $this->cnpjStatus;
    }

    /**
     * Retorna a data/hora de verificação do CNPJ.
     */
    public function getCnpjVerifiedAt(): ?string
    {
        return $this->cnpjVerifiedAt;
    }

    /**
     * Retorna o status de registro da empresa.
     */
    public function getRegistrationStatus(): string
    {
        return $this->registrationStatus;
    }

    /**
     * Retorna as notas de verificação.
     */
    public function getVerificationNotes(): ?string
    {
        return $this->verificationNotes;
    }

    /**
     * Retorna a data/hora de submissão.
     */
    public function getSubmittedAt(): ?string
    {
        return $this->submittedAt;
    }

    /**
     * Retorna a data/hora de aprovação.
     */
    public function getApprovedAt(): ?string
    {
        return $this->approvedAt;
    }

    /**
     * Retorna a data/hora de rejeição.
     */
    public function getRejectedAt(): ?string
    {
        return $this->rejectedAt;
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'                    => $this->id,
            'owner_user_id'         => $this->ownerUserId,
            'legal_name'            => $this->legalName,
            'trade_name'            => $this->tradeName,
            'cnpj'                  => $this->cnpj,
            'company_size'          => $this->companySize,
            'legal_entity_type'     => $this->legalEntityType,
            'business_category'     => $this->businessCategory,
            'primary_cnae'          => $this->primaryCnae,
            'secondary_cnaes'       => $this->secondaryCnaes,
            'state_registration'    => $this->stateRegistration,
            'municipal_registration'=> $this->municipalRegistration,
            'cnpj_status'           => $this->cnpjStatus,
            'cnpj_verified_at'      => $this->cnpjVerifiedAt,
            'registration_status'   => $this->registrationStatus,
            'verification_notes'    => $this->verificationNotes,
            'submitted_at'          => $this->submittedAt,
            'approved_at'           => $this->approvedAt,
            'rejected_at'           => $this->rejectedAt,
            'created_at'            => $this->createdAt,
            'updated_at'            => $this->updatedAt,
        ];
    }

    /**
     * Verifica se a empresa está com registro pendente de revisão.
     */
    public function isPending(): bool
    {
        return in_array($this->registrationStatus, [
            self::STATUS_PENDING_REVIEW,
            self::STATUS_UNDER_ANALYSIS,
            self::STATUS_DOCUMENTS_REQUESTED,
        ], true);
    }

    /**
     * Verifica se a empresa está aprovada.
     */
    public function isApproved(): bool
    {
        return $this->registrationStatus === self::STATUS_APPROVED;
    }

    /**
     * Verifica se a empresa foi rejeitada.
     */
    public function isRejected(): bool
    {
        return $this->registrationStatus === self::STATUS_REJECTED;
    }

    /**
     * Verifica se a empresa pode ser editada (apenas rascunho ou documentos solicitados).
     */
    public function canEdit(): bool
    {
        return in_array($this->registrationStatus, [
            self::STATUS_DRAFT,
            self::STATUS_DOCUMENTS_REQUESTED,
        ], true);
    }
}
