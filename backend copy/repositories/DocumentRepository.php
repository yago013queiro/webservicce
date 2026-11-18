<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\BusinessDocument;
use PDO;

/**
 * Repositório de documentos de empresas.
 *
 * Gerencia operações de persistência para documentos enviados
 * durante o processo de verificação de empresas.
 */
class DocumentRepository
{
    /**
     * Instância PDO para acesso ao banco de dados.
     */
    private PDO $db;

    /**
     * @param PDO $db Conexão PDO ativa.
     */
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Busca um documento pelo ID.
     *
     * @param int $id ID do documento.
     * @return BusinessDocument|null Objeto BusinessDocument ou null se não encontrado.
     */
    public function findById(int $id): ?BusinessDocument
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_documents WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? BusinessDocument::fromArray($row) : null;
    }

    /**
     * Busca todos os documentos de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return BusinessDocument[] Lista de objetos BusinessDocument.
     */
    public function findByBusinessId(int $businessId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_documents WHERE business_id = :business_id ORDER BY created_at DESC'
        );
        $stmt->execute(['business_id' => $businessId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => BusinessDocument::fromArray($row), $rows);
    }

    /**
     * Cria um novo registro de documento no banco de dados.
     *
     * @param array $data Dados do documento (business_id, document_type, original_filename, storage_key, mime_type, file_size_bytes).
     * @return int ID do documento recém-criado.
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO business_documents (business_id, document_type, original_filename, storage_key, mime_type, file_size_bytes, status, created_at, updated_at)
             VALUES (:business_id, :document_type, :original_filename, :storage_key, :mime_type, :file_size_bytes, :status, NOW(), NOW())'
        );

        $stmt->execute([
            'business_id'       => $data['business_id'],
            'document_type'     => $data['document_type'],
            'original_filename' => $data['original_filename'],
            'storage_key'       => $data['storage_key'],
            'mime_type'         => $data['mime_type'],
            'file_size_bytes'   => $data['file_size_bytes'],
            'status'            => $data['status'] ?? 'pending',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Atualiza o status de um documento (aprovação/rejeição).
     *
     * @param int         $id              ID do documento.
     * @param string      $status          Novo status (approved, rejected, pending).
     * @param int|null    $reviewedBy      ID do administrador que revisou (opcional).
     * @param string|null $rejectionReason Motivo da rejeição (opcional).
     * @return bool True se ao menos uma linha foi afetada.
     */
    public function updateStatus(int $id, string $status, ?int $reviewedBy = null, ?string $rejectionReason = null): bool
    {
        $sql = 'UPDATE business_documents SET status = :status, updated_at = NOW()';
        $params = ['status' => $status, 'id' => $id];

        if ($reviewedBy !== null) {
            $sql .= ', reviewed_by = :reviewed_by, reviewed_at = NOW()';
            $params['reviewed_by'] = $reviewedBy;
        }

        if ($rejectionReason !== null) {
            $sql .= ', rejection_reason = :rejection_reason';
            $params['rejection_reason'] = $rejectionReason;
        }

        $sql .= ' WHERE id = :id';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * Exclui todos os documentos de uma empresa.
     *
     * @param int $businessId ID da empresa.
     * @return bool True se ao menos uma linha foi excluída.
     */
    public function deleteByBusinessId(int $businessId): bool
    {
        $stmt = $this->db->prepare(
            'DELETE FROM business_documents WHERE business_id = :business_id'
        );
        $stmt->execute(['business_id' => $businessId]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Busca todos os documentos com status pendente.
     *
     * @return BusinessDocument[] Lista de documentos pendentes.
     */
    public function findPendingDocuments(): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM business_documents WHERE status = :status ORDER BY created_at ASC'
        );
        $stmt->execute(['status' => 'pending']);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => BusinessDocument::fromArray($row), $rows);
    }
}
