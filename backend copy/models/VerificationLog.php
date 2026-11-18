<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de log de verificação do sistema de marketplace.
 *
 * Registra todas as ações realizadas durante o processo de verificação
 * de empresas, incluindo submissões, aprovações, rejeições e mudanças de status.
 */
class VerificationLog
{
    // Constantes de tipo de ação
    public const string REGISTRATION_SUBMITTED  = 'registration_submitted';
    public const string CNPJ_VERIFIED           = 'cnpj_verified';
    public const string CNPJ_INVALID            = 'cnpj_invalid';
    public const string DOCUMENT_UPLOADED        = 'document_uploaded';
    public const string DOCUMENT_APPROVED        = 'document_approved';
    public const string DOCUMENT_REJECTED        = 'document_rejected';
    public const string DOCUMENTS_REQUESTED      = 'documents_requested';
    public const string BUSINESS_APPROVED        = 'business_approved';
    public const string BUSINESS_REJECTED        = 'business_rejected';
    public const string BUSINESS_SUSPENDED       = 'business_suspended';
    public const string BUSINESS_REACTIVATED     = 'business_reactivated';
    public const string STATUS_CHANGED           = 'status_changed';
    public const string BANK_ACCOUNT_VERIFIED    = 'bank_account_verified';
    public const string BANK_ACCOUNT_REJECTED    = 'bank_account_rejected';
    public const string ENTREPRENEUR_VERIFIED    = 'entrepreneur_verified';
    public const string ENTREPRENEUR_FAILED      = 'entrepreneur_failed';
    public const string UNDER_ANALYSIS           = 'under_analysis';
    public const string NOTES_ADDED              = 'notes_added';

    private ?int    $id;
    private ?int    $businessId;
    private ?int    $administratorUserId;
    private string  $actionType;
    private ?string $previousStatus;
    private ?string $newStatus;
    private ?string $reason;
    private ?string $createdAt;

    /**
     * Cria uma instância de VerificationLog a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados do log de verificação.
     */
    public function __construct(array $data)
    {
        $this->id                  = isset($data['id']) ? (int) $data['id'] : null;
        $this->businessId          = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $this->administratorUserId = isset($data['administrator_user_id']) ? (int) $data['administrator_user_id'] : null;
        $this->actionType          = (string) ($data['action_type'] ?? '');
        $this->previousStatus      = $data['previous_status'] ?? null;
        $this->newStatus           = $data['new_status'] ?? null;
        $this->reason              = $data['reason'] ?? null;
        $this->createdAt           = $data['created_at'] ?? null;
    }

    /**
     * Retorna o ID do log.
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
     * Retorna o ID do administrador que realizou a ação.
     */
    public function getAdministratorUserId(): ?int
    {
        return $this->administratorUserId;
    }

    /**
     * Retorna o tipo de ação registrada.
     */
    public function getActionType(): string
    {
        return $this->actionType;
    }

    /**
     * Retorna o status anterior à ação.
     */
    public function getPreviousStatus(): ?string
    {
        return $this->previousStatus;
    }

    /**
     * Retorna o novo status após a ação.
     */
    public function getNewStatus(): ?string
    {
        return $this->newStatus;
    }

    /**
     * Retorna o motivo/justificativa da ação.
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * Retorna a data/hora de criação do registro.
     */
    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
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
            'business_id'           => $this->businessId,
            'administrator_user_id' => $this->administratorUserId,
            'action_type'           => $this->actionType,
            'previous_status'       => $this->previousStatus,
            'new_status'            => $this->newStatus,
            'reason'                => $this->reason,
            'created_at'            => $this->createdAt,
        ];
    }
}
