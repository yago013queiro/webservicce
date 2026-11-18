<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\BusinessRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\VerificationLogRepository;
use App\Repositories\EntrepreneurRepository;
use App\Repositories\BankAccountRepository;
use App\Repositories\AddressRepository;
use App\Core\Database;

/**
 * Serviço de verificação administrativa.
 *
 * Gerencia o fluxo de aprovação/rejeição de empresas pelo lado
 * administrativo, incluindo revisão de documentos, transições de status
 * e geração de logs de auditoria.
 */
class VerificationService
{
    /**
     * Transições de status permitidas.
     * Mapeamento: status_atual => [status_permitidos].
     */
    private const ALLOWED_TRANSITIONS = [
        'pending_review'      => ['approved', 'rejected', 'documents_requested'],
        'documents_requested' => ['pending_review'],
        'approved'            => ['suspended'],
        'suspended'           => ['approved'],
    ];

    /**
     * @param BusinessRepository        $businessRepo     Repositório de empresas.
     * @param DocumentRepository        $documentRepo     Repositório de documentos.
     * @param VerificationLogRepository $logRepo          Repositório de logs.
     * @param EntrepreneurRepository    $entrepreneurRepo Repositório de empreendedores.
     * @param BankAccountRepository     $bankAccountRepo  Repositório de contas bancárias.
     */
    public function __construct(
        private BusinessRepository $businessRepo,
        private DocumentRepository $documentRepo,
        private VerificationLogRepository $logRepo,
        private EntrepreneurRepository $entrepreneurRepo,
        private BankAccountRepository $bankAccountRepo,
    ) {
    }

    /**
     * Aprova um documento enviado pela empresa.
     *
     * @param int $documentId ID do documento.
     * @param int $adminId    ID do administrador que está aprovando.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function approveDocument(int $documentId, int $adminId): array
    {
        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            return ['success' => false, 'error' => 'Documento não encontrado.'];
        }

        if ($document->status !== 'pending') {
            return ['success' => false, 'error' => 'Este documento já foi revisado.'];
        }

        $this->documentRepo->updateStatus($documentId, 'approved', $adminId);

        return ['success' => true];
    }

    /**
     * Rejeita um documento enviado pela empresa.
     *
     * @param int    $documentId ID do documento.
     * @param int    $adminId    ID do administrador que está rejeitando.
     * @param string $reason     Motivo da rejeição.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function rejectDocument(int $documentId, int $adminId, string $reason): array
    {
        $document = $this->documentRepo->findById($documentId);

        if ($document === null) {
            return ['success' => false, 'error' => 'Documento não encontrado.'];
        }

        if ($document->status !== 'pending') {
            return ['success' => false, 'error' => 'Este documento já foi revisado.'];
        }

        if (empty(trim($reason))) {
            return ['success' => false, 'error' => 'O motivo da rejeição é obrigatório.'];
        }

        $this->documentRepo->updateStatus($documentId, 'rejected', $adminId, $reason);

        return ['success' => true];
    }

    /**
     * Aprova uma empresa, transitando para o status APPROVED.
     *
     * @param int         $businessId ID da empresa.
     * @param int         $adminId    ID do administrador.
     * @param string|null $notes      Notas adicionais (opcional).
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function approveBusiness(int $businessId, int $adminId, ?string $notes = null): array
    {
        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'error' => 'Empresa não encontrada.'];
        }

        $validationResult = $this->validateTransition($business->verification_status, 'approved');
        if ($validationResult !== null) {
            return ['success' => false, 'error' => $validationResult];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $previousStatus = $business->verification_status;
            $this->businessRepo->updateStatus($businessId, 'approved', $notes);

            $this->logRepo->create([
                'business_id'           => $businessId,
                'administrator_user_id' => $adminId,
                'action_type'           => 'approve_business',
                'previous_status'       => $previousStatus,
                'new_status'            => 'approved',
                'reason'                => $notes,
            ]);

            $db->commit();

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao aprovar a empresa. Tente novamente.'];
        }
    }

    /**
     * Rejeita uma empresa, transitando para o status REJECTED.
     *
     * @param int    $businessId ID da empresa.
     * @param int    $adminId    ID do administrador.
     * @param string $reason     Motivo da rejeição.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function rejectBusiness(int $businessId, int $adminId, string $reason): array
    {
        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'error' => 'Empresa não encontrada.'];
        }

        $validationResult = $this->validateTransition($business->verification_status, 'rejected');
        if ($validationResult !== null) {
            return ['success' => false, 'error' => $validationResult];
        }

        if (empty(trim($reason))) {
            return ['success' => false, 'error' => 'O motivo da rejeição é obrigatório.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $previousStatus = $business->verification_status;
            $this->businessRepo->updateStatus($businessId, 'rejected', $reason);

            $this->logRepo->create([
                'business_id'           => $businessId,
                'administrator_user_id' => $adminId,
                'action_type'           => 'reject_business',
                'previous_status'       => $previousStatus,
                'new_status'            => 'rejected',
                'reason'                => $reason,
            ]);

            $db->commit();

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao rejeitar a empresa. Tente novamente.'];
        }
    }

    /**
     * Solicita documentos adicionais, transitando para DOCUMENTS_REQUESTED.
     *
     * @param int    $businessId ID da empresa.
     * @param int    $adminId    ID do administrador.
     * @param string $notes      Notas sobre os documentos solicitados.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function requestDocuments(int $businessId, int $adminId, string $notes): array
    {
        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'error' => 'Empresa não encontrada.'];
        }

        $validationResult = $this->validateTransition($business->verification_status, 'documents_requested');
        if ($validationResult !== null) {
            return ['success' => false, 'error' => $validationResult];
        }

        if (empty(trim($notes))) {
            return ['success' => false, 'error' => 'A descrição dos documentos solicitados é obrigatória.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $previousStatus = $business->verification_status;
            $this->businessRepo->updateStatus($businessId, 'documents_requested', $notes);

            $this->logRepo->create([
                'business_id'           => $businessId,
                'administrator_user_id' => $adminId,
                'action_type'           => 'request_documents',
                'previous_status'       => $previousStatus,
                'new_status'            => 'documents_requested',
                'reason'                => $notes,
            ]);

            $db->commit();

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao solicitar documentos. Tente novamente.'];
        }
    }

    /**
     * Suspende uma empresa aprovada.
     *
     * @param int    $businessId ID da empresa.
     * @param int    $adminId    ID do administrador.
     * @param string $reason     Motivo da suspensão.
     * @return array ['success' => true] ou ['success' => false, 'error' => string].
     */
    public function suspendBusiness(int $businessId, int $adminId, string $reason): array
    {
        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'error' => 'Empresa não encontrada.'];
        }

        $validationResult = $this->validateTransition($business->verification_status, 'suspended');
        if ($validationResult !== null) {
            return ['success' => false, 'error' => $validationResult];
        }

        if (empty(trim($reason))) {
            return ['success' => false, 'error' => 'O motivo da suspensão é obrigatório.'];
        }

        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $previousStatus = $business->verification_status;
            $this->businessRepo->updateStatus($businessId, 'suspended', $reason);

            $this->logRepo->create([
                'business_id'           => $businessId,
                'administrator_user_id' => $adminId,
                'action_type'           => 'suspend_business',
                'previous_status'       => $previousStatus,
                'new_status'            => 'suspended',
                'reason'                => $reason,
            ]);

            $db->commit();

            return ['success' => true];
        } catch (\Throwable $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao suspender a empresa. Tente novamente.'];
        }
    }

    /**
     * Retorna o perfil completo de uma empresa com todos os dados relacionados.
     *
     * @param int $businessId ID da empresa.
     * @return array Perfil completo com: business, address, entrepreneur, documents, bank_account, logs.
     */
    public function getBusinessFullProfile(int $businessId): array
    {
        $addressRepo = new AddressRepository(Database::getInstance());

        $business = $this->businessRepo->findById($businessId);

        if ($business === null) {
            return ['success' => false, 'error' => 'Empresa não encontrada.'];
        }

        return [
            'success'      => true,
            'business'     => $business,
            'address'      => $addressRepo->findPrimaryByBusinessId($businessId),
            'entrepreneur' => $this->entrepreneurRepo->findByBusinessId($businessId),
            'documents'    => $this->documentRepo->findByBusinessId($businessId),
            'bank_account' => $this->bankAccountRepo->findByBusinessId($businessId),
            'logs'         => $this->logRepo->findByBusinessId($businessId),
        ];
    }

    /**
     * Retorna estatísticas do painel administrativo.
     *
     * @return array Estatísticas com: counts_by_status, recent_actions, pending_reviews_count.
     */
    public function getDashboardStats(): array
    {
        $countsByStatus = $this->businessRepo->countByStatus();
        $recentActions = $this->logRepo->findRecentActions(20);
        $pendingReviews = $this->businessRepo->findAllPendingReview();

        return [
            'counts_by_status'      => $countsByStatus,
            'recent_actions'        => $recentActions,
            'pending_reviews_count' => count($pendingReviews),
        ];
    }

    /**
     * Valida se uma transição de status é permitida.
     *
     * @param string $currentStatus Status atual da empresa.
     * @param string $newStatus     Novo status desejado.
     * @return string|null Mensagem de erro se a transição é inválida, null se permitida.
     */
    private function validateTransition(string $currentStatus, string $newStatus): ?string
    {
        $allowed = self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];

        if (!in_array($newStatus, $allowed, true)) {
            $statusLabels = [
                'draft'               => 'Rascunho',
                'pending_review'      => 'Pendente de Revisão',
                'approved'            => 'Aprovada',
                'rejected'            => 'Rejeitada',
                'documents_requested' => 'Documentos Solicitados',
                'suspended'           => 'Suspensa',
            ];

            $currentLabel = $statusLabels[$currentStatus] ?? $currentStatus;
            $newLabel = $statusLabels[$newStatus] ?? $newStatus;

            return "Não é possível alterar o status de \"{$currentLabel}\" para \"{$newLabel}\".";
        }

        return null;
    }
}
