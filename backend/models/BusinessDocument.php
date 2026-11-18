<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modelo de documento empresarial do sistema de marketplace.
 *
 * Representa um documento enviado por uma empresa durante o processo
 * de verificação, incluindo tipo, status de revisão e metadados do arquivo.
 */
class BusinessDocument
{
    // Constantes de tipo de documento
    public const string TYPE_CNPJ_CARD       = 'cnpj_card';
    public const string TYPE_ALVARA          = 'alvara';
    public const string TYPE_IDENTITY_DOC    = 'identity_doc';
    public const string TYPE_SOCIAL_CONTRACT = 'social_contract';
    public const string TYPE_ADDRESS_PROOF   = 'address_proof';
    public const string TYPE_OTHER           = 'other';

    // Constantes de status do documento
    public const string STATUS_PENDING  = 'pending';
    public const string STATUS_APPROVED = 'approved';
    public const string STATUS_REJECTED = 'rejected';

    private ?int    $id;
    private ?int    $businessId;
    private string  $documentType;
    private string  $originalFilename;
    private string  $storageKey;
    private string  $mimeType;
    private int     $fileSize;
    private string  $documentStatus;
    private ?string $issuedAt;
    private ?string $expiresAt;
    private ?int    $reviewedBy;
    private ?string $reviewedAt;
    private ?string $rejectionReason;
    private ?string $uploadedAt;
    private ?string $updatedAt;

    /**
     * Cria uma instância de BusinessDocument a partir de um array associativo.
     *
     * @param array<string, mixed> $data Dados do documento.
     */
    public function __construct(array $data)
    {
        $this->id               = isset($data['id']) ? (int) $data['id'] : null;
        $this->businessId       = isset($data['business_id']) ? (int) $data['business_id'] : null;
        $this->documentType     = (string) ($data['document_type'] ?? '');
        $this->originalFilename = (string) ($data['original_filename'] ?? '');
        $this->storageKey       = (string) ($data['storage_key'] ?? '');
        $this->mimeType         = (string) ($data['mime_type'] ?? '');
        $this->fileSize         = (int) ($data['file_size'] ?? 0);
        $this->documentStatus   = (string) ($data['document_status'] ?? self::STATUS_PENDING);
        $this->issuedAt         = $data['issued_at'] ?? null;
        $this->expiresAt        = $data['expires_at'] ?? null;
        $this->reviewedBy       = isset($data['reviewed_by']) ? (int) $data['reviewed_by'] : null;
        $this->reviewedAt       = $data['reviewed_at'] ?? null;
        $this->rejectionReason  = $data['rejection_reason'] ?? null;
        $this->uploadedAt       = $data['uploaded_at'] ?? null;
        $this->updatedAt        = $data['updated_at'] ?? null;
    }

    /**
     * Retorna o ID do documento.
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
     * Retorna o tipo do documento.
     */
    public function getDocumentType(): string
    {
        return $this->documentType;
    }

    /**
     * Retorna o nome original do arquivo.
     */
    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    /**
     * Retorna a chave de armazenamento do arquivo.
     */
    public function getStorageKey(): string
    {
        return $this->storageKey;
    }

    /**
     * Retorna o tipo MIME do arquivo.
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    /**
     * Retorna o tamanho do arquivo em bytes.
     */
    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    /**
     * Retorna o status do documento.
     */
    public function getDocumentStatus(): string
    {
        return $this->documentStatus;
    }

    /**
     * Retorna a data de emissão do documento.
     */
    public function getIssuedAt(): ?string
    {
        return $this->issuedAt;
    }

    /**
     * Retorna a data de validade do documento.
     */
    public function getExpiresAt(): ?string
    {
        return $this->expiresAt;
    }

    /**
     * Retorna o ID do revisor.
     */
    public function getReviewedBy(): ?int
    {
        return $this->reviewedBy;
    }

    /**
     * Retorna a data/hora da revisão.
     */
    public function getReviewedAt(): ?string
    {
        return $this->reviewedAt;
    }

    /**
     * Retorna o motivo da rejeição.
     */
    public function getRejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    /**
     * Retorna a data/hora do upload.
     */
    public function getUploadedAt(): ?string
    {
        return $this->uploadedAt;
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
            'id'                => $this->id,
            'business_id'       => $this->businessId,
            'document_type'     => $this->documentType,
            'original_filename' => $this->originalFilename,
            'storage_key'       => $this->storageKey,
            'mime_type'         => $this->mimeType,
            'file_size'         => $this->fileSize,
            'document_status'   => $this->documentStatus,
            'issued_at'         => $this->issuedAt,
            'expires_at'        => $this->expiresAt,
            'reviewed_by'       => $this->reviewedBy,
            'reviewed_at'       => $this->reviewedAt,
            'rejection_reason'  => $this->rejectionReason,
            'uploaded_at'       => $this->uploadedAt,
            'updated_at'        => $this->updatedAt,
        ];
    }
}
